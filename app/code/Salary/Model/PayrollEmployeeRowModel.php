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
}
