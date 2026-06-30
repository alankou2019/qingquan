<?php
/**
 * Employee payroll slips.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Salary\Model\PayrollEmployeeRowModel;
use ScshuxCms\Salary\Model\PayrollPeriodModel;

class PayrollSlipModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("payroll_slips");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new PayrollSlipModel();
		}
		return self::$_instance;
	}

	public function publishByPeriod($companyId, $periodId, $operatorId, $employeeIds = array(), $allowArchivedRecord = false)
	{
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($companyId, $periodId);
		if (!$period) {
			$this->_lastError = '月工资表不存在';
			return false;
		}
		if (!$allowArchivedRecord && !PayrollPeriodModel::canPublishPayslip($period['status'])) {
			$this->_lastError = '只有审核通过或已归档的工资表可以发工资条';
			return false;
		}

		$rows = PayrollEmployeeRowModel::factory()->getRowsByPeriod($companyId, $periodId);
		$employeeMap = $this->buildEmployeeIdMap($employeeIds);
		if (!empty($employeeMap)) {
			$filteredRows = array();
			foreach ($rows as $row) {
				$employeeId = intval($row['employee_id']);
				if (isset($employeeMap[$employeeId])) {
					$filteredRows[] = $row;
				}
			}
			$rows = $filteredRows;
		}
		if (empty($rows)) {
			$this->_lastError = '没有符合发放范围的员工工资数据';
			return false;
		}

		$db = $this->getDB();
		$slipTable = $this->getSource();
		$now = time();
		$publishedCount = 0;
		foreach ($rows as $row) {
			$employeeId = intval($row['employee_id']);
			if ($employeeId <= 0) {
				continue;
			}
			$sql = 'insert into `' . $slipTable . '` ' .
				'(`company_id`,`payroll_period_id`,`payroll_employee_row_id`,`employee_id`,`version_no`,`status`,`published_at`,`created_at`,`updated_at`) values ' .
				'(' . intval($companyId) . ',' . intval($periodId) . ',' . intval($row['id']) . ',' . $employeeId . ',1,"published",' . $now . ',' . $now . ',' . $now . ') ' .
				'on duplicate key update payroll_employee_row_id=values(payroll_employee_row_id),status="published",' .
				'published_at=if(published_at>0,published_at,values(published_at)),updated_at=values(updated_at)';
			if ($db->execute($sql)) {
				$publishedCount++;
			}
		}

		if ($publishedCount <= 0) {
			$this->_lastError = '没有可发放的员工工资条';
			return false;
		}

		PayrollPeriodModel::factory()->markPublished($companyId, $periodId, $operatorId);
		return $publishedCount;
	}

	public function getPublishedEmployeeIdMap($companyId, $periodId)
	{
		$sql = 'select employee_id from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and payroll_period_id=' . intval($periodId) . ' and status="published"';
		$items = $this->getDB()->query($sql)->fetchAll();
		$map = array();
		foreach ($items as $item) {
			$employeeId = intval($item['employee_id']);
			if ($employeeId > 0) {
				$map[$employeeId] = 1;
			}
		}
		return $map;
	}

	public function getPeriodConfirmStats($companyId, $periodIds)
	{
		$return = array();
		if (empty($periodIds) || !is_array($periodIds)) {
			return $return;
		}
		$cleanIds = array();
		foreach ($periodIds as $periodId) {
			$periodId = intval($periodId);
			if ($periodId > 0) {
				$cleanIds[$periodId] = $periodId;
			}
		}
		if (empty($cleanIds)) {
			return $return;
		}
		$sql = 'select payroll_period_id,count(*) as published_count,' .
			'sum(case when viewed_at>0 then 1 else 0 end) as viewed_count,' .
			'sum(case when confirmed_at>0 then 1 else 0 end) as confirmed_count ' .
			'from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and payroll_period_id in (' . implode(',', $cleanIds) . ') and status="published" group by payroll_period_id';
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $item) {
			$periodId = intval($item['payroll_period_id']);
			$return[$periodId] = array(
				'published_count' => intval($item['published_count']),
				'viewed_count' => intval($item['viewed_count']),
				'confirmed_count' => intval($item['confirmed_count']),
			);
		}
		return $return;
	}

	public function confirmEmployeeSlip($companyId, $employeeId, $slipId)
	{
		$companyId = intval($companyId);
		$employeeId = intval($employeeId);
		$slipId = intval($slipId);
		if ($companyId <= 0 || $employeeId <= 0 || $slipId <= 0) {
			$this->_lastError = '参数错误';
			return false;
		}
		$item = $this->findFirst('id=' . $slipId . ' and company_id=' . $companyId . ' and employee_id=' . $employeeId . ' and status="published"');
		if (!$item) {
			$this->_lastError = '工资条不存在';
			return false;
		}
		if (intval($item->confirmed_at) > 0) {
			return true;
		}
		$now = time();
		$item->confirmed_at = $now;
		if (empty($item->viewed_at)) {
			$item->viewed_at = $now;
		}
		$item->updated_at = $now;
		if (!$item->save()) {
			$this->_lastError = '确认失败，请稍后再试';
			return false;
		}
		return true;
	}

	protected function buildEmployeeIdMap($employeeIds)
	{
		$map = array();
		if (empty($employeeIds) || !is_array($employeeIds)) {
			return $map;
		}
		foreach ($employeeIds as $employeeId) {
			$employeeId = intval($employeeId);
			if ($employeeId > 0) {
				$map[$employeeId] = 1;
			}
		}
		return $map;
	}

	public static function getEmployeePublishedSlips($companyId, $employeeId, $year = '', $month = '', $limit = 24)
	{
		$model = self::factory();
		$db = $model->getDB();
		$slipTable = $model->getSource();
		$periodTable = $model->getTableName('payroll_periods');
		$rowTable = $model->getTableName('payroll_employee_rows');

		$where = 's.company_id=' . intval($companyId) .
			' and s.employee_id=' . intval($employeeId) .
			' and s.status="published" and s.published_at>0';
		if ($month !== '') {
			$where .= ' and p.payroll_month="' . addslashes($month) . '"';
		} elseif ($year !== '') {
			$where .= ' and p.payroll_month like "' . addslashes($year) . '-%"';
		}

		$sql = 'select s.id,s.status,s.published_at,s.viewed_at,s.confirmed_at,' .
			'p.payroll_month,r.employee_name,r.department_name,r.earning_total,r.deduction_total,r.net_amount ' .
			'from `' . $slipTable . '` s ' .
			'left join `' . $periodTable . '` p on s.payroll_period_id=p.id ' .
			'left join `' . $rowTable . '` r on s.payroll_employee_row_id=r.id ' .
			'where ' . $where . ' order by p.payroll_month desc limit ' . intval($limit);
		return $db->query($sql)->fetchAll();
	}

	public static function getEmployeePublishedSlipDetail($companyId, $employeeId, $slipId)
	{
		$model = self::factory();
		$db = $model->getDB();
		$slipTable = $model->getSource();
		$periodTable = $model->getTableName('payroll_periods');
		$rowTable = $model->getTableName('payroll_employee_rows');

		$sql = 'select s.id,s.status,s.published_at,s.viewed_at,s.confirmed_at,' .
			'p.payroll_month,r.id as row_id,r.employee_name,r.employee_no,r.department_name,' .
			'r.position_name,r.earning_total,r.deduction_total,r.net_amount,r.remark ' .
			'from `' . $slipTable . '` s ' .
			'left join `' . $periodTable . '` p on s.payroll_period_id=p.id ' .
			'left join `' . $rowTable . '` r on s.payroll_employee_row_id=r.id ' .
			'where s.id=' . intval($slipId) .
			' and s.company_id=' . intval($companyId) .
			' and s.employee_id=' . intval($employeeId) .
			' and s.status="published" and s.published_at>0 limit 1';
		$item = $db->query($sql)->fetch();
		if (!$item) {
			return false;
		}
		if (empty($item['viewed_at'])) {
			$db->execute('update `' . $slipTable . '` set viewed_at=' . time() . ',updated_at=' . time() .
				' where id=' . intval($slipId) .
				' and company_id=' . intval($companyId) .
				' and employee_id=' . intval($employeeId));
			$item['viewed_at'] = time();
		}

		$valueTable = $model->getTableName('payroll_item_values');
		$valueSql = 'select project_name,direction,final_amount,remark from `' . $valueTable . '` ' .
			'where company_id=' . intval($companyId) .
			' and employee_id=' . intval($employeeId) .
			' and payroll_employee_row_id=' . intval($item['row_id']) .
			' order by id asc';
		$item['values'] = $db->query($valueSql)->fetchAll();
		return $item;
	}
}
