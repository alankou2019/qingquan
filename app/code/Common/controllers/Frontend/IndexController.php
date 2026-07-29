<?php
/**
 * 网站首页
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\User\Model\UserModel;
use ScshuxCms\Salary\Model\CompanyModuleAuthModel;
class  IndexController  extends FrontendBaseController
{
	
	/**
	 * 网站首页
	 */
	public  function  indexAction()
	{
		$bigClass=$this->request->get('bigClass')?intval($this->request->get('bigClass')):1;
		$bigClass=$this->filterBigClass($bigClass);
		$this->view->setVar('bigClass', $bigClass);
		$this->setLayouts('newadmin');
	}
	
	//导航设置
	public function mainAction()
	{
		$this->setLayouts('newadmin');
	}
	public function topAction()
	{
		$bigClass=$this->request->get('bigClass')?intval($this->request->get('bigClass')):1;
		$bigClass=$this->filterBigClass($bigClass);
		$this->view->setVar('bigClass', $bigClass);
		//判断是否已开通积分考评模块
		$isPoint=UserModel::checkPointModule();
		$this->view->setVar('ispoint', $isPoint);
		$moduleAuth = $this->getCompanyModuleAuth();
		$this->view->setVar('hasSalaryModule', CompanyModuleAuthModel::isEnabled($moduleAuth, 'salary'));
		$this->view->setVar('hasTrainingModule', CompanyModuleAuthModel::isEnabled($moduleAuth, 'training'));
		$this->view->setVar('hasPromotionModule', CompanyModuleAuthModel::isEnabled($moduleAuth, 'promotion'));
		$this->setLayouts('newadmin');
	}
	public function leftAction()
	{
		$bigClass=$this->request->get('bigClass')?intval($this->request->get('bigClass')):1;
		$bigClass=$this->filterBigClass($bigClass);
		$this->view->setVar('bigClass', $bigClass);
		$moduleAuth = $this->getCompanyModuleAuth();
		$this->view->setVar('salaryFeatures', $this->getFeatureStatus($moduleAuth, 'salary'));
		$this->view->setVar('trainingFeatures', $this->getFeatureStatus($moduleAuth, 'training'));
		$this->view->setVar('promotionFeatures', $this->getFeatureStatus($moduleAuth, 'promotion'));
		$this->setLayouts('newadmin');
	}
	public function swichAction()
	{
		$this->setLayouts('newadmin');
	}
	
	//空页面
	public function kongAction()
	{
		
	}
	
	
	/**
	 * @desc	设置layouts
	 * @param
	 * @return
	 */
	public function setLayouts($name)
	{
		$mainview =  $this->getView()->getMainView();
		$mainview = str_replace('/main', $name, $mainview);
		$this->getView()->setMainView($mainview);
	}

	protected function filterBigClass($bigClass)
	{
		$bigClass = intval($bigClass);
		if ($bigClass < 1) {
			return 1;
		}

		if ($bigClass == 2 && !UserModel::checkPointModule()) {
			return 1;
		}

		$moduleAuth = $this->getCompanyModuleAuth();
		if ($bigClass == 4 && !CompanyModuleAuthModel::isEnabled($moduleAuth, 'salary')) {
			return 1;
		}
		if ($bigClass == 5 && !CompanyModuleAuthModel::isEnabled($moduleAuth, 'training')) {
			return 1;
		}
		if ($bigClass == 6 && !CompanyModuleAuthModel::isEnabled($moduleAuth, 'promotion')) {
			return 1;
		}

		if (!in_array($bigClass, array(1, 2, 3, 4, 5, 6))) {
			return 1;
		}

		return $bigClass;
	}

	protected function getCompanyModuleAuth()
	{
		static $moduleAuth = null;
		if ($moduleAuth === null) {
			$moduleAuth = CompanyModuleAuthModel::getCompanyAuthMap($this->companyId);
		}
		return $moduleAuth;
	}

	protected function getFeatureStatus($moduleAuth, $moduleCode)
	{
		$return = array();
		$catalog = CompanyModuleAuthModel::getModuleCatalog();
		if (!isset($catalog[$moduleCode])) {
			return $return;
		}

		foreach ($catalog[$moduleCode]['features'] as $featureCode => $featureName) {
			$return[$featureCode] = CompanyModuleAuthModel::isEnabled($moduleAuth, $moduleCode, $featureCode) ? 1 : 0;
		}
		return $return;
	}
}
