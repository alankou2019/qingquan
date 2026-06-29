<?php
/**
 * Company module authorization.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class CompanyModuleAuthModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("company_module_auth");
	}

	/**
	 * @return \ScshuxCms\Salary\Model\CompanyModuleAuthModel
	 */
	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new CompanyModuleAuthModel();
		}
		return self::$_instance;
	}

	public static function getModuleCatalog()
	{
		return array(
			'performance' => array(
				'name' => '绩效考核',
				'code' => 'performance',
				'readonly' => 1,
				'note' => '原有模块，保留现有入口和业务流程，本次不提供关闭操作。',
				'features' => array(
					'assessment_template' => '考核模板',
					'monthly_assessment' => '月度考核',
					'point_assessment' => '积分考核',
					'report_export' => '报表导出',
				),
			),
			'salary' => array(
				'name' => '薪酬管理',
				'code' => 'salary',
				'readonly' => 0,
				'note' => '开通后企业后台显示薪酬管理页面，子功能按授权显示。',
				'features' => array(
					'payroll' => '工资核算',
					'payslip' => '工资条发放',
					'commission' => '提成核算',
					'performance_salary' => '绩效工资核算',
				),
			),
			'training' => array(
				'name' => '培训管理',
				'code' => 'training',
				'readonly' => 0,
				'note' => '预留模块，后续扩展培训计划、培训记录和培训档案。',
				'features' => array(
					'plan' => '培训计划',
					'record' => '培训记录',
					'exam' => '考试测评',
					'archive' => '培训档案',
				),
			),
			'promotion' => array(
				'name' => '晋升管理',
				'code' => 'promotion',
				'readonly' => 0,
				'note' => '预留模块，后续扩展晋升通道、申请和评审。',
				'features' => array(
					'channel' => '晋升通道',
					'application' => '晋升申请',
					'review' => '晋升评审',
					'record' => '晋升记录',
				),
			),
		);
	}

	public static function getCompanyAuthMap($companyId)
	{
		$return = array();
		$companyId = intval($companyId);
		if ($companyId <= 0) {
			return $return;
		}

		$items = self::factory()->find('company_id=' . $companyId);
		foreach ($items as $item) {
			$featureCode = $item->feature_code == '' ? '_module' : $item->feature_code;
			if (!isset($return[$item->module_code])) {
				$return[$item->module_code] = array();
			}
			$return[$item->module_code][$featureCode] = intval($item->is_enabled);
		}

		return $return;
	}

	public static function isEnabled($authMap, $moduleCode, $featureCode = '_module')
	{
		return isset($authMap[$moduleCode][$featureCode]) && intval($authMap[$moduleCode][$featureCode]) == 1;
	}

	public static function buildModuleViewList($authMap)
	{
		$return = array();
		$catalog = self::getModuleCatalog();
		foreach ($catalog as $moduleCode => $module) {
			$moduleEnabled = !empty($module['readonly']) ? 1 : (self::isEnabled($authMap, $moduleCode) ? 1 : 0);
			$features = array();
			foreach ($module['features'] as $featureCode => $featureName) {
				$features[] = array(
					'code' => $featureCode,
					'name' => $featureName,
					'enabled' => !empty($module['readonly']) ? 1 : (self::isEnabled($authMap, $moduleCode, $featureCode) ? 1 : 0),
				);
			}
			$return[] = array(
				'code' => $moduleCode,
				'name' => $module['name'],
				'note' => $module['note'],
				'readonly' => intval($module['readonly']),
				'enabled' => $moduleEnabled,
				'features' => $features,
			);
		}
		return $return;
	}

	public static function saveCompanyAuth($companyId, $authData, $operatorId = 0)
	{
		$companyId = intval($companyId);
		if ($companyId <= 0) {
			return false;
		}

		$catalog = self::getModuleCatalog();
		$now = time();
		foreach ($catalog as $moduleCode => $module) {
			if (!empty($module['readonly'])) {
				continue;
			}

			$moduleEnabled = isset($authData[$moduleCode]['_module']) ? intval($authData[$moduleCode]['_module']) : 0;
			self::saveOneAuth($companyId, $moduleCode, '', $moduleEnabled, $operatorId, $now);

			foreach ($module['features'] as $featureCode => $featureName) {
				$enabled = $moduleEnabled && isset($authData[$moduleCode][$featureCode]) ? intval($authData[$moduleCode][$featureCode]) : 0;
				self::saveOneAuth($companyId, $moduleCode, $featureCode, $enabled, $operatorId, $now);
			}
		}

		return true;
	}

	protected static function saveOneAuth($companyId, $moduleCode, $featureCode, $enabled, $operatorId, $now)
	{
		$model = self::factory();
		$where = 'company_id=' . intval($companyId) . ' and module_code="' . addslashes($moduleCode) . '" and feature_code="' . addslashes($featureCode) . '"';
		$item = $model->findFirst($where);
		$data = array(
			'company_id' => intval($companyId),
			'module_code' => $moduleCode,
			'feature_code' => $featureCode,
			'is_enabled' => intval($enabled) ? 1 : 0,
			'updated_at' => $now,
			'updated_by' => intval($operatorId),
		);

		if ($item) {
			return $item->save($data);
		}

		$data['created_at'] = $now;
		$authModel = new CompanyModuleAuthModel();
		return $authModel->save($data);
	}

	public static function getEnabledCompanies($companyIds, $moduleCode)
	{
		$return = array();
		if (empty($companyIds)) {
			return $return;
		}

		$ids = array();
		foreach ($companyIds as $companyId) {
			$companyId = intval($companyId);
			if ($companyId > 0) {
				$ids[] = $companyId;
			}
		}
		if (empty($ids)) {
			return $return;
		}

		$items = self::factory()->find('company_id in(' . implode(',', $ids) . ') and module_code="' . addslashes($moduleCode) . '" and feature_code="" and is_enabled=1');
		foreach ($items as $item) {
			$return[intval($item->company_id)] = 1;
		}

		return $return;
	}
}
