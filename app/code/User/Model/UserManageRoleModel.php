<?php
/**
 * 用户管理权限表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\User\Model;

use ScshuxCms\Core\Model\BaseModel;
use Phalcon\Forms\Element;
use ScshuxCms\Dacang\Model\CompanyDepartModel;
class  UserManageRoleModel extends BaseModel
{

	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("user_manage_role");
	}

	/**
	 * 返回实例
	 * @return \ScshuxCms\User\Model\UserRoleModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new UserManageRoleModel();
		}
		return self::$_instance;
	}
	
	
	/**
	 * @desc	删除单例
	 * @param			
	 * @return			
	 */
	public static function delFactory()
	{
		self::$_instance=null;
	}
	
	/**
	 * @desc	获取用户拥有的管理权限
	 * @param	$userId		
	 * @return	array		
	 */
	public function getUserManageRole($userId)
	{
		$return=array();
		if (!$userId)
		{
			return $return;
		}
		$userId=intval($userId);
		$userRole=self::find('user_id='.$userId);
		if ($userRole)
		{
			foreach ($userRole as $role)
			{
				$return[] = $role->depart_id;
			}
		}
		return $return;
	}
	
	
	/**
	 * @desc	根据用户拥有的权限获取查询指标库的where条件
	 * @param	$field		
	 * @return			
	 */
	public function getWhereByUserManageRole($field='')
	{
		$ddingDepart=array(
				'u.department_id'
		);
		$field=$field?$field:'depart_id';
		$where='';
		$userInfo=UserModel::factory()->getUser();
		$isAdmin=$userInfo->is_admin;
		if (!$isAdmin)
		{
			$userId=$userInfo->user_id;
			$userRole=self::getUserManageRole($userId);
			if (!$userRole)
			{
				$where=$field.'=0';
				return $where;
			}
			//有的是存的部门表自增id  有的是存的从钉钉获取的部门id  需要分别做处理
			if (in_array($field, $ddingDepart))
			{
				$items=CompanyDepartModel::factory()->find('id in('.implode(',', array_values($userRole)).')');
				$userRole=array();
				foreach ($items as $item)
				{
					$userRole[]=$item->dingding_id;
				}
			}
			
			if (!$userRole)
			{
				$where=$field.'=0';  
				return $where;
			}
			$where=$field.' in('.implode(',', array_values($userRole)).')';
		}
		return $where; 
	}

}