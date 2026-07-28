<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Api\Controller;
use ScshuxCms\Core\Controller\ApiBaseController;
use ScshuxCms\User\Model\UserModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use ScshuxCms\User\Helper\AccessToken;
use ScshuxCms\Cms\Model\AnnouncementLogModel;
use ScshuxCms\Cms\Model\AnnouncementModel;
use ScshuxCms\Gas\Model\GasCouponModel;
use ScshuxCms\Gas\Model\GasStationModel;
class UserController  extends ApiBaseController
{
	
	/**
	 * 用户信息
	 */
	public  function  infoAction()
	{
		$accessToken = $this->checkAccessToken();
		$postData = $this->getRequestData();
		if(empty($postData->old_password || empty($postData->new_password)))
		{
			$this->sendErrorResult('参数错误!');
		}
		$user = UserModel::factory()->loadUserByPhone($accessToken['phone']);
		if(empty($user)){
			$this->sendErrorResult('用户信息获取错误!');
		}
		
		$data = array(
			 'user_id' => $user->user_id,
			 'phone'  => $user->phone,
			 'head_ico'=> Helper::factory()->getFullPic($user->avatar),
			 'last_ip' => $user->last_ip,
			 'created' => Helper::factory()->getTime()->localDate('Y-m-d H:i:s',$user->created),
			 'last_time' => Helper::factory()->getTime()->localDate('Y-m-d H:i:s',$user->last_time),
			 'is_gas_station'=>'n',
			 'gas_station_qrcode'=>'',
			 'gas_station_name' =>'',
			 'invoice_amount' => 0,
			 'message_count' => AnnouncementLogModel::factory()->getUserMessageCount($user->user_id),
			 'coupon_count' => GasCouponModel::factory()->getCountByUserId($user->user_id)
		);
		
		//查询当前用户是否是加油站
		$gasStation = GasStationModel::factory()->findFirst('user_id='.$user->user_id);
		if($gasStation)
		{
			$qrcode ='station://'.$gasStation->id;
			$qrcode = Utils::makeQrcode($qrcode);
			$data['is_gas_station'] = 'y';
			$data['gas_station_qrcode'] = $this->getHelper()->getFullPic($qrcode);
			$data['gas_station_name'] = $gasStation->name;
		}
		$this->sendSuccessResult($data);
	}
	
	
	/**
	 * 用户注册
	 */
	public function  registerAction()
	{
		
		$postData = $this->getRequestData();
		if(empty($postData->code) || empty($postData->password) || !Utils::isMobile($postData->phone))
		{
			$this->sendErrorResult('参数错误!');
		}
		$cacheKey = sprintf('sms_%s_%s','register',$postData->phone);
		$vcode = $this->getHelper()->getCache()->get($cacheKey);
		if($vcode != $postData->code )
		{
			$this->sendErrorResult('验证码错误!');
		}
		if(strlen($postData->password)!=32)
		{
			$postData->password = md5($postData->password);
		}
		//检查电话号码是否存在
		$user = UserModel::factory()->loadUserByPhone($postData->phone);
		if($user){
			$this->sendErrorResult('手机号码已经存在!');
		}
		$user = UserModel::factory();
		$result = $user->saveData(array(
				'user_name' => $postData->phone,
				'phone'     => $postData->phone,
				'password'  => $postData->password,
				'created'   => $this->getHelper()->getTime()->gmtime(),
				'reg_ip'    => Utils::getIP()
		));
		if($result){
			
			$user->addLoginLog();
			$accessToken = AccessToken::add($user->user_id, $postData->phone);
			$this->sendSuccessResult($accessToken);
			
		}else{
			$this->sendErrorResult('注册失败!');
		}
	}
	
	/**
	 * 用户登录
	 */
	public function loginAction()
	{
		$postData = $this->getRequestData();
		if(empty($postData->password) || !Utils::isMobile($postData->phone))
		{
			$this->sendErrorResult('参数错误!');
		}
		$user = UserModel::factory()->loadUserByPhone($postData->phone);
		if(empty($user)){
			$this->sendErrorResult('手机号码不存在!',2001);
		}
		if(strlen($postData->password)!=32)
		{
			$postData->password = md5($postData->password);
		}
		if(strtolower($postData->password)!=strtolower($user->password))
		{
			$this->sendErrorResult('用户和密码不正确!',2001);
		}
		$accessToken = AccessToken::add($user->user_id, $postData->phone);
		$user->addLoginLog();
		$this->sendSuccessResult($accessToken);
		
	}
	
	
	
