<?php
/**
 * 钉钉接口
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Dacang\Helper\DingtalkCrypt;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Dacang\Helper\Dingding;
use ScshuxCms\Common\Model\ConfigModel;
use ScshuxCms\Core\Constants;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\MiniappRegistrationModel;
use ScshuxCms\User\Model\UserModel;
class  ApiController  extends FrontendBaseController
{
	
	
	public function initialize()
	{

	}
	
	public  function testAction()
	{
		Dingding::factory()->getDepartmentList(4);
	}
	
	
	public  function  dacangAction()
	{
		file_put_contents('dacang.txt', var_export($_REQUEST,TRUE));
	}
	
	
	/**
	 * 回调接口
	 */
	public function  isvreceiveAction()
	{
		$config  = $this->getHelper()->getConfig();
		$signature = $_REQUEST['signature'];
		$timestamp = $_REQUEST['timestamp'];
		$nonce     = $_REQUEST['nonce'];
		$requestData = file_get_contents('php://input');
		$requestData = json_decode($requestData);
		$encrypt   = $requestData->encrypt;
		$decryptMsg = '';
	    $dingtalkCrypt = new DingtalkCrypt($config->suite_token,$config->encodingaeskey,$config->suite_key);
	    $result = $dingtalkCrypt->DecryptMsg($signature,$timestamp,$nonce, $encrypt, $decryptMsg);
	    if($decryptMsg)
	    {
	    	$decryptMsgObj  = json_decode($decryptMsg);
	    	if($decryptMsgObj->EventType == 'check_create_suite_url')//验证url ，直接返回随机数加密
	    	{
	    		$result = '';
	    		$dingtalkCrypt->EncryptMsg($decryptMsgObj->Random, $timestamp, $nonce, $result);
	    		$this->sendJson($result);
	    		
	    	}elseif ($decryptMsgObj->EventType == 'check_update_suite_url')//更新url
	    	{
	    		$result = '';
	    		$dingtalkCrypt->EncryptMsg($decryptMsgObj->Random, $timestamp, $nonce, $result);
	    		$this->sendJson($result);
	    	}
	    	elseif ($decryptMsgObj->EventType == 'tmp_auth_code')//临时授权code
	    	{
    		    $timestamp = $decryptMsgObj->TimeStamp;
    		    //绑定或者生成企业
    		    $dingdingObj = new Dingding();
    		    $doresult = $dingdingObj->bindOrCreateCompany($decryptMsgObj->AuthCode);
    		    if(!$doresult)
    		    {
    		    	Dingding::factory()->log('绑定错误',$decryptMsg);
    		    }
    			$result = '';
    		    $dingtalkCrypt->EncryptMsg('success', $timestamp, $nonce, $result);
    			$this->sendJson($result);
	    			
	    	}elseif ($decryptMsgObj->EventType == 'suite_ticket')//定时推送Ticket
	    	{
	    		$suiteTicket  = $decryptMsgObj->SuiteTicket;
	    		$suiteTicketTime = $this->getHelper()->getTime()->gmtime();
	    		$suiteTicketTime = $this->getHelper()->getTime()->localDate('Y-m-d H:i:s',$suiteTicketTime);
	    		$configModel = ConfigModel::findFirst("code='suite_ticket'");
	    		if($configModel)
	    		{
	    			$configModel->save(array('value'=>$suiteTicket));
	    		}
	    		$configModel = ConfigModel::findFirst("code='suite_ticket_time'");
	    		if($configModel)
	    		{
	    			$configModel->save(array('value'=>$suiteTicketTime));
	    		}
	    		
	    		//清楚缓存
	    		$keyName = Constants::CACHE_SYSTEM_CONFIG;
	    		$this->getHelper()->getCache()->delete($keyName);
	    		
    			$result = '';
    			$dingtalkCrypt->EncryptMsg('success', $timestamp, $nonce, $result);
    			$this->sendJson($result);
    			
	    	}elseif ($decryptMsgObj->EventType == 'change_auth')//授权变更消息
	    	{
    			$result = '';
    			$dingtalkCrypt->EncryptMsg('success', $timestamp, $nonce, $result);
    			$this->sendJson($result);
	    	}
	    	
	    }else{
	    	exit('');
	    }
	}
	

	/**
	 * 企业自主提交小程序开通申请，审核通过前不创建企业和账号。
	 */
	public function miniappregisterAction()
	{
		if (!$this->request->isPost()) {
			$this->sendErrorResult('仅支持 POST 请求');
		}

		$data = $_POST;
		$raw = file_get_contents('php://input');
		if ($raw) {
			$json = json_decode($raw, true);
			if (is_array($json)) {
				$data = array_merge($data, $json);
			}
		}

		$companyName = isset($data['company_name']) ? trim($data['company_name']) : '';
		$contactName = isset($data['contact_name']) ? trim($data['contact_name']) : '';
		$adminMobile = isset($data['admin_mobile']) ? trim($data['admin_mobile']) : '';
		$industry = isset($data['industry']) ? trim($data['industry']) : '';
		$address = isset($data['address']) ? trim($data['address']) : '';

		if ($companyName === '' || $contactName === '') {
			$this->sendErrorResult('请填写企业名称和联系人姓名');
		}
		if (!preg_match('/^1[3-9]\d{9}$/', $adminMobile)) {
			$this->sendErrorResult('请填写正确的11位手机号');
		}
		if (CompanyModel::factory()->findFirst('name="' . addslashes($companyName) . '"')) {
			$this->sendErrorResult('该企业已经开通，请直接登录或联系运营人员');
		}
		if (UserModel::factory()->findFirst(
			'(user_name="' . addslashes($adminMobile) . '" or phone="' . addslashes($adminMobile) . '") and company_id>0'
		)) {
			$this->sendErrorResult('该手机号已经绑定企业，请直接登录或联系运营人员');
		}

		$pending = MiniappRegistrationModel::factory()->findFirst(
			'status="pending" and (admin_mobile="' . addslashes($adminMobile)
			. '" or company_name="' . addslashes($companyName) . '")'
		);
		if ($pending) {
			$this->sendSuccessResult(array(
				'application_id' => intval($pending->id),
				'status' => 'pending',
				'message' => '申请已提交，请等待运营人员审核'
			));
		}

		$now = time();
		$application = new MiniappRegistrationModel();
		$result = $application->save(array(
			'company_name' => $companyName,
			'industry' => $industry,
			'contact_name' => $contactName,
			'admin_mobile' => $adminMobile,
			'address' => $address,
			'person_limit' => 20,
			'status' => 'pending',
			'created_at' => $now,
			'updated_at' => $now
		));
		if (!$result) {
			$this->sendErrorResult('申请提交失败，请稍后重试');
		}

		$this->sendSuccessResult(array(
			'application_id' => intval($application->id),
			'status' => 'pending',
			'message' => '申请已提交，请等待运营人员审核'
		));
	}
}