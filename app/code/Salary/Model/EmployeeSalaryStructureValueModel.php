<?php
/**
 * Employee salary structure item values.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class EmployeeSalaryStructureValueModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("employee_salary_structure_values");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new EmployeeSalaryStructureValueModel();
		}
		return self::$_instance;
	}
}
