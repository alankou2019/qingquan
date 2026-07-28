<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Tree;
use ScshuxCms\User\Model\UserManageRoleModel;
use ScshuxCms\User\Model\UserModel;
class  DepartmentModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("company_department"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new DepartmentModel();
		}
		return self::$_instance;
	}
	
	
	
	/**
	 * 
	 * @desc 返回部门列表  二维数组
	 * @param  $companyId   公司id
	 * @date 2017年4月21日
	 */
	public static function departList($companyId)
	{	
		$return = array(); 
		if(!$companyId)
		{
			return $return ;
		}
		
		$companyId = intval($companyId) ;
		$items = self::find('company_id = '.$companyId) ;
		
		if($items)
		{
			//转化为数组
			foreach ($items as $item)
			{
				$return[$item->id]['id'] = $item->id ;
				$return[$item->id]['name'] = $item->name ;
				$return[$item->id]['dingding_id'] = $item->dingding_id ;
				$return[$item->id]['dingding_parent_id'] = $item->dingding_parent_id ;
			}
		}
		
		return $return ;
		
	}
	
	
	/**
	 *
	 * @desc 返回部门列表  一维数组
	 * @param  $companyId   公司id
	 * @date 2017年4月21日
	 */
	public static function departListOne($companyId)
	{
		$return = array();
		if(!$companyId)
		{
			return $return ;
		}
	
		$companyId = intval($companyId) ;
		$items = self::find('company_id = '.$companyId) ;
	
		if($items)
		{
			//转化为数组
			foreach ($items as $item)
			{
				$return[$item->dingding_id] = $item->name ;
			}
		}
		$arr = self::roleSx($return);
		return $return ;
	
	}
	
	
	/**
	 *
	 * @desc 返回部门列表  二维数组
	 * @param  $companyId   公司id
	 * @return array()
	 * @date 2017年4月21日
	 */
	public static function departListArray($companyId)
	{
		$return = array();
		if(!$companyId)
		{
			return $return ;
		}
	
		$companyId = intval($companyId) ;
		$items = self::find('company_id = '.$companyId) ;
	
		if($items)
		{
			//转化为数组
			foreach ($items as $item)
			{
				$return[$item->id]['id'] = $item->dingding_id ;
				$return[$item->id]['name'] = $item->name ;
			}
		}
	
		return $return ;
	
	}
	
	
	
	/**
	 * @desc	树形部门列表菜单
	 * @param			
	 * @return			
	 */
	public static function TreeDepartList($companyId)
	{
		$departlist = self::departList($companyId);
		if ($departlist)
		{
			$arr = self::roleSx($departlist);
			$treeobj = new Tree($arr['departList'],'dingding_id','name','dingding_parent_id');
			$departlist = $treeobj->unlimitedForLevel('&nbsp;&nbsp;&nbsp;&nbsp;',$arr['start']);
		}
		
		return $departlist ;
	}
	
	
	
	/**
	 * @desc	权限筛选
	 * @param			
	 * @return			
	 */
	public static function roleSx($departlist)
	{
		if (!$departlist)
		{
			return array();
		}
		//当前登录用户如果不是主管理员 则需要判断用户的管理权限
		$userInfo=UserModel::factory()->getUser();
		$isAdmin =$userInfo->is_admin;
		$start='';
		if (!$isAdmin)
		{
			//当前用户拥有的权限
			$userRole=UserManageRoleModel::factory()->getUserManageRole(UserModel::factory()->getUserId());
			if (!$userRole)
			{
				return array();
			}
			
			//判断是一维数组还是二维数组
			$currentValue=current($departlist);
			if (is_array($currentValue))
			{
				foreach ($departlist as $depart)
				{
					if (!in_array($depart['id'], $userRole))
					{
						unset($departlist[$depart['id']]);
					}
				}
				
				//找出最小的dingding_parent_id
				foreach ($departlist as $depart)
				{
					$start = empty($start) ? $depart['dingding_parent_id'] : ($depart['dingding_parent_id']<$start?$depart['dingding_parent_id']:$start);
				}
			}
			else 
			{
				foreach ($departlist as $key=>$depart)
				{
					if (!in_array($key, $userRole))
					{
						unset($departlist[$key]);
					}
				}
			}
		}
		return array(
				'departList'=>$departlist,
				'start'=>$start
		);
	}
	
	
}