<?php
/**
 * 接口控制类
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Core\Controller;
use ScshuxCms\User\Helper\AccessToken;
class  ApiBaseController  extends BaseController
{
	protected  $_requestData = null;

	/**
	 * 发送正确请求结果
	 * @param string $data
	 */
	protected  function sendSuccessResult($data)
	{
		$result = array(
				'status' => 'y',
				'data' => $data
		);
		$this->sendJson($result);
	}

	/**
	 * 发送错误请求结果
	 * @param string $error
	 */
	protected  function sendErrorResult($error='',$erroCode=-1)
	{
		$result = array(
				'status' => 'n',
				'error' => $error,
				'errorCode'=>$erroCode
		);
		$this->sendJson($result);
	}

	/**
	 * 检查用户授权
	 * @return
	 */
	protected  function  checkAccessToken()
	{
		$postData = $this->getRequestData();
		if(empty($postData->access_token) || empty($postData->user_id))
		{
			$this->sendErrorResult('参数错误!');
		}
		$accessToken = AccessToken::get($postData->access_token,$postData->user_id);
		if(empty($accessToken))
		{
			$this->sendErrorResult('用户授权错误!',2002);
		}
		return $accessToken;
	}

	/**
	 * 获取请求数据
	 */
	protected  function  getRequestData()
	{
		if($this->_requestData === null)
		{
			$data  = $_REQUEST['data'];
			$this->_requestData =  json_decode($data);
		}
		return $this->_requestData;
	}

}