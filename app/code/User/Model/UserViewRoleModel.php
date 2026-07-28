<?php
/**
 * 用户查看权限表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\User\Model;

use ScshuxCms\Core\Model\BaseModel;
use Phalcon\Forms\Element;
use ScshuxCms\Dacang\Model\CompanyDepartModel;
class  UserViewRoleModel extends BaseModel
{

	protected static  $_instance=null;

	public function initialize()
	{
		$this->setSource($this->getTableName("user_view_role"));
	}

	/**
	 * 返回实例
	 * @return \ScshuxCms\User\Model\UserRoleModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new UserViewRoleModel();
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
	 * @desc	获取管理员设置的   用户拥有的查看权限
	 * @param	$userId		
	 * @return	array		
	 */
	public function getUserViewRole($userId)
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
	public function getWhereByUserViewRole($field='')
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
			$userRole=self::getUserViewRole($userId);
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
	
	
	
	/**
	 * @desc	获取管理员设置的    用户能额外查看的权限列表
	 * @param	$userId		
	 * @return			
	 */
	public function getExtRole($userId)
	{
		$return = array();
		if (!$userId)
		{
			return $return;
		}
		$userId = intval($userId);
		$where  = 'vr.user_id='.$userId;
		$items = self::factory()->getModelsManager()->createBuilder()
					->columns('d.id,d.name')
					->addFrom('ScshuxCms\User\Model\UserViewRoleModel','vr')
					->leftJoin('ScshuxCms\Dacang\Model\CompanyDepartModel','vr.depart_id=d.id','d')
					->where($where)
					->getQuery()
					->execute();
		
		if ($items)
		{
			foreach ($items as $item)
			{
				$return[] = array(
						'value'=>$item->id,
						'text' =>$item->name
				);
			}
		}
		return $return;
	}

}