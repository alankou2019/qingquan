<?php
/**
 * 公司人员管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\User\Model\UserModel;
class CompanyUserModel extends BaseModel
{

	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("company_user");
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\CompanyModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new CompanyUserModel();
		}
		return self::$_instance;
	}
	
	
	/**
	 *
	 * @desc 通过id  获取用户名的字符串
	 * @param $ids
	 * @param $companyId
	 * @date 2017年4月21日
	 */
	public static function getUserByids($ids,$companyId)
	{
		$return  = '';
		if(!$ids || !$companyId)
		{
			return $return ;
		}
	
		$ids = trim($ids) ;
		$companyId = intval($companyId) ;
	
		$items = self::find("company_id = {$companyId} and id in ({$ids})") ;
		if($items)
		{
			foreach ($items as $item)
			{
				$return .= $item->name.',';
			}
			$return = trim($return,',') ;
		}
	
		return $return ;
	}
	
	
	/**
	 * 
	 * @desc	通过用户id  获取用户信息 包括用户所属部门
	 * @param	int  $id  用户id
	 * @return	obj
	 * @date	2017年5月4日
	 */
	public static function getUserAddDepartByids($id)
	{
		$return = new \stdClass() ;
		if(!$id)
		{
			return $return ;
		}
		$id = intval($id) ;
		
		$where = 'c.id = '.$id ;
		$items = UserModel::factory()->getModelsManager()->createBuilder()
												->columns('c.id,c.name,c.avatar,d.name as dname')
												->addFrom('ScshuxCms\Dacang\Model\CompanyUserModel','c')
												->leftJoin('ScshuxCms\Dacang\Model\DepartmentModel','d.dingding_id = c.department_id and c.company_id=d.company_id','d')
												->where($where)
												->getQuery()
												->execute() ;
		if($items)
		{
			$return = $items[0] ;
		}
		
		return $return ;
	}
	
	
	/**
	 *
	 * @desc	获取部门下  用户id
	 * @param	$departId
	 * @return	str
	 * @date	2017年5月6日
	 */
	public function getOneDepartUsers($departId)
	{
		$return = '';
		if(!$departId)
		{
			return $return;
		}
		
		//将部门自增id 转换成为钉钉部门id
		$departId = CompanyDepartModel::idAdpter($departId);
		if (is_numeric($departId))
		{
			$where='department_id='.intval($departId);
		}
		else 
		{
			$where='department_id in ('.trim($departId.')');
		}
		
		$items = CompanyUserModel::find($where) ;
		if($items)
		{
			foreach ($items as $item)
			{
				$return .= $item->id.',';
			}
			$return = rtrim($return,',') ;
		}
		return $return ;
	}
	
	
	
	/**
	 * 
	 * @desc	获取已经添加近考核组的人员数目
	 * @param	$companyId		
	 * @return	int
	 * @date	2017年5月16日
	 */
	public function getHasNum($companyId)
	{
		$return  = 999999 ;
		if(!$companyId)
		{
			return $return ;
		}
		
		$companyId = intval($companyId) ;
		$where = 'company_id = '.$companyId.' and addreport = 1';
		
		$items = CompanyUserModel::getModelsManager()->createBuilder()
											->columns('count(*) as num')
											->addFrom('ScshuxCms\Dacang\Model\CompanyUserModel','c')
											->where($where)
											->getQuery()
											->execute() ;
		if($items)
		{
			$return = $items[0]->num;
		}
		
		return $return ;
	}
	
	

	/**
	 *
	 * @desc	根据id获取用户详细信息 （名称 头像  部门 公司等等）
	 * @param	$uid  int
	 * @return	object
	 * @date	2017年6月2日
	 */
	public static function getDetailUser($uid)
	{
		
		if(!$uid || !is_numeric($uid))
		{
			return null ;
		}
		$uid = intval($uid) ;
		$where = 'u.id = '.$uid .' and d.company_id=u.company_id' ;
		$items = CompanyUserModel::factory()->getModelsManager()->createBuilder()
				->columns('u.id,u.name,u.avatar,d.name as dname,c.name as cname')
				->addFrom('ScshuxCms\Dacang\Model\CompanyUserModel','u')
				->leftJoin('ScshuxCms\Dacang\Model\DepartmentModel','u.department_id = d.dingding_id ','d')
				->leftJoin('ScshuxCms\Dacang\Model\CompanyModel','u.company_id = c.id','c')
				->where($where)
				->getQuery()
				->execute();
		if(count($items))
		{
			return $items[0] ;
		}
		return null ;
	}
	
	
}