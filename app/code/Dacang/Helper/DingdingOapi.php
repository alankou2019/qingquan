<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Helper;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\CompanyDepartModel;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\User\Model\UserModel;
class  DingdingOapi
{
	protected static $_dingding = null;
	
	/**
	 * 返回实例
	 * @return \ScshuxCms\Dacang\Helper\DingdingOapi
	 */
	public static  function factory()
	{
		if(self::$_dingding==null)
		{
			self::$_dingding = new  DingdingOapi();
		}
		return self::$_dingding;
	}
	
	/**
	 * 钉钉后台直接登录
	 * @param unknown $hash_key
	 * @param unknown $code
	 * @return NULL
	 */
	public    function  LoginByCode($hash_key,$code)
	{
		if(empty($hash_key)) return  null;
		
		$company = CompanyModel::findFirst("hash_key='{$hash_key}'");
		if(empty($company))
		{
			return null;
		}
		
		if(empty($company->corpid) || empty($company->corpsecret) || empty($company->agentid) || empty($company->ssosecret))
		{
			return null;
		}
		$token = $this->getAdminToken($company->corpid,$company->ssosecret);
		if(empty($token))
		{
			return null;
		}
		
		$url = 'https://oapi.dingtalk.com/sso/getuserinfo?access_token='.$token.'&code='.$code;
		$result = Utils::httpGet($url);
		$this->log('getuserinfo',$result);
		$resultObj = json_decode($result);
		if($resultObj->errmsg == 'ok')
		{
			
			//查询企业使用时间是否已经到期
			$expire_time = $company->expire_time ;
			
			if(($expire_time != -1) && ($expire_time < time()))
			{
				return false;
			}
			
			$user = UserModel::findFirst('company_id='.intval($company->id));
			if(empty($user))
			{
				return false;
			}
			
			
			UserModel::factory()->loginAfter($user,$company);
			
			return true;
			
		}
		
		return null;
	}
	
