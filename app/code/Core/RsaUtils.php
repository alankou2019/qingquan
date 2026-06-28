<?php
/**
 *   rsa加密解密 使用公钥加密 则使用私钥解密
 *
 * @version 1.0
 * @author  kaiping.jiang@scshux.com
 * @copyright 四川蜀讯科技有限公司 (http://www.scshuxun.com)
 */
namespace  ScshuxCms\Core;
class  RsaUtils
{

	protected  static   $private_key = '-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAL9F0TrXmbA7/lPI
S2jgx451YehrcIfp4lggSDtXPi7hckBWjOZpW3OBo/h52ClRfp4C2zhJPZHrs+Ch
CN2+EysxBL86RGeziapa23BoiHZZKQIuKS7BI+CAkE+UEWsAda1pKGmtf8XllHKk
jg4eIRbanTvBgl3k0rSW0iIhi12RAgMBAAECgYADrWf0ZajCxqSBH9yzS38Ksh8L
xOUI9+ouH5DD801ywUrdCczzsRrdAaURZeZOBQ8WIp/sWRwh7hIrQ21UNg9QQMKV
r58USmD+F9ibPBk1rRebyZ0DzYnuSctNMkVK/EAhyHtQVL/uRHuCnofigwTjv+VK
cpAWHWAkQDSvSqtN8QJBAOAigQFz8KYSr5HSXfhmuLuwPSWSuhawFNdlEcPwy6aV
pEKrWk0SY8qGRHT4rg7FyzccfnIeONn7Fi8zSIGOjnsCQQDad0hWuopC15hHRJXz
stpnkpjmSpZHw3miWCUc0/k/PEUqVEKQUsOLqMaO+SAUWruhqoYTjKRWxwjMNH0b
zYxjAkBv8GuTqS7oEm8DGhh6hfr/Kf7v8/7ic+CEzuJ3hJyBPz1BWskHswaJ0FMC
RGzRzfE3PhDct8FXBLLIsgklQ2hfAkArziE2Kr9QedRx7eG3dNRwUifQfYI2r45z
LAN9DU/8CwS/YRfbwoytM1FlF7UD/9GBsCSkRN4q/EvaTmgLrCKLAkEAmhsmuJqs
VeRvXFze6owDchlXRoIC5I8UCaVm1LESJmeg9itQlNx46SjS9s6QG16+y7ixFxqE
1uwYDWpV2ET/rQ==
-----END PRIVATE KEY-----
';

	protected  static   $public_key  = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC/RdE615mwO/5TyEto4MeOdWHo
a3CH6eJYIEg7Vz4u4XJAVozmaVtzgaP4edgpUX6eAts4ST2R67PgoQjdvhMrMQS/
OkRns4mqWttwaIh2WSkCLikuwSPggJBPlBFrAHWtaShprX/F5ZRypI4OHiEW2p07
wYJd5NK0ltIiIYtdkQIDAQAB
-----END PUBLIC KEY-----';


	/**
	 * 私钥加密
	 * @param  $data
	 */
	public static  function  privateEncrypt($data)
	{
		$encrypted = "";
		$pi_key =  openssl_pkey_get_private(self::$private_key);
		foreach (str_split($data, 117) as $chunk) {
			$encryptData = '';
			openssl_private_encrypt($chunk, $encryptData, $pi_key);
			$encrypted .= $encryptData;
		}
		return base64_encode($encrypted);
	}


	/**
	 * 私钥解密
	 * @param  $data
	 */
	public static  function  privateDecrypt($data)
	{
		$data = base64_decode($data);
		$decrypted = '';
		$pi_key =  openssl_pkey_get_private(self::$private_key);
		foreach (str_split($data, 128) as $chunk) {
			$tempdata = '';
			openssl_private_decrypt($chunk,$tempdata,$pi_key);//私钥解密
			$decrypted .=$tempdata;
		}
		return $decrypted;
	}



	/**
	 * 公钥加密
	 * @param  $data
	 */
	public static  function  publicEncrypt($data)
	{

		$encrypted = "";
		$pu_key = openssl_pkey_get_public(self::$public_key);
		foreach (str_split($data, 117) as $chunk) {
			$encryptData = '';
			openssl_public_encrypt($chunk, $encryptData, $pu_key);
			$encrypted .= $encryptData;
		}
		return base64_encode($encrypted);


	}



	/**
	 * 公钥解密
	 * @param  $data
	 */
	public static  function  publicDecrypt($data)
	{
		$data = base64_decode($data);
		$decrypted = '';
		$pu_key = openssl_pkey_get_public(self::$public_key);
		foreach (str_split($data, 128) as $chunk) {
			$tempdata = '';
			openssl_public_decrypt($chunk,$tempdata,$pu_key);//私钥解密
			$decrypted .=$tempdata;
		}
		return $decrypted;

	}

}