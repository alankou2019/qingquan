<?php
/**
 * 授权
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\User\Helper;
use ScshuxCms\Core\Helper;
class AccessToken
{
	const  ACCESS_TOKEN_PREFIX = 'access_token_';
	
	/**
	 * 获取Token
	 * @return string
	 */
	protected static  function getToken()
	{
		return md5(microtime(true).rand(1000, 100000));
	}
	
	/**
	 *
	 * @param  $user_id
	 * @param  $phone
	 * @param  $head_ico
	 * @param  $expires_in
	 */
	public  static  function  add($user_id,$phone,$head_ico='',$expires_in=2592000)
	{
		$access_token = AccessToken::getToken();
		$keyname = AccessToken::ACCESS_TOKEN_PREFIX . $access_token;
		$data = array(
				'access_token' => $access_token,
				'expires_in' => $expires_in,
				'user_id'  => intval($user_id),
				'phone'   => $phone,
				'head_ico' => Helper::factory()->getFullPic($head_ico),
				'created'  => Helper::factory()->getTime()->gmtime()
		);
		Helper::factory()->getCache()->save($keyname,$data,$expires_in);
		return $data;
	}
	
	/**
	 * 获取access_token
	 * @param string  $access_token
	 * @param integer $user_id
	 */
	public  static  function get($access_token = '',$user_id ='')
	{
		
		if(empty($access_token) || empty($user_id)) return  array();
		$keyname = AccessToken::ACCESS_TOKEN_PREFIX . $access_token;
		$info = Helper::factory()->getCache()->get($keyname);
		
		if(empty($info) || $info['user_id']!=$user_id){
			return array();
		}
		
		//过期时间
		$expires_in = Helper::factory()->getTime()->gmtime() - $info['created'];
		if($expires_in > $info['expires_in']){
			return array();
		}
		
		return $info;
	}
	
	/**
	 * @param string $access_token
	 * @param integer $user_id
	 * 清除access_token，用户退出
	 */
	public static function del($access_token,$user_id)
	{
		if(empty($access_token) || empty($user_id)) return  array();
		$keyname = AccessToken::ACCESS_TOKEN_PREFIX . $access_token;
		Helper::factory()->getCache()->delete($keyName);
		return true;
	}
}