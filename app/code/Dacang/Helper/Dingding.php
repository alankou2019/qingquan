<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Helper;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\User\Model\UserModel;
use ScshuxCms\Dacang\Model\CompanyDepartModel;
use ScshuxCms\Dacang\Model\CompanyUserModel;
class  Dingding
{
	const  CACHE_SUITE_TOKEN = 'dingding_suite_token';
	
	protected  $_config = null;
	
	protected static $_dingding = null;
	
	/**
	 * 返回实例
	 * @return \ScshuxCms\Dacang\Helper\Dingding
	 */
	public static  function factory()
	{
		if(self::$_dingding==null)
		{
			self::$_dingding = new  Dingding();
		}
		return self::$_dingding;
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
	
	
	public  function  getCompanyByCorpid($corpid)
	{
		$company = CompanyModel::factory()->findFirst("corpid='{$corpid}'");
		return $company;
	}
	
	/**
	 * 通过code获取用户信息
	 * @param  $corpid
	 * @param  $code
	 */
	public  function  getOauthUserInfo($corpid,$code)
	{
		$company = $this->getCompanyByCorpid($corpid);
		if(empty($company))
		{
			return false;
		}
		$access_token = $this->getCorpTokenByCompany($company);
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
			$user  = $this->createCompanyUserById($result->userid, $company);
			return array(
					'dingding_user_id'=> $result->userid,
					'is_sys'=> $is_sys,
					'user_id' => $user->id,
					'company_id'=>$user->company_id
			);
		}
		return false;
	}
	
	/**
	 * 通过钉钉用户编号创建用户
	 * @param  $userId
	 * @param  $company
	 * @return \Phalcon\Mvc\Model
	 */
	public  function createCompanyUserById($userId,$company)
	{
		if(!is_numeric($company))
		{
			$company = $company->id;
		}
		$companyUser = $this->getCompanyUserById($userId, $company);
		
		$data = array(
				'company_id' => $company,
				'dingding_user_id' => $userId
		);
		
		$dingdingdUserInfo  = $this->getFullUserInfo($company, $userId);
		if(!empty($dingdingdUserInfo))
		{
			if($dingdingdUserInfo->isAdmin)
			{
				$data['is_admin']=1;
			}else 
			{
				$data['is_admin']=0;
			}
			
			$data['name'] = $dingdingdUserInfo->name;
			$data['avatar'] = $dingdingdUserInfo->avatar;
			$data['dingid'] = $dingdingdUserInfo->dingId;
			$data['unionid'] = $dingdingdUserInfo->unionid;
			$data['department_id'] = max($dingdingdUserInfo->department);
		}
		if(empty($companyUser))
		{
			$companyUser = new CompanyUserModel();
			$data['created'] =  Helper::factory()->getTime()->gmtime();
			$companyUser->saveData($data);
		}
		else
		{
			$companyUser->saveData($data) ;
		}
		return  $companyUser;
	}
	
	/**
	 * 获取用户
	 * @param  $userId 钉钉编号
	 * @param  $company 企业编号
	 */
	public  function  getCompanyUserById($userId,$company)
	{
		if(!is_numeric($company))
		{
			$company = $company->id;
		}
		$companyuser = new CompanyUserModel();
		return  $companyuser->findFirst("dingding_user_id='{$userId}' and company_id='{$company}'");
	}
	
	/**
	 * 获取用户的信息
	 * @param  $company
	 * @param  $user_id
	 * @return NULL
	 */
	public  function  getFullUserInfo($company,$user_id)
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
		
		$accessToken = $this->getCorpTokenByCompany($company);
		$url = 'https://oapi.dingtalk.com/user/get?access_token='.$accessToken.'&userid='.$user_id;
		$result = Utils::httpGet($url);
		$this->log('user_get',$result);
		
		$result = json_decode($result);
		if($result->errmsg=='ok')
		{
			return $result;
		}
		
