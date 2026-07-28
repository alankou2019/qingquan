<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Helper;
class Prpcrypt
{
	public $key;
	
	public function __construct($k)
	{
		$this->key = base64_decode($k . "=");
	}
	
	public function Prpcrypt($k)
	{
		$this->key = base64_decode($k . "=");
	}
	public function encrypt($text, $corpid)
	{
		try {
			//获得16位随机字符串，填充到明文之前
			$random = $this->getRandomStr();
			$text = $random . pack("N", strlen($text)) . $text . $corpid;
			// 网络字节序
			$iv = substr($this->key, 0, 16);
			//使用自定义的填充方式对明文进行补位填充
			$pkc_encoder = new PKCS7Encoder;
			$text = $pkc_encoder->encode($text);
			//加密
			$encrypted = openssl_encrypt($text, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
			if ($encrypted === false) {
				return array(ErrorCode::$EncryptAESError, null);
			}
			//print(base64_encode($encrypted));
			//使用BASE64对加密后的字符串进行编码
			return array(ErrorCode::$OK, base64_encode($encrypted));
		} catch (\Exception $e) {
			print $e;
			return array(ErrorCode::$EncryptAESError, null);
		}
	}
	public function decrypt($encrypted, $corpid)
	{
		try {
			$ciphertext_dec = base64_decode($encrypted, true);
			if ($ciphertext_dec === false) {
				return array(ErrorCode::$DecryptAESError, null);
			}
			$iv = substr($this->key, 0, 16);
			$decrypted = openssl_decrypt($ciphertext_dec, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
			if ($decrypted === false) {
				return array(ErrorCode::$DecryptAESError, null);
			}
		} catch (\Exception $e) {
			return array(ErrorCode::$DecryptAESError, null);
		}
		try {
			//去除补位字符
			$pkc_encoder = new PKCS7Encoder;
			$result = $pkc_encoder->decode($decrypted);
			//去除16位随机字符串,网络字节序和AppId
			if (strlen($result) < 16)
				return "";
			$content = substr($result, 16, strlen($result));
			$len_list = unpack("N", substr($content, 0, 4));
			$xml_len = $len_list[1];
			$xml_content = substr($content, 4, $xml_len);
			$from_corpid = substr($content, $xml_len + 4);
		} catch (\Exception $e) {
			print $e;
			return array(ErrorCode::$DecryptAESError, null);
		}
		if ($from_corpid != $corpid)
			return array(ErrorCode::$ValidateSuiteKeyError, null);
		return array(0, $xml_content);
	}
	function getRandomStr()
	{
		$str = "";
		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($str_pol) - 1;
		for ($i = 0; $i < 16; $i++) {
			$str .= $str_pol[mt_rand(0, $max)];
		}
		return $str;
	}
}