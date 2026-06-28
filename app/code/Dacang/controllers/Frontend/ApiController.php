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
	
}