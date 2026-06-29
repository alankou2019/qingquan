<?php
/**
 * Promotion management reserved entry for company backend.
 */
namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Salary\Model\CompanyModuleAuthModel;

class PromotionController extends FrontendBaseController
{
	public function indexAction()
	{
		$this->checkModule();
		$this->view->setVar('features', $this->getFeatures());
	}

	public function channelAction()
	{
		$this->showFeature('channel', '晋升通道');
	}

	public function applicationAction()
	{
		$this->showFeature('application', '晋升申请');
	}

	public function reviewAction()
	{
		$this->showFeature('review', '晋升评审');
	}

	public function recordAction()
	{
		$this->showFeature('record', '晋升记录');
	}

	protected function showFeature($featureCode, $featureName)
	{
		$this->checkFeature($featureCode);
		$this->view->setVar('featureName', $featureName);
		$this->view->pick('promotion/feature');
	}

	protected function checkModule()
	{
		$authMap = CompanyModuleAuthModel::getCompanyAuthMap($this->companyId);
		if (!CompanyModuleAuthModel::isEnabled($authMap, 'promotion')) {
			Utils::showMsg('晋升管理模块未开通，请联系营运后台开通。', $this->getHelper()->createUrl(array('p' => 'index/index')));
		}
		return $authMap;
	}

	protected function checkFeature($featureCode)
	{
		$authMap = $this->checkModule();
		if (!CompanyModuleAuthModel::isEnabled($authMap, 'promotion', $featureCode)) {
			Utils::showMsg('当前晋升子功能未开通，请联系营运后台开通。', $this->getHelper()->createUrl(array('p' => 'promotion/index')));
		}
		return true;
	}

	protected function getFeatures()
	{
		$authMap = $this->checkModule();
		$items = array(
			array('code' => 'channel', 'name' => '晋升通道', 'url' => 'promotion/channel'),
			array('code' => 'application', 'name' => '晋升申请', 'url' => 'promotion/application'),
			array('code' => 'review', 'name' => '晋升评审', 'url' => 'promotion/review'),
			array('code' => 'record', 'name' => '晋升记录', 'url' => 'promotion/record'),
		);
		foreach ($items as $key => $item) {
			$items[$key]['enabled'] = CompanyModuleAuthModel::isEnabled($authMap, 'promotion', $item['code']) ? 1 : 0;
		}
		return $items;
	}
}
