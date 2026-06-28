<?php
/**
 * 钉钉接口获取
* @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
* @author kaiping.jiang <kaiping.jiang@scshux.com>
*/
namespace  ScshuxCms\Core\Helper;
use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\CompanyModel;

class Dding
{
	const APPID = 'dingoaqdzdwnsjvgzyzrho';
	const APPSECRET = 'wekaz2B3eSyJMNKQy970VXcgwFT4y0Q_tjtw8bv3LXVkOKM3oHFuKMNgdfYKyu-v';

	const OAPI_HOST = "https://oapi.dingtalk.com/sns/";

	const USERCONTENT =  '';      													//被考核人消息内容
	const REPORTUSERCONTENT = '亲，有一个新的绩效考核需要你审核，请尽快处理哦';		//考核人消息内容
	protected static $_instance = null ;

	public $error = '';

	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new Dding();
		}

		return self::$_instance;
	}


	/**
	 *
	 * @desc	获取当前登录用户 的cropaccesstoken
	 * @param	$corpid
	 * @param	$corpsecret
	 * @date	2017年5月10日
	 */
	public function getCorpAccessToken($corpid, $corpsecret)
	{
		$cache = Helper::factory()->getCache();
		$cropaccesstoken = $cache->get('crop_access_token');
		if (!$cropaccesstoken)
		{
			if(!$corpid || !$corpsecret)
			{
				$this->error = '参数错误';return false;
			}
				
			$url = "https://oapi.dingtalk.com/gettoken?corpid=".$corpid."&corpsecret=".$corpsecret;
			$response = $this->checkCurlData(Utils::httpGet($url)) ;

			$cropaccesstoken = $response->access_token;

			//设置缓存  时间为7000s
			$cache->save('crop_access_token', $cropaccesstoken,7000);
		}
		return $cropaccesstoken;
	}

	/**
	 *
	 * @desc	获取访问令牌
	 * @date	2017年5月8日
	 */
	public function getAccessToken()
	{
		$cache = Helper::factory()->getCache();
		$accesstoken = $cache->get('access_token');
		if (!$accesstoken)
		{
			if (!self::APPID)
			{
				$this->error = '请设置appid';return  false ;
			}
			if (!self::APPSECRET)
			{
				$this->error = '请设置appsecert';return  false ;
			}
				
			$url = $this->joinParams('/gettoken', array('appid' => self::APPID, 'appsecret' => self::APPSECRET));
			$response = $this->checkCurlData(Utils::httpGet($url)) ;
				
			$accesstoken = $response->access_token;
				
			//设置缓存
			$cache->save('access_token', $accesstoken);
		}
		return $accesstoken;
	}


	/**
	 *
	 * @desc	获取永久授权码
	 * @param  	零时code
	 * @date	2017年5月8日
	 */
	public function getPerCode($code)
	{
		if (!$code)
		{
			$this->error = '参数不争取，请传递临时授权码';return  false ;
		}
		$data = array(
				'tmp_auth_code' => $code
		);

		$accesstoken = $this->getAccessToken();
		$perssioncode = $this->checkCurlData(Utils::postJson("https://oapi.dingtalk.com/sns/get_persistent_code?access_token=".$accesstoken, json_encode($data))) ;

		return $perssioncode;
	}


	/**
	 * 获取用户授权的sns_token
	 * @param $openid           	用户id
	 * @param $persistent_code		永久授权码
	 */

	public function getSnsToken($openid,$persistent_code)
	{
		if(!$openid || !$persistent_code)
		{
			$this->error = '参数错误' ;return false ;
		}

		$dataarr = array(
				'openid' => $openid,
				'persistent_code' => $persistent_code
		);

		$accesstoken = $this->getAccessToken();
		$snstoken = $this->checkCurlData(Utils::postJson('https://oapi.dingtalk.com/sns/get_sns_token?access_token='.$accesstoken, json_encode($dataarr)));

		return  $snstoken;
	}



	/**
	 * 获取钉钉D
	 * @param  $snstoken
	 */
	public function getUserDdingId($snstoken)
	{
		$ddingid = '';
		if(!$snstoken)
		{
			$this->error = '参数错误，请设置snstoken';return false ;
		}

		$userinfo = $this->checkCurlData(Utils::httpGet("https://oapi.dingtalk.com/sns/getuserinfo?sns_token=".$snstoken))  ;
		if($userinfo)
		{
			$ddingid = $userinfo->user_info->dingId ;
		}
		return $ddingid ;
	}


	/**
	 *  根据unicode  获取userid
	 */
	public function getUserid($unicode)
	{
		if(!$unicode)
		{
			$this->error = '参数错误，请设置唯一码';return false ;
		}

		$accesstoken = $this->getCorpAccessToken();
		$url = 'https://oapi.dingtalk.com/user/getUseridByUnionid?access_token='.$accesstoken.'&unionid='.$unicode;
		$userid = $this->checkCurlData(Utils::httpGet($url)) ;

		if($userid)
		{
			return $userid->userid ;
		}
	}


	/**
	 * 获取成员详情
	 * @param $unicode
	 */
	public function getUserDetails($unicode)
	{
		$userid = $this->getUserid($unicode);
		$accesstoken = $this->getAccessToken();
		$url = "https://oapi.dingtalk.com/user/get?access_token=".$accesstoken."&userid=".$userid;

		$userdetails = $this->checkCurlData(Utils::httpGet($url)) ;

		return $userdetails;

	}
	/**
	 *
	 * @desc	检查curl返回的数据
	 * @date	2017年5月8日
	 */
	public function checkCurlData($response)
	{
		$response = json_decode($response) ;
		return $response;
	}




	/**
	 *
	 * @desc	生成签名
	 * @date	2017年5月8日
	 */
	public function sign($ticket, $nonceStr, $timeStamp, $url)
	{
		$plain = 'jsapi_ticket=' . $ticket .
		'&noncestr=' . $nonceStr .
		'&timestamp=' . $timeStamp .
		'&url=' . $url;
		return sha1($plain);
	}


	/**
	 *
	 * @desc	当前请求页面地址
	 * @date	2017年5月8日
	 */
	public function curPageURL()
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
	 *
	 * @desc	拼接参数
	 * @date	2017年5月8日
	 */
	private function joinParams($path, $params)
	{
		$url = self::OAPI_HOST.$path;
		if (count($params) > 0)
		{
			$url = $url . "?";
			foreach ($params as $key => $value)
			{
				$url = $url . $key . "=" . $value . "&";
			}
			$length = count($url);
			if ($url[$length - 1] == '&')
			{
				$url = substr($url, 0, $length - 1);
			}
		}
		return $url;
	}


	/**
	 *
	 * @desc	通过钉钉给企业用户发送消息
	 * @param	str   companyId   公司id
	 * @param	str  $touser   接受者的钉钉id  096333646824092543,093257413635478842,03096259041062525
	 * @param   str  $content  发送消息的正文
	 * @param   str  $msgtype  消息类别 1 kpi消息 2 积分考评消息
	 * @return
	 * @date	2017年5月9日
	 */
	public function sendMsg($companyId,$touser,$content,$msgtype=1)
	{
		if(!$companyId)
		{
			$this->error = '参数错误';	return false;
		}
		$companyinfo = CompanyModel::findFirst($companyId);
		if(!$companyinfo)
		{
			$this->sendErrorResult('错误') ;
		}

		$corpid = $companyinfo->corpid ;
		$corpsecret = $companyinfo->corpsecret ;
		$agentid = $companyinfo->agentid ;

		$accesstoken = $this->getCorpAccessToken($corpid, $corpsecret) ;

		if(!$accesstoken)
		{
			$this->error = 'token获取失败';	return false;
		}

		if(!$agentid)
		{
			$this->error = 'agentid获取失败';	return false;
		}
			
		if(!$touser)
		{
			$this->error = '请设置接受者的钉钉id';	return false;
		}

		if (!$content)
		{
			$content = self::REPORTUSERCONTENT ;
		}



		//调用钉钉接口进行消息发送
		$url = 'https://oapi.dingtalk.com/message/send?access_token='.$accesstoken;
		$data= $this->createMsgtemp($agentid, $touser, $content, 2, $msgtype);
		if (!$data)
		{
			$this->error = '获取消息模版错误'; return false;
		}

		$response = $this->checkCurlData(Utils::postJson($url, json_encode($data)));
// 		file_put_contents('sendmsg_error.log', serialize($response));
		if($response->errmsg == 'ok' && $response->errcode == 0)
		{
			return true;
		}
		else
		{
			$this->error = $response->errmsg ;
			return false;
		}
	}


	/**
	 * @desc	创建消息模版
	 * @param
	 * @return
	 */
	public function createMsgtemp($agentid,$touser,$content,$type,$msgtype)
	{
		//根据agentid  获取企业的hashkey
		$companyInfo=CompanyModel::findFirst('agentid='.$agentid);
		if (!$companyInfo)
		{
			return false;
		}
		$haskey=$companyInfo->hash_key;
		if (!$haskey)
		{
			return false;
		}
		//$type 1 text  2 link
		$type=$type?intval($type):1;
		$title=$msgtype==1?"KPI考评":'积分考评';
		$controller=$msgtype==1?'bs':'bspoint';
		
		$data=false;
		switch ($type)
		{
			case 1:
				$data=array(
						'agentid'=>$agentid,
						'touser' =>$touser,
						'msgtype'=>'text',
						'text'   =>json_encode(array(
								'title'  =>$title,
								'content'=>$content
								)),
				);
				break;
			case 2:
				$data=array(
						'agentid'=>$agentid,
						'touser' =>$touser,
						'msgtype'=>'link',
						'link'   =>json_encode(array(
								'messageUrl'=>Helper::factory()->createUrl(array('p'=>$controller.'/index/'.$haskey,'_f'=>'full')),
								'picUrl'=>Helper::factory()->createUrl(array('p'=>$controller.'/index','_f'=>'full')),
								'title' =>$title,
								'text'  =>$content
						)),
				);
				break;
		}

		return $data;
	}


	/**
	 *
	 * @desc	jsapi 获取配置信息
	 * 默认的$corpId $corpsecret $agentId 都为大仓公司
	 * @date	2017年5月16日
	 */
	public function getConfig()
	{
		$corpId = 'ding80e5f3c8db0a9a6d35c2f4657eb6378f';
		$agentId = '93270341';
		$nonceStr = uniqid();
		$timeStamp = time();
		$url = $this->curPageURL();
		$corpAccessToken = $this->getCorpAccessToken('ding80e5f3c8db0a9a6d35c2f4657eb6378f', 'Uzbxin-2Eus0IHKND2z5ECrr4LLS2yMOHJlIJ5Jv3lKcUvWx2_cICzRxbw8nOX-Q');
		if (!$corpAccessToken)
		{
			Log::e("[getConfig] ERR: no corp access token");
		}

		$ticket = $this->getTicket($corpAccessToken);
		$signature = $this->sign($ticket, $nonceStr, $timeStamp, $url);

		$config = array(
				'url' => $url,
				'nonceStr' => $nonceStr,
				'agentId' => $agentId,
				'timeStamp' => $timeStamp,
				'corpId' => $corpId,
				'signature' => $signature);
		return json_encode($config, JSON_UNESCAPED_SLASHES);
	}



	/**
	 * 缓存jsTicket。jsTicket有效期为两小时，需要在失效前请求新的jsTicket（注意：以下代码没有在失效前刷新缓存的jsTicket）。
	 */
	public  function getTicket($accessToken)
	{
		if(!$accessToken)
		{
			$this->error = 'accesstoken 错误';  return  false ;
		}
		$url = "https://oapi.dingtalk.com/get_jsapi_ticket?access_token=".$accessToken;
		$response = $this->checkCurlData(Utils::httpGet($url)) ;

		$jsticket = $response->ticket;

		return $jsticket;
	}



	/**
	 *
	 * @desc	返回错误信息
	 * @date	2017年5月9日
	 */
	public function getError()
	{
		return $this->error ;
	}

}



