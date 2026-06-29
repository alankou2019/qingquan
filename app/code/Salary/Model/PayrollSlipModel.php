<?php
/**
 * Employee payroll slips.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

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
