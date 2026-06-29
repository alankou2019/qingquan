<?php
/**
 * Training management reserved entry for company backend.
 */
namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Salary\Model\CompanyModuleAuthModel;

class TrainingController extends FrontendBaseController
{
	public function indexAction()
	{
		$this->checkModule();
		$this->view->setVar('features', $this->getFeatures());
	}

	public function planAction()
	{
		$this->showFeature('plan', '培训计划');
	}

	public function recordAction()
	{
		$this->showFeature('record', '培训记录');
	}

	public function examAction()
	{
		$this->showFeature('exam', '考试测评');
	}

	public function archiveAction()
	{
		$this->showFeature('archive', '培训档案');
	}

	protected function showFeature($featureCode, $featureName)
	{
		$this->checkFeature($featureCode);
		$this->view->setVar('featureName', $featureName);
		$this->view->pick('training/feature');
	}

	protected function checkModule()
	{
		$authMap = CompanyModuleAuthModel::getCompanyAuthMap($this->companyId);
		if (!CompanyModuleAuthModel::isEnabled($authMap, 'training')) {
			Utils::showMsg('培训管理模块未开通，请联系营运后台开通。', $this->getHelper()->createUrl(array('p' => 'index/index')));
		}
		return $authMap;
	}

	protected function checkFeature($featureCode)
	{
		$authMap = $this->checkModule();
		if (!CompanyModuleAuthModel::isEnabled($authMap, 'training', $featureCode)) {
			Utils::showMsg('当前培训子功能未开通，请联系营运后台开通。', $this->getHelper()->createUrl(array('p' => 'training/index')));
		}
		return true;
	}

	protected function getFeatures()
	{
		$authMap = $this->checkModule();
		$items = array(
			array('code' => 'plan', 'name' => '培训计划', 'url' => 'training/plan'),
			array('code' => 'record', 'name' => '培训记录', 'url' => 'training/record'),
			array('code' => 'exam', 'name' => '考试测评', 'url' => 'training/exam'),
			array('code' => 'archive', 'name' => '培训档案', 'url' => 'training/archive'),
		);
		foreach ($items as $key => $item) {
			$items[$key]['enabled'] = CompanyModuleAuthModel::isEnabled($authMap, 'training', $item['code']) ? 1 : 0;
		}
		return $items;
	}
}
