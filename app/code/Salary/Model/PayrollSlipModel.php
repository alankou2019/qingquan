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
}
