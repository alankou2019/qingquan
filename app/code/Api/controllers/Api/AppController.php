<?php
/**
 *
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Api\Controller;
use ScshuxCms\Core\Controller\ApiBaseController;
use ScshuxCms\Core\Sms;
use ScshuxCms\Core\Helper;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Advert\Model\AdvertModel;
use ScshuxCms\Gas\Model\GasStationModel;
use ScshuxCms\Payment\Model\PaymentModel;
use ScshuxCms\User\Model\UserModel;
class  AppController extends ApiBaseController
{

	/**
	 * 系统接口
	 */
	public function indexAction()
	{
		//格式化加油站类型
		$formatGasTypes = array();
	    foreach (GasStationModel::factory()->getTypes() as $gasTypeId=>$gasTypeName)
	    {
	    	$formatGasTypes[] = array(
	    			'type_id'=> $gasTypeId,
	    			'type_name'=>$gasTypeName
	    	);
	    }
		$postData = $this->getRequestData();
		$config = Helper::factory()->getConfig();
		$result = array(
			'isSupport' =>'y',
			'iosVersion'=>$config->ios_version,
			'androidVersion'=>$config->android_version,
			'androidApkUrl'=>$config->android_apkurl,
			'servicePhone'=>Helper::factory()->getConfig('tel'),
			'canUse' => 'y',
			'aboutus' => Helper::factory()->getConfig('aboutus'),
			'gasTypes' => $formatGasTypes,
			'invoiceAgreement' => Helper::factory()->getConfig('invoice_agreement'),
			'wechatAppkey' =>Helper::factory()->getConfig('wechat_appkey'),
			'wechatAppsecret' =>Helper::factory()->getConfig('wechat_appsecret'),
			'paymentList' => Helper::factory()->getPayments(),
			'express_fee'=>Helper::factory()->getConfig('express_fee')
		);
		$this->sendSuccessResult($result);
	}

	/**
	 * 广告接口
	 */
	public  function  adAction()
	{
		$postData = $this->getRequestData();
		if(empty($postData->ad_key) || empty($postData->limit))
		{
			$this->sendErrorResult('参数错误!');
		}
		$items = AdvertModel::factory()->loadAdsByPositionCode($postData->ad_key,$postData->limit);
		$adItems = array();
		foreach ($items as $item)
		{
			$adItems[] = array(
				'name' => $item->name,
				'pic'  => Helper::factory()->getFullPic($item->content)
			);
		}
		$this->sendSuccessResult($adItems);
	}


	/**
	 * 短信接口
	 */
	public  function smsAction()
	{

		$postData = $this->getRequestData();
		if(empty($postData->sms_key) || empty($postData->phone) || !Utils::isMobile($postData->phone))
		{
			$this->sendErrorResult('参数错误!');
		}

		$smsKeys = array(
			'register','forget_password'
		);

		if(!in_array($postData->sms_key, $smsKeys)){
			$this->sendErrorResult('不支持的验证码类型!');
		}

		$user = UserModel::factory()->loadUserByPhone($postData->phone);
		if($user && $postData->sms_key == 'register')//注册 验证手机号码
		{
			$this->sendErrorResult('手机号码已被注册!');
		}
		if(!$user && $postData->sms_key == 'forget_password')//忘记密码 验证手机号码
		{
			$this->sendErrorResult('无效的手机号码!');
		}

		$data = array(
			'mobile_code' => rand(100001, 999999)
		);
		$result = false;
		switch ($postData->sms_key)
		{
			 default:
			 $result =Sms::sendCommon($postData->phone,$data);
			 break;
		}

		if($result){

			$cacheKey = sprintf('sms_%s_%s',$postData->sms_key,$postData->phone);
			$this->getHelper()->getCache()->save($cacheKey,$data['mobile_code']);
			$this->sendSuccessResult(array(
				 'phone'=>$postData->phone,
				 'created'=>$this->getHelper()->getTime()->gmtime()
			));

		}else{
			$this->sendErrorResult('发送错误!');
		}
	}


	public  function  payAction()
	{

	}


}