	/**
	 * 通过企业编号获取授权
	 * @param  $company
	 * @return NULL|Ambigous <NULL, \Phalcon\Cache\Backend\mixed>
	 */
	public function  getTokenByCompany($company)
	{
		if(empty($company)) return  null;
		
		if(is_numeric($company))
		{
			$company = CompanyModel::findFirst('id='.intval($company));
		}
		if(empty($company))
		{
			return null;
		}
		if(empty($company->corpid) || empty($company->corpsecret))
		{
			return null;
		}
		return  $this->getToken($company->corpid, $company->corpsecret);
	
	}
	
	
	public  function  log($msg,$extdata=null)
	{
		$msg = Helper::factory()->getTime()->localDate('Y-m-d H:i:s',Helper::factory()->getTime()->gmtime()).':'.$msg.PHP_EOL;
		if(!empty($extdata))
		{
			if(is_array($extdata) || is_object($extdata))
			{
				$extdata = var_export($extdata,true);
			}
			$msg .= '扩展数据:'.$extdata.PHP_EOL;
		}
		$msg .= PHP_EOL;
	
		file_put_contents('dingding_aoapi.log', $msg,FILE_APPEND);
	}
	
	
	/**
	 * 获取授权
	 */
	public    function  getAdminToken($corpid='',$ssosecret='')
	{
		$keyName = md5('admin_token_'.$corpid.'_'.$ssosecret);
		$result =  Helper::factory()->getCache()->get($keyName);
		if(empty($result))
		{
			$url = 'https://oapi.dingtalk.com/sso/gettoken?corpid='.$corpid.'&corpsecret='.$ssosecret;
			$result = Utils::httpGet($url);
			$this->log('sso_gettoken',$result);
			$resultObj = json_decode($result);
			if($resultObj->errmsg == 'ok')
			{
				Helper::factory()->getCache()->save($keyName,$resultObj->access_token,6000);
				return $resultObj->access_token;
				
			}else{
				return null;
			}
		}
		return $result;
		
	}
	
	
	/**
	 * 获取授权
	 */
	public    function  getToken($corpid='',$corpsecret='',$time='')
	{

		$keyName = md5('token_'.$corpid.'_'.$corpsecret);
		$result =  Helper::factory()->getCache()->get($keyName);
		if(empty($result))
		{
            $url = 'https://oapi.dingtalk.com/gettoken?corpid='.$corpid.'&corpsecret='.$corpsecret;
            if ($time && $time > 1546272001 ){
                $url = 'https://oapi.dingtalk.com/gettoken?appkey='.$corpid.'&appsecret='.$corpsecret;
            }
			$result = Utils::httpGet($url);
			$this->log('Token',$result);
			$resultObj = json_decode($result);
			if($resultObj->errmsg == 'ok')
			{
				Helper::factory()->getCache()->save($keyName,$resultObj->access_token,6000);
				return $resultObj->access_token;
				
			}else{
				return null;
			}
		}
		return $result;
	
	}
	
	
	/**
	 * 通过code获取用户信息
	 * @param  $hash_key
	 * @param  $code
	 */
	public  function  getOauthUserInfo($hash_key,$code)
	{
		$companyModel = new CompanyModel();
		$company = $companyModel->findFirst("hash_key='{$hash_key}'");
		if(empty($company))
		{
			return false;
		}
		$access_token = $this->getTokenByCompany($company);
		$url = 'https://oapi.dingtalk.com/user/getuserinfo?access_token='.$access_token.'&code='.$code;
		$result = Utils::httpGet($url);
		$this->log('getuserinfo',$result);
		$result = json_decode($result);
		if($result->errmsg=='ok')
		{
			$is_sys = false;
			if($result->sys_level==1 || $result->sys_level==2)
			{
				$is_sys = true;
			}
			
			//查询用户
			$company_userModel = new CompanyUserModel();
			$company_user = $company_userModel->findFirst("dingding_user_id='{$result->userid}' and company_id=".$company->id);
			
			return array(
					'dingding_user_id'=> $result->userid,
					'is_sys'=> $is_sys,
					'user_id' => $company_user->id,
					'company_id'=>$company_user->company_id
			);
		}
		return false;
	}
	
	
	/**
	 * 缓存jsTicket。jsTicket有效期为两小时，需要在失效前请求新的jsTicket（注意：以下代码没有在失效前刷新缓存的jsTicket）。
	 */
	public  function getTicket($corpId,$accessToken)
	{
		$jsticket = Helper::factory()->getCache()->get('js_ticket_'.$corpId,3600);
		if (!$jsticket)
		{
			$response = Utils::httpGet('https://oapi.dingtalk.com/get_jsapi_ticket?type=jsapi&access_token='.$accessToken);
			$this->log('get_jsapi_ticket',$response);
			$response = json_decode($response);
			if($response->errmsg=='ok')
			{
				Helper::factory()->getCache()->save('js_ticket_'.$corpId,$response->ticket,3600);
				$jsticket = $response->ticket;
			}
		}
		return $jsticket;
	}
	
	
	
	
	public  function  getJsConfig($company_haskey)
	{
		
		if(empty($company_haskey)) return  null;
		
		$company = CompanyModel::findFirst("hash_key='{$company_haskey}'");
		if(empty($company))
		{
			return null;
		}
		
		if(empty($company->corpid) || empty($company->corpsecret) || empty($company->agentid))
		{
			return null;
		}
	
		$corpId = $company->corpid;
		$agentId = $company->agentid;
		$nonceStr = 'abcdefg';
		$timeStamp = Helper::factory()->getTime()->gmtime();
		$url = $this->getCurpage();
		
		$accessToken = $this->getToken($company->corpid,$company->corpsecret,$company->created);
		
		$ticket = $this->getTicket($company->corpid,$accessToken);
		if(empty($ticket))
		{
			return null;
		}
		$signature = $this->sign($ticket, $nonceStr, $timeStamp, $url);
		$arr = array();
		$arr['ticket'] = $ticket;
		$arr['nonceStr'] = $nonceStr;
		$arr['timeStamp'] = $timeStamp;
		$arr['url'] = $url;
		$arr['signature'] = $signature;
		
		return  array(
				'url' => $url,
				'nonceStr' => $nonceStr,
				'agentId' => $agentId,
				'timeStamp' => $timeStamp,
				'corpId' => $corpId,
				'signature' => $signature,
                'true_corpId'=>$company->remark ? $company->remark : $corpId,
                'is_new' => $company->remark ? 1 : 0
		);
	}
	
