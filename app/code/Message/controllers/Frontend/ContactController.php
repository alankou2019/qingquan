<?php
/**
 * 联系我们
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Captcha;
use ScshuxCms\Message\Model\MessageModel;
class  ContactController  extends FrontendBaseController
{
	
	public  function  indexAction()
	{
		$this->setSeo("在线预约");
	}
	
	/**
	 * 验证码
	 */
	public function  captchaAction()
	{
		header("Content-type: image/GIF");
		$captchaObj =new  Captcha(140,42,25);
		$captchaObj->doImg();
		$this->session->set('captcha', $captchaObj->getCode());
		exit;
		
	}
	
	/**
	 * 提交预约信息
	 */
	public function doAppointMentAction()
	{
		$postData = $this->request->get('data');
		$ip = $_SERVER['REMOTE_ADDR'];
		$data = array(
			'covered_area' 	=> $postData['area'],
			'cooper_service' => $postData['service'],
			'company'		=> $postData['company'],
			'nickname' 		=> $postData['userName'],
			'phone'			=> $postData['userTel'],
			'company_addr'	=> $postData['companyAddr'],
			'ip'			=> $ip,
			'inputtime'		=> time()
		);
		$vcode = $this->session->get('captcha');
		if($vcode != $postData['captcha'])
		{
			$this->sendErrorResult("验证码错误，请重新输入！");
		}
		$result = MessageModel::factory()->saveData($data);
		if($result)
		{
			$this->sendSuccessResult("预约提交成功！");
		}
		else
		{
			$this->sendErrorResult("预约提交失败，请重试！");
		}
		exit;
	}
	
}