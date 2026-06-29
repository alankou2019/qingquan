<?php
/**
 * Employee salary structure versions.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class EmployeeSalaryStructureModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("employee_salary_structures");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new EmployeeSalaryStructureModel();
		}
		return self::$_instance;
	}
}
