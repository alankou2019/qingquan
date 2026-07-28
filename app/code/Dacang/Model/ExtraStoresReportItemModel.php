<?php
/**
 * 归档报表 额外的加减分
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;

use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use Phalcon\Di\FactoryDefault;

class  ExtraStoresReportItemModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("extra_stores_report_item"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ExtraStoresReportItemModel();
		}
		return self::$_instance;
	}
	
	
	
	/**
	 * 
	 * @desc	报表额外的加减分进行归档	
	 * @param	int  $reportId  报表id
	 * @return	bool
	 * @date	2017年5月3日
	 */
	public function saveExtraStoresItem($reportId,$time)
	{
		$return = false ;
		if (!$reportId)
		{
			return $return ;
		}
		
		$reportId = intval($reportId) ;
		if (!$time)
		{
			$time = Helper::factory()->getTime()->gmtime();
		}
		
		//拼接	sql
		$sql = "insert into ".self::getSource()." (user_id,report_user_id,report_time,report_point,report_id,storestime) " ;
		$sql.= "select user_id,report_user_id,report_time,report_point,report_id,{$time}  FROM ".self::getTableName('extra_report_item')." where report_id = ".$reportId ;
		
		try {
			$res = FactoryDefault::getDefault()->getdb()->query($sql);
			if($res)
			{
				$return = true ;
			}
			
		}catch (\Exception $e){
			$return = false ;
		}
		
		return $return ;
		
	}
	
	
	
	/**
	 * @desc	根据报表id  计算类加分
	 * @param	$reportid
	 * @return
	 */
	public static function getSumPoint($reportid)
	{
		$return = 0 ;
		if (!$reportid)
		{
			return $return ;
		}
	
		$reportid = intval($reportid) ;
		$where = 'report_id = '.$reportid ;
		$items = self::factory()->getModelsManager()->createBuilder()
						->columns('sum(report_point) as point')
						->addFrom('ScshuxCms\Dacang\Model\ExtraStoresReportItemModel')
						->where($where)
						->getQuery()
						->execute() ;
	
		if (count($items) > 0)
		{
			$return = $items[0]->point ;
		}
	
		return $return ;
	
	}
	
	
	/**
	 * @desc	获取报表加减分的评分人员
	 * @param	$reportid
	 * @return
	 */
	public static function getExtraUser($reportid,$sid)
	{
		$return = array();
		if (!$reportid)
		{
			return $return ;
		}
	
		//获取生成考核表的时间
		$storesinfo = ReportStoresModel::findFirst($sid) ;
		if (!$storesinfo)
		{
			return $return ;
		}
		
		$storestime = $storesinfo->storestime ;
		
		$reportid = intval($reportid) ;
		$columns = 'c.name,c.id as userid,e.report_time,e.report_point' ;
		$where = 'e.report_id = '.$reportid.' and storestime = '.$storestime;
	
		$items = ExtraReportItemModel::factory()->getModelsManager()->createBuilder()
								->columns($columns)
								->addFrom('ScshuxCms\Dacang\Model\ExtraStoresReportItemModel','e')
								->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','e.report_user_id = c.id','c')
								->where($where)
								->getQuery()
								->execute()
								->toArray() ;
	
		if (count($items) > 0)
		{
			$return = $items ;
		}
	
		return $return;
	}
	
	
	/**
	 * @desc	根据报表id  获取加减分评分说明
	 * @param	$id
	 * @return
	 */
	public static function getDesc($id) 
	{
		$return = '' ;
		if (!$id)
		{
			return $return ;
		}
	
		$id = intval($id) ;
		$desc = self::factory()->findFirst($id) ;
		if ($desc)
		{
			$return = $desc->desc ;
		}
	
		return $return ;
	}	
	
	
	
	
	/**
	 * @desc	根据报表id  计算平均分
	 * @param	$reportid
	 * @return
	 */
	public static function getAvgPoint($reportid)
	{
		$return = 0 ;
		if (!$reportid)
		{
			return $return ;
		}
	
		$reportid = intval($reportid) ;
		$where = 'report_id = '.$reportid ;
		$items = self::factory()->getModelsManager()->createBuilder()
		->columns('sum(report_point) as point')
		->addFrom('ScshuxCms\Dacang\Model\ExtraStoresReportItemModel')
		->where($where)
		->getQuery()
		->execute() ;
	
		if (count($items) > 0)
		{
			$return = $items[0]->point ;
		}
	
		return $return ;
	
	}
	
	
}