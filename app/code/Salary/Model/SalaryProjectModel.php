<?php
/**
 * Company salary projects.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class SalaryProjectModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("salary_projects");
	}

	/**
	 * @return \ScshuxCms\Salary\Model\SalaryProjectModel
	 */
	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new SalaryProjectModel();
		}
		return self::$_instance;
	}

	public static function getSourceTypeLabels()
	{
		return array(
			'fixed' => '固定项目',
			'calculated' => '核算项目',
		);
	}

	public static function getDirectionLabels()
	{
		return array(
			'earning' => '应发',
			'deduction' => '应扣',
		);
	}

	public static function getCalculationModeLabels()
	{
		return array(
			'manual' => '手工录入',
			'fixed' => '固定金额',
			'formula' => '公式计算',
			'module' => '模块带入',
		);
	}

	public static function getStatusLabels()
	{
		return array(
			'active' => '启用',
			'inactive' => '停用',
		);
	}

	public static function label($labels, $key)
	{
		return isset($labels[$key]) ? $labels[$key] : $key;
	}
}