	/**
	 * 找回密码
	 */
	public function  forgetAction()
	{
		
		$postData = $this->getRequestData();
		if(empty($postData->password) || !Utils::isMobile($postData->phone) || empty($postData->code))
		{
			$this->sendErrorResult('参数错误!');
		}
		$user = UserModel::factory()->loadUserByPhone($postData->phone);
		if(empty($user)){
			$this->sendErrorResult('手机号码不存在!',2001);
		}
		if(strlen($postData->password)!=32)
		{
			$postData->password = md5($postData->password);
		}
		$cacheKey = sprintf('sms_%s_%s','forget_password',$postData->phone);
		$vcode = $this->getHelper()->getCache()->get($cacheKey);
		if($vcode != $postData->code )
		{
			$this->sendErrorResult('验证码错误!');
		}
		//修改密码
		$result = $user->saveData(array(
			'password' => $postData->password
		));
		if($result)
		{
			$this->sendSuccessResult();
		}else{
			$this->sendErrorResult('找回密码失败!',2001);
		}
		
	}
	
	/**
	 * 修改密码
	 */
	public function changepwdAction()
	{
		$accessToken = $this->checkAccessToken();
		$postData = $this->getRequestData();
		if(empty($postData->old_password || empty($postData->new_password)))
		{
			$this->sendErrorResult('参数错误!');
		}
		if(strlen($postData->old_password)!=32)
		{
			$postData->old_password = md5($postData->old_password);
		}
		if(strlen($postData->new_password)!=32)
		{
			$postData->new_password = md5($postData->new_password);
		}
		$user = UserModel::factory()->loadUserByPhone($accessToken['phone']);
		if(strtolower($user->password) != strtolower($postData->old_password))
		{
			$this->sendErrorResult('密码不匹配!',2001);
		}
		$result = $user->saveData(array(
			'password' => $postData->new_password
		));
		if($result)
		{
			$this->sendSuccessResult();
		}else{
			$this->sendErrorResult('修改错误!',2001);
		}
	
	}
	
	/**
	 * 信息修改
	 */
	public function  updateAction()
	{
		$accessToken = $this->checkAccessToken();
		$postData = $this->getRequestData();
		if(empty($postData->old_password || empty($postData->new_password)))
		{
			$this->sendErrorResult('参数错误!');
		}
		$user = UserModel::factory()->loadUserByPhone($accessToken['phone']);
		if(empty($user)){
			$this->sendErrorResult('用户信息获取错误!');
		}
		$updateData = array();
		if(isset($_FILES['head_ico']['name']) && !empty($_FILES['head_ico']['name']))
		{
			$avatar = Utils::uploadFile('head_ico','user_avatar');
			if($avatar)
			{
				$updateData['avatar'] = $avatar;
			}
		}
		if(empty($updateData))
		{
			$this->sendErrorResult('用户信息修改失败!');
		}
		$user->saveData($updateData);
		$this->sendSuccessResult();
	}
	
	/**
	 * 系统消息
	 */
	public function  messageAction()
	{
		$accessToken = $this->checkAccessToken();
		$postData = $this->getRequestData();
		$items = AnnouncementLogModel::factory()->getUserMessage($postData->user_id);
		$this->sendSuccessResult($items);
	}
	
	/**
	 * 系统消息查看
	 */
	public function messageviewAction()
	{
		$accessToken = $this->checkAccessToken();
		$postData = $this->getRequestData();
		if(empty($postData->id)){
			$this->sendErrorResult('参数错误!');
		}
		
		//查询信息
		$announcement  = AnnouncementModel::factory()->findFirst('id='.$postData->id);
		if(empty($announcement))
		{
			$this->sendErrorResult('消息不存在!',2006);
		}
		
		//更改状态
		$announcementLog = AnnouncementLogModel::findFirst('user_id='.$postData->user_id.' and announcement_id='.$postData->id);
		if(empty($announcementLog))
		{
			$this->sendErrorResult('消息不存在!',2006);
		}
		
		if($announcementLog->status!=1)
		{
			$announcementLog->saveData(array(
					'status' => 1
			));
		}
		
		$result = array(
				'id' => $announcement->id,
				'title' => $announcement->title,
				'content' => $announcement->content,
				'pic' => $this->getHelper()->getFullPic($announcement->pic),
				'created' => $this->getHelper()->getTime()->localDate('Y-m-d H:i:s',$announcement->created)
		);
		$this->sendSuccessResult($result);
		
	}
	
}