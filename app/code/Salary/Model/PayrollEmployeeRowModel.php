<?php
/**
 * Employee monthly payroll rows.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class PayrollEmployeeRowModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("payroll_employee_rows");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new PayrollEmployeeRowModel();
		}
		return self::$_instance;
	}

	public function getRowsByPeriod($companyId, $periodId)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and payroll_period_id=' . intval($periodId) . ' order by id asc';
		return $this->getDB()->query($sql)->fetchAll();
	}

	public function getPayrollMatrix($companyId, $periodId)
	{
		$rows = $this->getRowsByPeriod($companyId, $periodId);
		$valueTable = $this->getTableName('payroll_item_values');
		$values = array();
		$sql = 'select * from `' . $valueTable . '` where company_id=' . intval($companyId) .
			' and payroll_period_id=' . intval($periodId) . ' order by id asc';
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $item) {
			$rowId = intval($item['payroll_employee_row_id']);
			if (!isset($values[$rowId])) {
				$values[$rowId] = array();
			}
			if (SalaryProjectModel::isTextProject($item) && isset($item['text_value'])) {
				$values[$rowId][intval($item['salary_project_id'])] = $item['text_value'];
			} else {
				$values[$rowId][intval($item['salary_project_id'])] = sprintf('%.2f', floatval($item['final_amount']));
			}
		}
		foreach ($rows as $key => $row) {
			$rowId = intval($row['id']);
			$rows[$key]['values'] = isset($values[$rowId]) ? $values[$rowId] : array();
		}
		return $rows;
	}
}
