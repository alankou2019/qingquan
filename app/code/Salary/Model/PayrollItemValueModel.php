<?php
/**
 * Monthly payroll item value snapshots.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class PayrollItemValueModel extends BaseModel
{
	protected static $_instance = null;

	public function initialize()
	{
		$this->setSource($this->getTableName("payroll_item_values"));
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new PayrollItemValueModel();
		}
		return self::$_instance;
	}
}
