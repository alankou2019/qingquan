<?php
/**
 * Payroll archive snapshots.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class PayrollArchiveModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("salary_payroll_archives");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new PayrollArchiveModel();
		}
		return self::$_instance;
	}

	public function ensureTable()
	{
		$table = $this->getSource();
		$sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `company_id` int(10) unsigned NOT NULL,
		  `payroll_period_id` int(10) unsigned NOT NULL,
		  `payroll_month` char(7) NOT NULL,
		  `source_label` varchar(120) NOT NULL DEFAULT '',
		  `employee_count` int(10) unsigned NOT NULL DEFAULT '0',
		  `earning_total` decimal(16,2) NOT NULL DEFAULT '0.00',
		  `deduction_total` decimal(16,2) NOT NULL DEFAULT '0.00',
		  `net_total` decimal(16,2) NOT NULL DEFAULT '0.00',
		  `snapshot_data` mediumtext,
		  `archived_by` int(10) unsigned NOT NULL DEFAULT '0',
		  `archived_at` int(10) unsigned NOT NULL DEFAULT '0',
		  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  KEY `idx_salary_payroll_archive` (`company_id`,`payroll_month`,`archived_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8";
		return $this->getDB()->execute($sql);
	}

	public function createFromPeriod($companyId, $periodId, $operatorId)
	{
		$this->ensureTable();
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($companyId, $periodId);
		if (!$period) {
			$this->_lastError = '工资表不存在';
			return false;
		}
		$rows = PayrollEmployeeRowModel::factory()->getPayrollMatrix($companyId, $periodId);
		$projects = PayrollEmployeeRowModel::factory()->getPayrollProjectSnapshots($companyId, $periodId);
		$snapshot = array(
			'version' => 2,
			'projects' => $projects,
			'rows' => $rows,
		);
		$sourceLabel = PayrollPeriodModel::getSourceName($period['source_type'], $period['source_name']);
		$now = time();
		$sql = 'insert into `' . $this->getSource() . '` (`company_id`,`payroll_period_id`,`payroll_month`,`source_label`,`employee_count`,`earning_total`,`deduction_total`,`net_total`,`snapshot_data`,`archived_by`,`archived_at`,`created_at`) values ' .
			'(' . intval($companyId) . ',' . intval($periodId) . ',"' . addslashes($period['payroll_month']) . '","' . addslashes($sourceLabel) . '",' . intval($period['employee_count']) . ',' . $this->money($period['earning_total']) . ',' . $this->money($period['deduction_total']) . ',' . $this->money($period['net_total']) . ',"' . addslashes(serialize($snapshot)) . '",' . intval($operatorId) . ',' . $now . ',' . $now . ')';
		if (!$this->getDB()->execute($sql)) {
			$this->_lastError = '保存归档记录失败';
			return false;
		}
		$archiveId = intval($this->getDB()->lastInsertId());
		if ($archiveId > 0) {
			return $archiveId;
		}
		$sql = 'select id from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and payroll_period_id=' . intval($periodId) . ' and archived_by=' . intval($operatorId) .
			' and archived_at=' . $now . ' and created_at=' . $now . ' order by id desc limit 1';
		$row = $this->getDB()->query($sql)->fetch();
		if ($row) {
			return intval($row['id']);
		}
		$this->_lastError = '保存归档记录失败';
		return false;
	}

	public function getCompanyArchives($companyId, $limit = 36)
	{
		$this->ensureTable();
		$slipTable = $this->getTableName('payroll_slips');
		$sql = 'select a.*,count(distinct s.id) as slip_count,ifnull(sum(case when s.status="published" then 1 else 0 end),0) as published_count ' .
			'from `' . $this->getSource() . '` a ' .
			'left join `' . $slipTable . '` s on a.payroll_period_id=s.payroll_period_id and a.company_id=s.company_id ' .
			'where a.company_id=' . intval($companyId) . ' group by a.id order by a.payroll_month desc,a.id desc limit ' . intval($limit);
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $key => $item) {
			$items[$key]['row_count'] = intval($item['employee_count']);
			$items[$key]['archived_time'] = empty($item['archived_at']) ? '-' : date('Y-m-d H:i', intval($item['archived_at']));
			$items[$key]['source_label'] = $item['source_label'];
		}
		return $items;
	}

	public function getArchive($companyId, $archiveId)
	{
		$this->ensureTable();
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' and id=' . intval($archiveId) . ' limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	public function restoreToPayroll($companyId, $archiveId, $operatorId)
	{
		$archive = $this->getArchive($companyId, $archiveId);
		if (!$archive) {
			$this->_lastError = 'Archive payroll not found';
			return false;
		}
		$snapshot = @unserialize($archive['snapshot_data']);
		if (empty($snapshot) || !is_array($snapshot)) {
			$this->_lastError = 'Archive payroll data is incomplete';
			return false;
		}
		$projects = array();
		$snapshotRows = $snapshot;
		if (isset($snapshot['version']) && intval($snapshot['version']) >= 2) {
			$projects = isset($snapshot['projects']) && is_array($snapshot['projects']) ? $snapshot['projects'] : array();
			$snapshotRows = isset($snapshot['rows']) && is_array($snapshot['rows']) ? $snapshot['rows'] : array();
		}
		$rows = array();
		foreach ($snapshotRows as $item) {
			$employeeId = isset($item['employee_id']) ? intval($item['employee_id']) : 0;
			if ($employeeId <= 0) {
				continue;
			}
			$rows[] = array(
				'employee' => array(
					'id' => $employeeId,
					'name' => isset($item['employee_name']) ? $item['employee_name'] : '',
					'mobile' => isset($item['employee_no']) ? $item['employee_no'] : '',
					'department_name' => isset($item['department_name']) ? $item['department_name'] : '',
					'position_name' => isset($item['position_name']) ? $item['position_name'] : '',
				),
				'values' => isset($item['values']) && is_array($item['values']) ? $item['values'] : array(),
			);
		}
		if (empty($rows)) {
			$this->_lastError = 'Archive payroll has no employee rows to restore';
			return false;
		}
		if (empty($projects)) {
			$projects = PayrollEmployeeRowModel::factory()->getPayrollProjectSnapshots($companyId, intval($archive['payroll_period_id']));
		}
		if (empty($projects)) {
			// Last-resort compatibility for very old archives without item metadata.
			$projects = SalaryProjectModel::factory()->getCompanyProjects($companyId);
		}
		$periodId = PayrollPeriodModel::factory()->savePayrollMatrix($companyId, $archive['payroll_month'], $projects, $rows, $operatorId, 'manual', 'archive restore', intval($archive['payroll_period_id']));
		if (!$periodId) {
			$this->_lastError = PayrollPeriodModel::factory()->getLastError();
			return false;
		}
		$slipTable = $this->getTableName('payroll_slips');
		$this->getDB()->execute('delete from `' . $slipTable . '` where company_id=' . intval($companyId) . ' and payroll_period_id=' . intval($periodId));
		return $periodId;
	}

	protected function money($value)
	{
		return sprintf('%.2f', round(floatval($value), 2));
	}
}
