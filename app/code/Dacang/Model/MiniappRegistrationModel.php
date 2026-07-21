<?php
namespace ScshuxCms\Dacang\Model;

use ScshuxCms\Core\Model\BaseModel;

class MiniappRegistrationModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName('miniapp_registration_application');
	}

	public static function factory()
	{
		if (self::$_instance === null) {
			self::$_instance = new MiniappRegistrationModel();
		}
		return self::$_instance;
	}
}