		return null;
	}
	
	/**
	 * isv
	 * @param unknown $company
	 * @return NULL|string
	 */
	public  function isvConfig($company)
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
		if(empty($company->corpid) || empty($company->permanent_code) || empty($company->agentid))
		{
			return null;
		}

		$corpId = $company->corpid;
		$agentId = $company->agentid;
		$nonceStr = 'abcdefg';
		$timeStamp = Helper::factory()->getTime()->gmtime();
		$url = $this->getCurpage();
		$accessToken = $this->getCorpTokenByCompany($company);
		$ticket = $this->getTicket($corpId,$accessToken);
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
				'suite_key' => $this->getConfig('suite_key'),
				'signature' => $signature
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
		
		file_put_contents('dingding.log', $msg,FILE_APPEND);
	}
	
	/**
	 * 获取系统配置
	 * @return \stdClass
	 */
	public  function  getConfig($keyname=null)
	{
		if($this->_config == null)
		{
		   $this->_config = Helper::factory()->getConfig();
		}
		if($keyname)
		{
			return $this->_config->{$keyname};
		}
		return $this->_config;
	}
	
	/**
	 * 绑定或者创建企业
	 */
	public  function  bindOrCreateCompany($authCode)
	{
		if(empty($authCode)) return false;
		
		$permanentInfo  = $this->getPermanentCode($authCode);
		if(empty($permanentInfo))//授权错误
		{
			return false;
		}
		//获取企业信息
		$authfo = $this->getAuthInfo($permanentInfo['corpid']);
		if($authfo)
		{
			
			$companyData = array(
					'name' => $authfo->auth_corp_info->corp_name,
					'corpid' =>  $permanentInfo['corpid'],
					'corp_name' => $authfo->auth_corp_info->corp_name,
					'permanent_code' => $permanentInfo['permanent_code'],
					'join_time' => Helper::factory()->getTime()->gmtime(),
					'industry' => $authfo->auth_corp_info->industry,
					'auth_level' => $authfo->auth_corp_info->auth_level,
					'invite_url' => $authfo->auth_corp_info->invite_url,
					'corp_logo_url' => $authfo->auth_corp_info->corp_logo_url,
					'ext_data' => json_encode($authfo),
					'expire_time' => Helper::factory()->getTime()->gmstr2time('+30 days'),
					'agentid' => $authfo->auth_info->agent[0]->agentid
			);
			
			$company_id  = 0;
			//查询企业
			$companyModel = CompanyModel::findFirst("corpid='{$companyData['corpid']}'");
			if(empty($companyModel))
			{
				$companyData['created'] = Helper::factory()->getTime()->gmtime();
				$companyData['status'] = 1;
				$companyData['hash_key'] = md5(microtime(true));
				$companyModel = CompanyModel::factory();
				$companyModel->saveData($companyData);
				$company_id = $companyModel->id;
				
				//生成一个顶级部门
				$data = array(
						'name' => $companyData['name'],
						'dingding_id' => 1,
						'dingding_parent_id' => 0,
						'company_id' => $company_id
				);
				$companyDepartModel= new CompanyDepartModel();
				$companyDepartModel->saveData($data);
				
			}else
			{
				$company_id = $companyModel->id;
				$companyModel->saveData($companyData);
			}
			
			//查询当前是否有管理员
			$userModel = UserModel::factory()->findFirst('company_id='.$company_id.' and is_admin=1');
			if(empty($userModel))
			{
				$userModel = UserModel::factory();
				$result = $userModel->saveData(array(
					'is_admin' => 1,
					'company_id' => $company_id,
					'created' => Helper::factory()->getTime()->gmtime(),
					'reg_ip'  => Utils::getIP()
				));
				
				if($result)
				{
					$userModel->saveData(array(
						'user_name' => 'dacang'.$userModel->user_id,
						'password'  => md5('dacang2017')
					));
				}
			}
			
			//激活插件
			$result = $this->activateSuite($permanentInfo['corpid'],$permanentInfo['permanent_code']);
			if(empty($result))
			{
				return false;
			}else{
				return true;
			}
			
		}
		return false;
	}
	
	/**
	 * 获取套件访问Token
	 * @param string $suite_key
	 * @param string $suite_secret
	 * @param string $suite_ticket
	 * @return NULL
	 */
	public  function  getSuiteToken()
	{
		$access_token = Helper::factory()->getCache()->get(Dingding::CACHE_SUITE_TOKEN,6000);
		if(empty($access_token))
		{
			$data = array(
					'suite_key' =>  $this->getConfig('suite_key'),
					'suite_secret' => $this->getConfig('suite_secret'),
					'suite_ticket' => $this->getConfig('suite_ticket')
			);
			$result = $this->doJsonRequest('https://oapi.dingtalk.com/service/get_suite_token', $data);
			$this->log('getSuiteToken',$result);
			$result = json_decode($result);
			if($result->errmsg == 'ok' && empty($result->errcode))
			{
				$access_token = $result->suite_access_token;
				Helper::factory()->getCache()->save(Dingding::CACHE_SUITE_TOKEN,$access_token,6000);
			}
		}
		return $access_token;
		
	}

	
	/**
	 * 获取企业的永久授权码
	 * @return NULL
	 */
	public  function  getPermanentCode($authCode)
	{
		$url  = 'https://oapi.dingtalk.com/service/get_permanent_code?suite_access_token='.$this->getSuiteToken();
		$data = array(
				'tmp_auth_code' => $authCode
		);
		$result = $this->doJsonRequest($url, $data);
		$this->log('getPermanentCode',$result);
		$result = json_decode($result);
		if($result->errmsg == 'ok' && empty($result->errcode))
		{
			return array(
					'corpid'=>$result->auth_corp_info->corpid,
					'corp_name'=>$result->auth_corp_info->corp_name,
					'permanent_code'=>$result->permanent_code
			);
		}
		return null;
		
	}
	
	/**
	 * 通过企业编号获取授权
	 * @param  $company
	 * @return NULL|Ambigous <NULL, \Phalcon\Cache\Backend\mixed>
	 */
	public function  getCorpTokenByCompany($company)
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
		if(empty($company->corpid) || empty($company->permanent_code))
		{
			return null;
		}
		return  $this->getCorpToken($company->corpid, $company->permanent_code);
		
	}
	
	/**
	 * 获取企业授权的access_token
	 * @return NULL
	 */
	public function  getCorpToken($auth_corpid,$permanent_code)
	{
		$keyname = 'dingding_token_'.md5($auth_corpid);
		$token = Helper::factory()->getCache()->get($keyname,6000);
		if(empty($token))
		{
			$url  = 'https://oapi.dingtalk.com/service/get_corp_token?suite_access_token='.$this->getSuiteToken();
			$data = array(
					'auth_corpid' => $auth_corpid,
					'permanent_code' => $permanent_code
			);
			$result = $this->doJsonRequest($url, $data);
			$this->log('getCorpToken',$result);
			$result = json_decode($result);
		    if($result->errmsg == 'ok' && empty($result->errcode))
			{
				$token = $result->access_token;
				Helper::factory()->getCache()->save($keyname,$token,6000);
			}
		}
		return $token;;
	}
	
	/**
	 * 激活授权套件
	 * @return NULL
	 */
	public  function  activateSuite($auth_corpid,$permanent_code)
	{
		$url  = 'https://oapi.dingtalk.com/service/activate_suite?suite_access_token='.$this->getSuiteToken();
		$data = array(
				'suite_key' => $this->getConfig('suite_key'),
				'auth_corpid' => $auth_corpid,
				'permanent_code' => $permanent_code
		);
		$result = $this->doJsonRequest($url, $data);
		$this->log('activateSuite',$result);
		$result = json_decode($result);
		if($result->errmsg == 'ok' && empty($result->errcode))
		{
			return true;
		}
		return  false;
	}
	
	/**
	 * 获取企业授权的授权数据
	 * @return NULL
	 */
	public function  getAuthInfo($auth_corpid)
	{
		$url  = 'https://oapi.dingtalk.com/service/get_auth_info?suite_access_token='.$this->getSuiteToken();
		$data = array(
				'suite_key' => $this->getConfig('suite_key'),
				'auth_corpid' => $auth_corpid
		);
		$result = $this->doJsonRequest($url, $data);
		$this->log('getAuthInfo',$result);
		$result = json_decode($result);
		if($result->errmsg == 'ok' && empty($result->errcode))
		{
			return $result;
		}
		return null;
	}
	
	/**
	 * 获取公司部门
	 * @param  $company
	 */
	public  function  getDepartmentList($company)
	{
		
		$access_token = $this->getCorpTokenByCompany($company);
		if(empty($access_token))
		{
			return null;
		}
		$url = 'https://oapi.dingtalk.com/department/list?access_token='.$access_token.'';
		$result =  Utils::httpGet($url);
		$this->log('getDepartmentList',$result);
		if($result->errmsg == 'ok' && empty($result->errcode))
		{
			return $result;
		}
		return null;
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
		$token = $this->getCorpTokenByCompany($company);
		if(empty($token)) return null;
		
		$result = $this->getDepartmentList($company);
		print_r($result);
		exit;
		
		$this->log('auth_scopes',$result);
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
	 * 获取企业员工人数
	 * @param  $company_id
	 * @return NULL
	 */
	public  function  getOrgUserCount($company_id)
	{
		$access_token = $this->getCorpTokenByCompany($company_id);
		if(empty($access_token))
		{
			return null;
		}
		$url = 'https://oapi.dingtalk.com/user/get_org_user_count?onlyActive=0&access_token='.$access_token;
		$result =  Utils::httpGet($url);
		$this->log('获取员工总数',$result);
		$result = json_decode($result);
		if($result->errmsg == 'ok' && empty($result->errcode))
		{
			return  $result->count;
		}else{
			$this->log('getOrgUserCount',$result);
		}
		return  -1;
	    
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