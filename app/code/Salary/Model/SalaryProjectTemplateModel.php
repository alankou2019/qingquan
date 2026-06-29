<?php
/**
 * System salary project templates.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class SalaryProjectTemplateModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("salary_project_templates");
	}

	/**
	 * @return \ScshuxCms\Salary\Model\SalaryProjectTemplateModel
	 */
	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new SalaryProjectTemplateModel();
		}
		return self::$_instance;
	}
}
