<?php
/**
 * 后台管理系统控制类,所有后台管理系统都应继承该类
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Core\Controller;
use ScshuxCms\Common\Model\AdminUserModel;
class  AdminBaseController  extends  BaseController
{
	/**
	 * 初始化
	 */
	public function initialize()
	{
		$adminUser = AdminUserModel::getLoginUser();
		if(empty($adminUser))
		{
			$this->redirect('login');
		}
		$this->getView()->setVar('_adminUser', $adminUser);
	}
}