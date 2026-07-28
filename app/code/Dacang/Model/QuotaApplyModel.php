<?php
/**
 * 指标申请表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Constants;
use ScshuxCms\User\Model\UserModel;
class QuotaApplyModel extends BaseModel
{

	protected static  $_instance=null;

	public function initialize()
	{
		$this->setSource($this->getTableName("quota_apply"));
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\QuotaModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new QuotaApplyModel();
		}
		return self::$_instance;
	}
	
	
	
	/**
	 * @desc	删除实例  循环添加的时候使用
	 * @param			
	 * @return			
	 */
	public static function delFactory()
	{
		self::$_instance = null ;
	}
	
	
	/**
	 * @desc	通过指标
	 * 将指标添加到指标库     并且添加到指标考评表里面	
	 * @param	$id		
	 * @return			
	 */
	public static function passQuota($apply)
	{
		if (!$apply)
		{
			return false;
		}
		if (is_numeric($apply))
		{
			$apply=self::findFirst(intval($apply));
			if (!$apply)
			{
				return false;
			}
		}
		//添加到指标库
		$quotaData=array(
				'name'=>$apply->name,
				'company_id'=>$apply->company_id,
				'point_desc'=>$apply->point_desc,
				'type'=>$apply->type,
				'depart_id' =>$apply->depart_id,
		);
		$res=QuotaModel::factory()->saveData($quotaData);
		if (!$res)
		{
			return false;
		}
		
		$apply->delete();
		$quotatype=Constants::getQuotaType();
		return array(
				'id'=>QuotaModel::factory()->id,
				'quota'=>$apply->name,
				'quotatype'=>$quotatype[$apply->type],
				'quotatypeval'=>$apply->type
		);
		return false;
	}
	
	
	
	/**
	 * @desc	获取待审核指标
	 * @param			
	 * @return			
	 */
	public static function getApplyQuota($reportId)
	{
		if (!$reportId)
		{
			return false;
		}
		$reportId=intval($reportId);
		
		$where='q.report_id='.$reportId;
		$columns='q.id,q.name,q.point_desc,q.type,q.created_at,u.name as username';
		$items=self::factory()->getModelsManager()->createBuilder()->columns($columns)
		->addFrom('ScshuxCms\Dacang\Model\QuotaApplyModel','q')
		->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','q.user_id=u.id','u')
		->where($where)->getQuery()->execute();
		
		return $items;
	}
	
	
	
	/**
	 * @desc	获取指标申请状态
	 * @param			
	 * @return			
	 */
	public static function getApplyStatus($key)
	{
		$arr = array(
				'yes'=>1,
				'no' =>2
		);
		
		if ($key)
		{
			$key=trim($key);
			return $arr[$key]?$arr[$key]:'';
		}
		return $arr;
	}

}