	/**
	 * 签名
	 * @param  $ticket
	 * @param  $nonceStr
	 * @param  $timeStamp
	 * @param  $url
	 * @return string
	 */
	public  function sign($ticket, $nonceStr, $timeStamp, $url)
	{
		$plain = 'jsapi_ticket=' . $ticket .
		'&noncestr=' . $nonceStr .
		'&timestamp=' . $timeStamp .
		'&url=' . $url;
		file_put_contents('ticket', $plain);
		return sha1($plain);
	}
	
	
	/**
	 * 获取当前url
	 * @return string
	 */
	public  function  getCurpage()
	{
		$pageURL = 'http';
		
		if (array_key_exists('HTTPS',$_SERVER)&&$_SERVER["HTTPS"] == "on")
		{
			$pageURL .= "s";
		}
		$pageURL .= "://";
		
		if ($_SERVER["SERVER_PORT"] != "80")
		{
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		}
		else
		{
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	/**
	 * 获取部门信息
	 * @param  $company
	 */
	public  function  getDepartment($company)
	{
		
		if(is_numeric($company))
		{
			$company = CompanyModel::findFirst('id='.intval($company));
		}
		$token = $this->getTokenByCompany($company);
		if(empty($token)) return null;
		
		$url = 'https://oapi.dingtalk.com/department/list?access_token='.$token;
		$result = Utils::httpGet($url);
		$this->log($result);
		$resultObj = json_decode($result);
		if($resultObj->errmsg == 'ok')
		{
			return $resultObj->department;
		}
		return null;
	}
	
	
	/**
	 * 同步企业部门和人员
	 * @param unknown $company
	 */
	public  function  asyncDepartment($company)
	{
		if(is_numeric($company))
		{
			$company = CompanyModel::findFirst('id='.intval($company));
		}
		
		$department = $this->getDepartment($company);
		if(empty($department))
		{
			return false;
		}
		$currentIds = array();
		foreach ($department as $item)
		{
			$companyDepartModel = new  CompanyDepartModel();
			
			$data = array(
					'name' => $item->name,
					'dingding_id' => $item->id,
					'dingding_parent_id' => $item->parentid,
					'company_id' => $company->id
			);
			$companyDepartModel = $companyDepartModel->findFirst('company_id='.$company->id.' and dingding_id='.$item->id);
			if(empty($companyDepartModel))
			{
				$companyDepartModel = new  CompanyDepartModel();
				$companyDepartModel->saveData($data);
				$currentIds[] = $companyDepartModel->id;
			}else
			{
				$companyDepartModel->saveData($data);
				$currentIds[] = $companyDepartModel->id;
			}
		}
		if(!empty($currentIds))
		{
			CompanyDepartModel::factory()->deleteBySql(' company_id = '.$company->id.' and id not in('.join(',', $currentIds).')');
		}
		return true;
	}
	
	/**
	 * 异步企业人员信息
	 * @param  $company
	 */
	public  function  asyncSimplelist($company)
	{
		if(is_numeric($company))
		{
			$company = CompanyModel::findFirst('id='.intval($company));
		}
		
		$items = CompanyDepartModel::factory()->find('company_id='.intval($company->id));

		$userids = array();
		foreach ($items as $item)
		{
			//$users =  $this->getSimplelist($company, $item->dingding_id);
			//获取用户详细信息
			$users =  $this->getUserlist($company, $item->dingding_id);
			foreach ($users as $user)
			{
				$companyUser = new CompanyUserModel();
				$companyUser = $companyUser->findFirst("company_id='{$company->id}' and dingding_user_id='{$user->userid}'");
				$data = array(
						'company_id' => $company->id,
						'department_id' => $item->dingding_id,
						'dingding_user_id' => $user->userid,
						'name' => $user->name,
						'avatar' => $user->avatar,
						'dingid' => $user->dingId                    //钉钉的id  登录的时候用来比对数据
				);
				if(empty($companyUser))
				{
					$companyUser = new CompanyUserModel();
					$data['created'] =  Helper::factory()->getTime()->gmtime();
					$companyUser->saveData($data);
					$userids[] = $companyUser->id ;
				}
				else 
				{
					$companyUser->saveData($data) ;
					$userids[] = $companyUser->id ;
				}
			}
		}
		if(!empty($userids))
		{
			CompanyUserModel::factory()->deleteBySql(' company_id = '.$company->id.' and id not in('.join(',', $userids).')') ;
		}
		
		return true;	
	}
	
	/**
	 * 获取部门人员
	 * @param  $company
	 * @param  $department_id
	 */
	public  function  getSimplelist($company,$department_id)
	{
		
		if(is_numeric($company))
		{
			$company = CompanyModel::findFirst('id='.intval($company));
		}
		
		$token = $this->getTokenByCompany($company);
		if(empty($token)) return null;
		
		$url = 'https://oapi.dingtalk.com/user/simplelist?access_token='.$token.'&department_id='.$department_id.'&offset=0&size=100&order=entry_asc';
		$result = Utils::httpGet($url);
		$this->log($result);
		$resultObj = json_decode($result);
		if($resultObj->errmsg == 'ok')
		{
			return $resultObj->userlist;
		}
		return null;
		
	}
	
	
	/**
	 * 
	 * @desc	获取用户列表（详情）
	 * @param	$company	
	 * @return	$department_id
	 * @date	2017年5月9日
	 */
	public function getUserlist($company,$department_id)
	{
		if(is_numeric($company))
		{
			$company = CompanyModel::findFirst('id='.intval($company));
		}
		
		$token = $this->getTokenByCompany($company);
		if(empty($token)) return null;
		
		$url = 'https://oapi.dingtalk.com/user/list?access_token='.$token.'&department_id='.$department_id.'&offset=0&size=100&order=entry_asc';
		$result = Utils::httpGet($url);
		$this->log($result);
		$resultObj = json_decode($result);
		if($resultObj->errmsg == 'ok')
		{
			return $resultObj->userlist;
		}
		return null;
	}
	

	/**
	 * 执行json请求
	 * @param  $url
	 * @param  $data
	 * @return mixed
	 */
	public  function  doJsonRequest($url,$data)
	{
		$data = json_encode($data);
		$result = Utils::httpPost(
				$url,$data ,40,array(
						'Content-Type: application/json',
						'Content-Length: ' . strlen($data)));
		return $result;
	
	}
	
}