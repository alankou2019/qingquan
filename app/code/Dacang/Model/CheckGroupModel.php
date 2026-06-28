<?php
/**
 * 审核组
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\User\Model\UserModel;
use Phalcon\Validation;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Helper;
use ScshuxCms\Core\Helper\Utils;
class CheckGroupModel extends BaseModel
{

	protected static  $_instance=null;
	protected static $error='';

	public function getSource()
	{
		return $this->getTableName("check_group");
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\CompanyModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new CheckGroupModel();
		}
		return self::$_instance;
	}
	
	
	/**
	 * @desc	保存数据
	 * @param	$requestData		
	 * @return			
	 */
	public static function saveData($requestData)
	{
		if (!$requestData)
		{
			self::$error='error'; return false;
		}
		$id=intval($requestData->get('id'));
		if ($id)
		{
			$groupInfo=self::findFirst($id);
			if (!$groupInfo)
			{
				self::$error='考核组不存在'; return false;
			}
			
			$groupInfo->name=$requestData->get('name');
			$groupInfo->desc=$requestData->get('desc');
			$groupInfo->depart_ids=implode(',', $requestData->get('role'));
		}
		else 
		{
			$groupInfo=self::factory();
			$groupInfo->name=$requestData->get('name');
			$groupInfo->desc=$requestData->get('desc');
			$groupInfo->depart_ids=implode(',', $requestData->get('role'));
			$groupInfo->company_id=UserModel::getCompanyId();
			$groupInfo->created_at=Helper::factory()->getTime()->gmtime();
		}
		
		return  $groupInfo->save();
	}

	
	/**
	 * @desc	获取审核组
	 * @param			
	 * @return			
	 */
	public static function getGroup()
	{
		$return=array();
		$companyId=UserModel::getCompanyId();
		$where='company_id='.$companyId;
		$items=self::find($where)->toArray();
		if ($items)
		{
			foreach ($items as $val)
			{
				$return[$val['id']]=$val;
			}
			
		}
		return $return;
	}
	
	
	
	/**
	 * @desc	通过部门id  或许需要审核该部门的 审核人列表
	 * @param	$departId
	 * @return
	 */
	public static function getGroupUserByDepartId($departId)
	{
		if (!$departId)
		{
			return false;
		}
	
		$departId=intval($departId);
		$where='find_in_set("'.$departId.'",g.depart_ids)';
		$columns='gu.user_id';
		$item=self::factory()->getModelsManager()->createBuilder()->columns($columns)
		->addFrom('ScshuxCms\Dacang\Model\CheckGroupModel','g')
		->leftJoin('ScshuxCms\Dacang\Model\CheckGroupUserModel','g.id=gu.group_id','gu')
		->where($where)->getQuery()->execute()->toArray();

		$return=array();
		if ($item){
			foreach ($item as $v)
			{
				$v['user_id']?$return[]=$v['user_id']:'';
			}
		}
		return $return;
	}
	
	
	
	/**
	 * @desc	通过部门id  或许需要审核该部门的 审核人列表
	 * @param	$departId
	 * @return
	 */
	public static function getGroupUserInfoByDepartId($departId)
	{
		if (!$departId)
		{
			return false;
		}
	
		$departId=intval($departId);
		$where='find_in_set("'.$departId.'",g.depart_ids)';
		$columns='gu.user_id,u.name as username';
		$item=self::factory()->getModelsManager()->createBuilder()->columns($columns)
		->addFrom('ScshuxCms\Dacang\Model\CheckGroupModel','g')
		->leftJoin('ScshuxCms\Dacang\Model\CheckGroupUserModel','g.id=gu.group_id','gu')
		->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','gu.user_id=u.id','u')
		->where($where)->getQuery()->execute()->toArray();
		
		if ($item){
			foreach ($item as $key=>$v)
			{
				if (empty($v['user_id']))
				{
					unset($item[$key]);
				}
			}
		}
		return $item;
	}
	
	
	
	/**
	 * @desc	通过用户id  获取该用户能审核的部门id列表
	 * @param	$userId
	 * @return	array
	 */
	public static function getDepartIdsByUserId($userId)
	{
		if (!$userId)
		{
			return false;
		}
		$userId=intval($userId);
		$where='u.user_id='.$userId;
		$columns='g.depart_ids';
		$items=self::factory()->getModelsManager()->createBuilder()->columns($columns)
				->addFrom('ScshuxCms\Dacang\Model\CheckGroupModel','g')
				->leftJoin('ScshuxCms\Dacang\Model\CheckGroupUserModel','g.id=u.group_id','u')
				->where($where)->getQuery()->execute()->toArray();
		
		if ($items)
		{
			$return=array();
			foreach ($items as $val)
			{
				$temp=explode(',', $val['depart_ids']);
				if ($temp)
				{
					foreach ($temp as $v)
					{
						$return[]=$v;
					}
				}
			}
			return array_unique($return);
		}
		return false;
				
	}
	
	
	/**
	 * @desc	获取错误
	 * @param			
	 * @return			
	 */
	public static function getError()
	{
		return self::$error;
	}
}