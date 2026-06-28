<?php
/**
 * 大仓用户登录
 */
namespace ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use ScshuxCms\Core\Captcha;
use ScshuxCms\User\Model\UserModel;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Helper\DingdingOapi;
class  LoginController extends  FrontendBaseController
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
	public  function  indexAction($hash_key='')
	{
		if(UserModel::isLogin())
		{
			$this->redirect('index/index');
		}
		
		$code = $_REQUEST['code'];
		if($hash_key&& $code)
		{
			$reuslt = DingdingOapi::factory()->LoginByCode($hash_key, $code);
			if($reuslt)
			{
				$this->redirect('index/index');
				
			}
			
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
			 $vcode = $this->session->get('usercaptcha');
			 if(strtolower($vcode)!=strtolower($postData['code']))
			 {
			 	$this->sendErrorResult('验证码错误!');
			 }
			 $this->session->remove('usercaptcha');
			 $username = addslashes(trim($postData['username']));
			 
			 $user = UserModel::factory()->loadUserByUserName($username);
			 if(empty($user))
			 {
			 	$this->sendErrorResult('用户不存在!');
			 }
			 
			 if(md5($postData['password']) != $user->password)
			 {
			 	$this->sendErrorResult('用户名和密码不匹配!');
			 }
			 
			 //查询当前企业信息
			 $companyModel = CompanyModel::findFirst('id='.intval($user->company_id));
			 if(empty($companyModel) || empty($companyModel->status))
			 {
			 	$this->sendErrorResult('当前企业不存在或者已经被禁用!');
			 }
			 
			 //查询企业使用时间是否已经到期
			 $expire_time = $companyModel->expire_time ;
			 
			 if(($expire_time != -1) && ($expire_time < time()))
			 {
			 	$this->sendErrorResult('你的试用时间已经到期，请联系管理员'); 
			 }
			 
			 UserModel::factory()->loginAfter($user,$companyModel);
			 $this->sendSuccessResult('登录成功!');
		}
	}
	
	/**
	 * 退出
	 */
	public  function  logoutAction()
	{
		$data = array() ;
		Helper::factory()->getSession()->set('_user', $data);
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
		$this->session->set('usercaptcha', $captchaObj->getCode());
		exit;
	
	}
	
	
}
