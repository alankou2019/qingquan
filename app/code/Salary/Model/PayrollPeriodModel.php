<?php
/**
 * Monthly payroll periods.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class PayrollPeriodModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("payroll_periods");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new PayrollPeriodModel();
		}
		return self::$_instance;
	}
}
