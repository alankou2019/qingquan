<?php
/**
 * 短信插件
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Core;
class Sms
{
	
	/**
	 * 发送公共验证码
	 * @param  $mobile
	 * @param array $data
	 * @return boolean
	 */
	public  static  function  sendCommon($mobile,$data=null)
	{
		$templateString ="您的验证码为：mobile_code，请注意保管!";
		$content = strtr($templateString,$data);
		return  self::send($mobile, $content);
	}
	
	
	/**
	 * @brief 发送短信
	 * @param string $mobile
	 * @param string $content
	 * @return
	 */
	public static function send($mobile,$content)
	{
		
		$config = Helper::factory()->getConfig();
		
		/**
		 * 校验短信
		*/
		if(empty($config->sms_account) || empty($config->sms_password) || empty($content) || empty($mobile)){
			return  false;
		}
		/*
		 输入参数：CorpID-帐号，Pwd-密码，Mobile-发送手机号(多个号码以逗号分隔)，Content-发送内容，Cell-扩展号(可为空或必须是4位以下的数字），SendTime-定时发送时间(固定14位长度字符串，比如：20060912152435代表2006年9月12日15时24)
		 输出参数：大于等于0的数字，发送成功（得到大于等于0的数字、作为取报告的id）；-1、帐号未注册；-2、网络访问超时，请重试；-3、密码错误；-5、余额不足；-6、定时发送时间不是有效的时间格式；-7、提交信息末尾未加签名，请添加中文企业签名【 】； -8、发送内容需在1到300个字之间；-9、 发送号码为空
		 */
		//$content .=$config->sms_sign;
		$encode = mb_detect_encoding($content, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
		//echo $content ."|".$mobile .'<br>';
		if($encode != 'GB2312'){
			$content = iconv($encode,"GB2312",$content);
		}
		$post_data = array(
				'CorpID'  => 'CDJS001105',//帐号
				'Pwd' => 'scsx*2017',//密码
				'Content'  => $content,//发送内容
				'Mobile'   => $mobile,//发送手机号(多个号码以逗号分隔)
				'SendTime' => '',
				'Cell'     => ''
		);	
		$string = '';
		foreach ($post_data as $k => $v)
		{
			$string .="$k=".urlencode($v).'&';
		}
		$post_string = substr($string,0,-1);
	
		$gateway = "https://sdk2.028lk.com/sdk2/LinkWS.asmx/Send?" . $string;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $gateway);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 40);
		$result = curl_exec($ch);
		if($result == 0 || $result == 1){
			return true;
		}else{
			return false;
		}
	
	}	
}