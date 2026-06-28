<?php
/**
 * 系统用户表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Common\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
class AdminUserModel extends BaseModel
{

	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("admin_user");
	}
	
	/**
	 * 登录后执行的动作
	 * @param \ScshuxCms\Common\Model\AdminUserModel $adminUser
	 */
	public  function loginAfter($adminUser)
	{
		if(empty($adminUser)) return false;
			
		
		//保存登录信息
		$data = array(
				'user_id' => $adminUser->user_id,
				'role_id' => $adminUser->role_id,
				'realname' => $adminUser->realname,
				'email' => $adminUser->email,
				'last_ip' => $adminUser->last_ip,
				'logdate'=> $adminUser->logdate,
				'lognum' => $adminUser->lognum
		);
		$data =  arrayToObject($data);
		Helper::factory()->getSession()->set('_admin_user', $data);
		
		//保存日志
		AdminLogModel::factory()->addLog('登录系统!',$adminUser->user_id);//登录日志
		$adminUser->save(array(
				'last_ip' => Utils::getIP(),
				'logdate' => Helper::factory()->getTime()->gmtime(),
				'lognum'  => $adminUser->lognum+1
		));
		
	}
	
	/**
	 * 获取当前用户
	 * @return array
	 */
	public static   function getLoginUser()
	{
		return Helper::factory()->getSession()->get('_admin_user');
	}
	
	/**
	 * 获取当前用户是否登录
	 */
	public  static  function  isLogin()
	{
		$adminUser = self::getLoginUser();
		if($adminUser)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * 获取当前用户编号
	 * @return string|Ambigous <>
	 */
	public  static  function getUserId()
	{
		$adminUser = self::getLoginUser();
		if($adminUser)
		{
			return '';
		}
		return $adminUser->user_id;
	}

	/**
	 * 返回实例
	 * @return \ScshuxCms\Common\Model\AdminUserModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new AdminUserModel();
		}
		return self::$_instance;
	}



}