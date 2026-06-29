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

	public function publishByPeriod($companyId, $periodId, $operatorId)
	{
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($companyId, $periodId);
		if (!$period) {
			$this->_lastError = '月工资表不存在';
			return false;
		}
		if (!PayrollPeriodModel::canPublishPayslip($period['status'])) {
			$this->_lastError = '只有审核通过的工资表可以发工资条并归档';
			return false;
		}

		$rows = PayrollEmployeeRowModel::factory()->getRowsByPeriod($companyId, $periodId);
		if (empty($rows)) {
			$this->_lastError = '月工资表没有员工工资数据';
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
