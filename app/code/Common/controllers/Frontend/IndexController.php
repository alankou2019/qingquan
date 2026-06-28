<?php
/**
 * 网站首页
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\User\Model\UserModel;
class  IndexController  extends FrontendBaseController
{
	
	/**
	 * 网站首页
	 */
	public  function  indexAction()
	{
		$bigClass=$this->request->get('bigClass')?intval($this->request->get('bigClass')):1;
		$bigClass=$bigClass>2?2:$bigClass;
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
		$this->view->setVar('bigClass', $bigClass);
		//判断是否已开通积分考评模块
		$isPoint=UserModel::checkPointModule();
		$this->view->setVar('ispoint', $isPoint);
		$this->setLayouts('newadmin');
	}
	public function leftAction()
	{
		$bigClass=$this->request->get('bigClass')?intval($this->request->get('bigClass')):1;
		$this->view->setVar('bigClass', $bigClass);
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
}