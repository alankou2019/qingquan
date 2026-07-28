<?php
/**
 * 积分审核记录
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use Phalcon\Di\FactoryDefault;
use Phalcon\Cache\Frontend\Data;
class  PointCheckModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("point_check"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new PointCheckModel();
		}
		return self::$_instance;
	}

	
	
	/**
	 * @desc	保存审核记录
	 * @param	$data		
	 * @return	boolean		
	 */
	public static function saveData($data)
	{
		if (!$data || !is_array($data))
		{
			return false;
		}
		$nowtime=Helper::factory()->getTime()->gmtime();
		$db=FactoryDefault::getDefault()->get('db');
		$sql=' insert into '.self::factory()->getSource().'(item_detail_id,user_id,status,created_at) values("'.intval($data['item_detail_id']).
			'","'.intval($data['user_id']).'","'.intval($data['status']).'","'.$nowtime.'") on duplicate key update created_at='.$nowtime;
		
		$res=$db->query($sql);
		if ($res)
		{
			//判断所有审核人是否都已经审核通过
			if (self::allIsCheck($data['item_detail_id']))
			{
				PointReportItemDetailModel::UpStatus($data['item_detail_id']);
			}
			return true;
		}
		return false;
	}
	
	
	
	/**
	 * @desc	根据考评记录表id  检查是否所有审核人都已经审核通过
	 * @param	$id		考评记录表		
	 * @return			
	 */
	public static function allIsCheck($id)
	{
		if (!$id)
		{
			return false;
		}
		
		//获取此考评记录  对应的考评人的部门id
		$id=intval($id);
		$departId=PointReportItemDetailModel::getDepartById($id);
		if (!$departId)
		{
			return false;
		}
		//获取需要审核该部门所有审核人
		$groupUser=CheckGroupModel::getGroupUserByDepartId($departId['department_id']);
		if (!$groupUser)
		{
			//如果未设置考评人  则表示为已经全部审核通过
			return true;
		}
		//获取已经参与考评的审核人
		$hasCheckUser=self::getHasCheckUser($id);
		//如果需要考评的人员已经全部考评  那么就表示已经考评完
		if (empty(array_diff($groupUser, $hasCheckUser)))
		{
			return true;
		}
		return false;
		
	}
	
	
	
	/**
	 * @desc	获取已经参与审核的审核人
	 * @param	$id		
	 * @return			
	 */
	public static function getHasCheckUser($id)
	{
		$return=array();
		if (!$id)
		{
			return $return;
		}
		$hasCheckUser=self::find('item_detail_id='.$id.' and status=1');
		if ($hasCheckUser)
		{
			foreach ($hasCheckUser as $user)
			{
				$return[]=$user->user_id;
			}
		}
		return $return;
	}
	
	
	/**
	 * @desc	获取待审核的报表
	 * @param	$userId		
	 * @return	array		
	 */
	public static function getHavaCheckReport($userId,$page,$pageSize)
	{
		$return=array();
		if (!$userId)
		{
			return $return;
		}
		
		$page=$page?intval($page):1;
		$pageSize=$pageSize?intval($pageSize):10;
		$offset=($page-1)*$pageSize;
		//先获取当前用户 能管理的部门列表  以便生成where条件
		$departIds=CheckGroupModel::getDepartIdsByUserId($userId);
		if (!$departIds)
		{
			return $return;
		}
		//将部门自增id  转为钉钉部门id
		$departIds=CompanyDepartModel::idAdpter(trim(implode(',', $departIds),','));
		if (!$departIds)
		{
			return $return;
		}
		//获取被考评人属于此部门  并且考评表正在进行中 的考评表列表
		$where=is_numeric($departIds)?'u.department_id = '.$departIds:'u.department_id in ('.$departIds.')';
		$where.=' and pu.state=0';
		$columns='p.id,p.name,p.created,d.name as dname';
		$items=self::factory()->getModelsManager()->createBuilder()->columns($columns)
					->addFrom('ScshuxCms\Dacang\Model\PointReportModel','p')
					->leftJoin('ScshuxCms\Dacang\Model\PointReportItemModel','p.id=i.report_id','i')
					->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.user_id=u.id','u')
					->leftJoin('ScshuxCms\Dacang\Model\CompanyDepartModel','u.department_id=d.dingding_id','d')
					->leftJoin('ScshuxCms\Dacang\Model\PointReportUserModel','pu.report_id=p.id','pu')
					->where($where)->groupBy('p.id')->getQuery()->execute()->toArray();
		
		if ($items)
		{
			//删除已经审核完成的考评表
			foreach ($items as $key=>$val)
			{
				$isCheck=self::isAllCheck($val['id'], $userId);
				if ($isCheck)
				{
					unset($items[$key]);
				}
			}
		}
		return $items;
	}
	
	
	
	/**
	 * @desc	根据考评表id，用户id，判断用户对此考评表的积分记录是否已经全部审核
	 * @param	$reportId		
	 * @param	$userId		
	 * @return			
	 */
	public static function isAllCheck($reportId,$userId)
	{
		$return=false;
		if (!$reportId || !$userId)
		{
			return $return;
		}
		$reportId=intval($reportId);
		$userId=intval($userId);
		//需要进行审核的积分记录条数
		$haveCheckCount=PointReportItemDetailModel::find('report_id='.$reportId)->count();
		
		//已经审核的积分记录条数
		$where='d.report_id='.$reportId.' and c.user_id='.$userId;
		$hasCheckCount=self::factory()->getModelsManager()->createBuilder()->columns('c.id')
				->addFrom('ScshuxCms\Dacang\Model\PointReportItemDetailModel','d')
				->leftJoin('ScshuxCms\Dacang\Model\PointCheckModel','c.item_detail_id=d.id','c')
				->where($where)->getQuery()->execute()->count();
		if ($haveCheckCount==$hasCheckCount)
		{
			$return=true;
		}
		return $return;
	}
	
	
	/**
	 * @desc	获取待审核的积分记录
	 * @param	$userId		
	 * @param	$reportId		
	 * @return			
	 */
	public static function getHavaCheckPoint($data)
	{
		$userId=$data['user_id'];
		$reportId=$data['report_id'];
		
		if (!$userId || !$reportId)
		{
			return false;
		}
		$userId=intval($userId);
		$reportId=intval($reportId);
		//积分记录表的状态为未通过并且当前用户还未对此记录进行审核
		$where='d.report_id='.$reportId.' and d.status=0';
		$columns='d.id,d.point,d.reason,d.created_at,q.name as qname,c.status as checkstatus';
		$items=self::factory()->getModelsManager()->createBuilder()->columns($columns)
				->addFrom('ScshuxCms\Dacang\Model\PointReportItemDetailModel','d')
				->leftJoin('ScshuxCms\Dacang\Model\PointCheckModel','c.item_detail_id = d.id and c.user_id='.$userId,'c')
				->leftJoin('ScshuxCms\Dacang\Model\QuotaModel','q.id=d.quota_id','q')
				->where($where)->orderBy('d.id desc')->getQuery()->execute();
		
		return $items;
	}
	
	
	/**
	 * @desc	积分审核状态
	 * @param			
	 * @return			
	 */
	public static function getStatus()
	{
		$arr=array(
				'0'=>'未通过',
				'1'=>'通过'
		);
		return $arr;
	}
	
	/**
	 * @desc	获取对应状态
	 * @param			
	 * @return			
	 */
	public static function getMapStatus($key)
	{
		$arr=array(
				'yes'=>'1',
				'no' =>'0'
		);
		return $arr[$key];
	}
}