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
}
