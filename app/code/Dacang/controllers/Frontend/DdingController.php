<?php
/**
 * 钉钉
* @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
* @author kaiping.jiang <kaiping.jiang@scshux.com>
*/
namespace ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper\Dding;
use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Dacang\Helper\DingdingOapi;
class  DdingController  extends FrontendBaseController
{
	public function initialize()
	{
		$mainview =  $this->getView()->getMainView();
		$mainview = str_replace('/main', '/bs', $mainview);
		$this->getView()->setMainView($mainview);
	}


	public function loginAction()
	{
		$frontuser = Helper::factory()->getSession()->get('_frontuser');
		if($frontuser)
		{
			$this->redirect('bs/newindex') ;
		}
		$company_haskey = $this->session->get('company_haskey');
		$jsconfig = DingdingOapi::factory()->getJsConfig($company_haskey);
		$this->view->setVar('jsconfig', $jsconfig);
		$this->view->setVar('callbackUrl', Helper::factory()->getCache()->get('callbackurl'));
	}
	
	
	/*
	 * 用户信息
	 */
	public  function infoAction()
	{
		$company_haskey = $this->session->get('company_haskey');
		$code  = $_REQUEST['code'];
		$userInfo = DingdingOapi::factory()->getOauthUserInfo($company_haskey, $code);
		if($userInfo)
		{
			
			$this->session->set('user_id',$userInfo['user_id']);
			$this->session->set('dingding_user_id',$userInfo['dingding_user_id']);
			$this->session->set('company_id',$userInfo['company_id']);
			$this->session->set('current_company_haskey', $company_haskey);
			$this->sendSuccessResult($userInfo);
			
		}else
		{
			$this->sendErrorResult('登录错误!');
		}
		
	}
	


	public function testAction()
	{
		$openid = 'fFt9pSii6D8dMzggIN7oAtgiEiE';
		$percode = 'NhWjlX7lAXX_VhvJitF3Zxaecd9LTz1XybH_UfuGK7Oq2sS93oaKLSGZixy4YcHX';

		$ddmodel = Dding::factory();
		$snstoken = $ddmodel->getSnsToken($openid, $percode) ;

		$snstoken = $snstoken->sns_token ;
		//获取钉钉id
		$ddingid = $ddmodel->getUserDdingId($snstoken) ;
		$userinfo = CompanyUserModel::findfirst("dingid = '".$ddingid."'") ;

		if(!isset($_SESSION))
		{
			session_start();
		}
			
		$session_id = session_id();

		$data = array(
				'id' => $userinfo->id,
				'company_id' => $userinfo->company_id
		);

		echo $session_id.'<br/>';
		Helper::factory()->getCache()->save($session_id,json_encode($data),'31536000');


		$this->redirect('bs/index') ;
	}
}