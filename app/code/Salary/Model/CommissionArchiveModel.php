<?php
/**
 * Archived monthly commission snapshots.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class CommissionArchiveModel extends BaseModel
{
	protected static $_instance = null;

	public function initialize()
	{
		$this->setSource($this->getTableName('salary_commission_archives'));
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new CommissionArchiveModel();
		}
		return self::$_instance;
	}

	public function archivePeriod($companyId, $periodId, $operatorId)
	{
		$this->purgeExpiredDeletedArchives($companyId);
		$periodModel = CommissionPeriodModel::factory();
		$period = $periodModel->getCompanyPeriod($companyId, $periodId);
		if (!$period) {
			$this->_lastError = 'Commission calculation sheet does not exist';
			return false;
		}
		if (!in_array($period['status'], array('draft', 'calculated'))) {
			$this->_lastError = 'Only a calculated commission sheet can be archived';
			return false;
		}
		$existing = $this->getActiveArchiveByPeriod($companyId, $periodId);
		if ($existing) {
			$this->_lastError = 'This commission sheet has already been archived';
			return false;
		}
		$rows = $periodModel->getCommissionMatrix($companyId, $periodId);
		if (empty($rows)) {
			$this->_lastError = 'The commission sheet has no employee data to archive';
			return false;
		}
		$now = time();
		$sql = 'insert into `' . $this->getSource() . '` (`company_id`,`commission_period_id`,`commission_month`,`employee_count`,`matched_count`,`total_amount`,`snapshot_data`,`archived_by`,`archived_at`,`created_at`) values ' .
			'(' . intval($companyId) . ',' . intval($periodId) . ',"' . addslashes($period['commission_month']) . '",' . intval($period['employee_count']) . ',' . intval($period['matched_count']) . ',' . $this->money($period['total_amount']) . ',"' . addslashes(serialize($rows)) . '",' . intval($operatorId) . ',' . $now . ',' . $now . ')';
		$db = $this->getDB();
		$db->begin();
		try {
			$db->execute($sql);
			$archiveId = intval($db->lastInsertId());
			if ($archiveId <= 0) {
				throw new \Exception('Cannot create archive record');
			}
			$db->execute('update `' . $periodModel->getSource() . '` set status="archived",updated_at=' . $now . ' where id=' . intval($periodId) . ' and company_id=' . intval($companyId));
			$db->commit();
			return $archiveId;
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = 'Archive commission sheet failed: ' . $e->getMessage();
			return false;
		}
	}

	public function getCompanyArchives($companyId, $filter = array(), $limit = 60)
	{
		$this->purgeExpiredDeletedArchives($companyId);
		$where = ' where company_id=' . intval($companyId) . ' and deleted_at=0';
		if (!empty($filter['commission_month'])) {
			$where .= ' and commission_month="' . addslashes($filter['commission_month']) . '"';
		}
		if (!empty($filter['department_name'])) {
			$where .= ' and snapshot_data like "%' . addslashes($filter['department_name']) . '%"';
		}
		if (!empty($filter['employee_name'])) {
			$where .= ' and snapshot_data like "%' . addslashes($filter['employee_name']) . '%"';
		}
		$sql = 'select * from `' . $this->getSource() . '`' . $where . ' order by commission_month desc,id desc limit ' . intval($limit);
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $key => $item) {
			$items[$key]['archived_time'] = empty($item['archived_at']) ? '-' : date('Y-m-d H:i', intval($item['archived_at']));
		}
		return $items;
	}

	public function getArchive($companyId, $archiveId, $includeDeleted = false)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' and id=' . intval($archiveId);
		if (!$includeDeleted) {
			$sql .= ' and deleted_at=0';
		}
		$sql .= ' limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	public function getActiveArchiveByMonth($companyId, $commissionMonth)
	{
		if (!preg_match('/^\d{4}\-\d{2}$/', $commissionMonth)) {
			return false;
		}
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and commission_month="' . addslashes($commissionMonth) . '" and deleted_at=0 order by id desc limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	public function getArchiveRows($companyId, $archiveId)
	{
		$archive = $this->getArchive($companyId, $archiveId);
		if (!$archive) {
			return array();
		}
		$rows = @unserialize($archive['snapshot_data']);
		return is_array($rows) ? $rows : array();
	}

	/**
	 * Import an archived monthly commission total into the matching editable payroll period.
	 * Employees are matched by both name and mobile number; re-import overwrites the same project value.
	 */
	public function importArchiveToPayroll($companyId, $archiveId, $operatorId)
	{
		$companyId = intval($companyId);
		$archive = $this->getArchive($companyId, $archiveId);
		if (!$archive) {
			$this->_lastError = '提成归档记录不存在';
			return false;
		}

		$payrollModel = PayrollPeriodModel::factory();
		$payrollPeriod = $payrollModel->getCompanyPeriodByMonth($companyId, $archive['commission_month']);
		if (!$payrollPeriod) {
			$this->_lastError = '未找到' . $archive['commission_month'] . '的可编辑工资核算表，请先生成工资表';
			return false;
		}
		if (!in_array($payrollPeriod['status'], array('draft', 'calculated', 'rejected'))) {
			$this->_lastError = '当前工资表已提交审核、审核通过、归档或发放工资条，不能导入提成奖';
			return false;
		}

		$rowModel = PayrollEmployeeRowModel::factory();
		$projects = $rowModel->getPayrollProjectSnapshots($companyId, intval($payrollPeriod['id']));
		$commissionProjectId = 0;
		foreach ($projects as $projectKey => $project) {
			if (isset($project['linked_module']) && $project['linked_module'] == 'commission' &&
				isset($project['status']) && $project['status'] == 'active' && intval($project['deleted_at']) == 0) {
				if ($commissionProjectId > 0) {
					$this->_lastError = '当前工资表存在多个关联“提成奖”的工资项目，无法确定导入目标';
					return false;
				}
				$commissionProjectId = intval($project['id']);
				// Archived commission is a fixed monthly module result and must not be recalculated as a formula.
				$projects[$projectKey]['calculation_mode'] = 'module';
				$projects[$projectKey]['formula_text'] = '';
			}
		}
		if ($commissionProjectId <= 0) {
			$this->_lastError = '当前工资表未包含“提成奖”工资项目，请先在工资项目设置中启用该项目后重新生成工资表';
			return false;
		}

		$archiveRows = $this->getArchiveRows($companyId, $archiveId);
		$payrollRows = $rowModel->getPayrollMatrix($companyId, intval($payrollPeriod['id']));
		if (empty($archiveRows) || empty($payrollRows)) {
			$this->_lastError = '提成归档记录或工资核算表没有可导入的员工数据';
			return false;
		}

		$archiveMap = array();
		$archiveDuplicates = 0;
		$invalidArchiveCount = 0;
		foreach ($archiveRows as $row) {
			$key = $this->buildPersonMatchKey(isset($row['employee_name']) ? $row['employee_name'] : '', isset($row['employee_no']) ? $row['employee_no'] : '');
			if ($key === '') {
				$invalidArchiveCount++;
				continue;
			}
			if (isset($archiveMap[$key])) {
				$archiveMap[$key]['duplicate'] = 1;
				$archiveDuplicates++;
				continue;
			}
			$archiveMap[$key] = array(
				'amount' => isset($row['total_amount']) ? $this->money($row['total_amount']) : '0.00',
				'duplicate' => 0,
			);
		}

		$payrollKeyCount = array();
		foreach ($payrollRows as $row) {
			$key = $this->buildPersonMatchKey(isset($row['employee_name']) ? $row['employee_name'] : '', isset($row['employee_no']) ? $row['employee_no'] : '');
			if ($key !== '') {
				$payrollKeyCount[$key] = isset($payrollKeyCount[$key]) ? $payrollKeyCount[$key] + 1 : 1;
			}
		}

		$rows = array();
		$matchedKeys = array();
		$matchedCount = 0;
		$duplicateCount = $archiveDuplicates;
		$importedTotal = 0;
		foreach ($payrollRows as $payrollRow) {
			$values = isset($payrollRow['values']) && is_array($payrollRow['values']) ? $payrollRow['values'] : array();
			$key = $this->buildPersonMatchKey($payrollRow['employee_name'], $payrollRow['employee_no']);
			if ($key === '' || !isset($archiveMap[$key])) {
				// No matching archived record: preserve this employee's current payroll values.
			} elseif (!empty($archiveMap[$key]['duplicate']) || intval($payrollKeyCount[$key]) > 1) {
				$duplicateCount++;
			} else {
				// Assignment (not addition) makes repeated imports idempotent.
				$values[$commissionProjectId] = $archiveMap[$key]['amount'];
				$matchedKeys[$key] = 1;
				$matchedCount++;
				$importedTotal += floatval($archiveMap[$key]['amount']);
			}
			$rows[] = array(
				'employee' => array(
					'id' => intval($payrollRow['employee_id']),
					'name' => $payrollRow['employee_name'],
					'mobile' => $payrollRow['employee_no'],
					'department_name' => $payrollRow['department_name'],
					'position_name' => $payrollRow['position_name'],
				),
				'values' => $values,
			);
		}
		if ($matchedCount <= 0) {
			$this->_lastError = '没有找到手机号和姓名同时匹配的员工，未导入任何提成奖';
			return false;
		}
		$unmatchedCount = $invalidArchiveCount;
		foreach ($archiveMap as $key => $item) {
			if (empty($item['duplicate']) && !isset($matchedKeys[$key])) {
				$unmatchedCount++;
			}
		}

		$result = $payrollModel->savePayrollMatrix(
			$companyId,
			$payrollPeriod['payroll_month'],
			$projects,
			$rows,
			$operatorId,
			$payrollPeriod['source_type'],
			$payrollPeriod['source_name'],
			intval($payrollPeriod['id'])
		);
		if (!$result) {
			$this->_lastError = $payrollModel->getLastError();
			return false;
		}

		return array(
			'payroll_period_id' => intval($result),
			'payroll_month' => $payrollPeriod['payroll_month'],
			'archive_id' => intval($archiveId),
			'matched_count' => $matchedCount,
			'unmatched_count' => $unmatchedCount,
			'duplicate_count' => $duplicateCount,
			'imported_total' => $this->money($importedTotal),
		);
	}

	public function restoreToCalculation($companyId, $archiveId, $operatorId)
	{
		$archive = $this->getArchive($companyId, $archiveId);
		if (!$archive) {
			$this->_lastError = 'Commission archive record does not exist';
			return false;
		}
		$period = CommissionPeriodModel::factory()->getCompanyPeriod($companyId, intval($archive['commission_period_id']));
		if (!$period || $period['status'] != 'archived') {
			$this->_lastError = 'The archived commission sheet cannot be restored';
			return false;
		}
		$db = $this->getDB();
		$db->begin();
		try {
			$db->execute('update `' . CommissionPeriodModel::factory()->getSource() . '` set status="calculated",updated_at=' . time() . ' where id=' . intval($period['id']) . ' and company_id=' . intval($companyId));
			$db->execute('delete from `' . $this->getSource() . '` where id=' . intval($archiveId) . ' and company_id=' . intval($companyId));
			$db->commit();
			return intval($period['id']);
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = 'Restore commission archive failed: ' . $e->getMessage();
			return false;
		}
	}

	public function deleteArchive($companyId, $archiveId, $operatorId)
	{
		$archive = $this->getArchive($companyId, $archiveId);
		if (!$archive) {
			$this->_lastError = 'Commission archive record does not exist';
			return false;
		}
		$db = $this->getDB();
		$now = time();
		$db->begin();
		try {
			$db->execute('update `' . $this->getSource() . '` set deleted_by=' . intval($operatorId) . ',deleted_at=' . $now . ' where id=' . intval($archiveId) . ' and company_id=' . intval($companyId));
			$db->execute('update `' . CommissionPeriodModel::factory()->getSource() . '` set status="deleted",updated_at=' . $now . ' where id=' . intval($archive['commission_period_id']) . ' and company_id=' . intval($companyId) . ' and status="archived"');
			$db->commit();
			return true;
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = 'Delete commission archive failed: ' . $e->getMessage();
			return false;
		}
	}

	public function purgeExpiredDeletedArchives($companyId)
	{
		$expiredAt = time() - 180 * 86400;
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' and deleted_at>0 and deleted_at<' . intval($expiredAt);
		$items = $this->getDB()->query($sql)->fetchAll();
		$periodModel = CommissionPeriodModel::factory();
		$rowTable = $this->getTableName('salary_commission_rows');
		$valueTable = $this->getTableName('salary_commission_item_values');
		foreach ($items as $item) {
			$period = $periodModel->getCompanyPeriod($companyId, intval($item['commission_period_id']));
			if ($period && $period['status'] == 'deleted') {
				$this->getDB()->execute('delete from `' . $valueTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($period['id']));
				$this->getDB()->execute('delete from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($period['id']));
				$this->getDB()->execute('delete from `' . $periodModel->getSource() . '` where company_id=' . intval($companyId) . ' and id=' . intval($period['id']) . ' and status="deleted"');
			}
			$this->getDB()->execute('delete from `' . $this->getSource() . '` where id=' . intval($item['id']) . ' and company_id=' . intval($companyId));
		}
	}

	protected function getActiveArchiveByPeriod($companyId, $periodId)
	{
		$sql = 'select id from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($periodId) . ' and deleted_at=0 limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	protected function money($value)
	{
		return sprintf('%.2f', round(floatval($value), 2));
	}

	protected function buildPersonMatchKey($name, $mobile)
	{
		$name = preg_replace('/\s+/', '', trim((string)$name));
		$mobile = preg_replace('/\D+/', '', trim((string)$mobile));
		if ($name === '' || $mobile === '') {
			return '';
		}
		return $mobile . '|' . $name;
	}
}
