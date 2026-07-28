<?php
/**
 * 审核组成员
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\User\Model\UserModel;
use Phalcon\Validation;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Helper;
class CheckGroupUserModel extends BaseModel
{

	protected static  $_instance=null;
	protected static $_error='';

	public function initialize()
	{
		$this->setSource($this->getTableName("check_group_user"));
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\CompanyModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new CheckGroupUserModel();
		}
		return self::$_instance;
	}
	
	
	/**
	 * @desc	添加到审核组
	 * @param	$userId		
	 * @param	$groupId		
	 * @return			
	 */
	public static function addToGroup($userId,$groupId)	
	{
		if (!$userId || !$groupId)
		{
			return false;
		}
		$groupId=intval($groupId);
		$ids=explode(',', trim($userId));
		$nowtime=Helper::factory()->getTime()->gmtime();
		$sql='insert into '.self::factory()->getSource().' (group_id,user_id,created_at) values';
		foreach ($ids as $id)
		{
			$sql.='('.$groupId.','.$id.',"'.$nowtime.'"),';
		}
		$sql=rtrim($sql,',');
		$sql.=' on duplicate key update created_at="'.$nowtime.'"';
		$db=FactoryDefault::getDefault()->get('db');
		$res=$db->query($sql);
		return $res;
		
	}
	
	
	
	/**
	 * @desc	判断当前用户是否是审核组成员
	 * @param	$userId		
	 * @return	bool		
	 */
	public static function isCheck($userId)
	{
		$return=false;
		if (!$userId)
		{
			return  $return;
		}
		$item=self::findFirst('user_id='.$userId);
		$return=empty($item)?false:true;
		return $return;
	}
	
	
	/**
	 * @desc	判断当前用户是否可以审核积分记录
	 * @param			
	 * @return			
	 */
	public static function isCheckUser($userId,$detailId)
	{
		$return=false;
		if (!$userId || !$detailId)
		{
			self::$_error='param error';return $return;
		}
		$userId=intval($userId);
		$detailId=intval($detailId);
		
		$where='gu.user_id='.$userId;
		$columns='g.depart_ids';
		$checkUser=self::factory()->getModelsManager()->createBuilder()->columns($columns)
					->addFrom('ScshuxCms\Dacang\Model\CheckGroupUserModel','gu')
					->leftJoin('ScshuxCms\Dacang\Model\CheckGroupModel','gu.group_id=g.id','g')
					->where($where)->getQuery()->execute()->toArray();
		
		if (!$checkUser)
		{
			self::$_error='用户未添加到审核组';return $return;
		}
		//获取当前用户 可以审核的部门
		$departIds=array();
		foreach ($checkUser as $user)
		{
			if ($user['depart_ids'])
			{
				$temp=explode(',', $user['depart_ids']);
				foreach ($temp as $v)
				{
					$v?$departIds[]=$v:'';
				}
			}
		}
		$departIds=array_unique($departIds);
		if (!$departIds)
		{
			self::$_error='用户所属审核组未设置管理部门';return $return;
		}
		
		//根据积分记录id  获取被审核人所属部门
		$dapartId=PointReportItemDetailModel::getDepartById($detailId);
		if (!$dapartId)
		{
			self::$_error='获取被考评人所属部门失败';return $return;
		}
		if ($dapartId['status']==1)
		{
			self::$_error='此积分记录已经审核完成';return $return;
		}
		if (!in_array($dapartId['department_id'], $departIds))
		{
			self::$_error='无审核权限';return $return;
		}
		return true;
	}
	
	
	/**
	 * @desc	获取错误信息
	 * @param			
	 * @return			
	 */
	public static function getError()
	{
		return self::$_error;
	}
}