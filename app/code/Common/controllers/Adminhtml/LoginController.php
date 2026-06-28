<?php
/**
 * 系统登录
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Core\Captcha;
use ScshuxCms\Common\Model\AdminUserModel;
use ScshuxCms\Core\Helper;
class  LoginController extends  AdminBaseController
{
	
	/**
	 * 初始化
	 */
	public function initialize()
	{
		
	}
	
	/**
	 * 登录界面
	 */
	public  function  indexAction()
	{
		if(AdminUserModel::isLogin())
		{
			$this->redirect('index');
		}
	}
	
	/**
	 * 执行登录
	 */
	public  function  doLoginAction()
	{
		if($this->request->isPost())
		{
			 $postData = $_POST;
			 if(empty($postData['username']) || empty($postData['password']) || empty($postData['code']))
			 {
			 	$this->sendErrorResult('请检查您提交的数据!');
			 }
			 $vcode = $this->session->get('captcha');
			 if(strtolower($vcode)!=strtolower($postData['code']))
			 {
			 	$this->sendErrorResult('验证码错误!');
			 }
			 $this->session->remove('captcha');
			 $username = addslashes($postData['username']);
			 $adminUser = AdminUserModel::factory()->findFirst("username='{$username}'");
			 if(empty($adminUser))
			 {
			 	$this->sendErrorResult('用户不存在!');
			 }
			 if(empty($adminUser->is_active))
			 {
			 	$this->sendErrorResult('该用户已经被禁用!');
			 }
			 if(md5($postData['password']) != $adminUser->password)
			 {
			 	$this->sendErrorResult('用户名和密码不匹配!');
			 }
			 AdminUserModel::factory()->loginAfter($adminUser);
			 $this->sendSuccessResult('登录成功!');
		}
	}
	
	/**
	 * 退出
	 */
	public  function  logoutAction()
	{
		$data = array() ;
		
		Helper::factory()->getSession()->set('_admin_user', $data);
		$this->redirect('login');
	}
	
	/**
	 * 验证码
	 */
	public function  captchaAction()
	{
		header("Content-type: image/GIF");
		$captchaObj =new  Captcha(100,45,22);
		$captchaObj->doImg();
		$this->session->set('captcha', $captchaObj->getCode());
		exit;
	
	}
	
	
}
