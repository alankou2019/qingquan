<?php
/**
 * 额外加减分 报表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;

use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;

class  ExtraReportItemModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function getSource()
	{
		return $this->getTableName("extra_report_item");
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\ExtraReportItemModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ExtraReportItemModel();
		}
		return self::$_instance;
	}
	
	/**
	 * @desc	增加
	 * @param	$extrareportuserid	评分用户	
	 * @param	$report_id 			报表id	
	 * @param	$userid				被考核人id
	 * @return			
	 */
	public static function add($extrareportuserid,$report_id,$userid)
	{
		$return = false ;
		if (!$extrareportuserid)
		{
			return $return ;
		}
		
		$report_id = intval($report_id) ;
		$extrareportuserid = trim($extrareportuserid) ;
		$userarr = explode(',', $extrareportuserid) ;
		
		self::factory()->deleteBySql('report_id = '.$report_id) ;
		foreach ($userarr as $user)
		{
			$data = array(
					'user_id'  => $userid,
					'report_id'=> $report_id,
					'report_user_id' => $user
			) ;	
			
			self::factory()->save($data) ;
			
			
			self::delFactory() ;
		}
		
	}
	
	
	
	
	/**
	 * @desc	获取报表加减分的评分人员
	 * @param	$reportid		
	 * @return			
	 */
	public static function getExtraUser($reportid)
	{
		$return = array();
		if (!$reportid)
		{
			return $return ;
		}
		
		$reportid = intval($reportid) ;
		$columns = 'c.name,c.id as userid,e.report_time,e.report_point' ;
		$where = 'e.report_id = '.$reportid;
		
		$items = ExtraReportItemModel::factory()->getModelsManager()->createBuilder()
									->columns($columns)
									->addFrom('ScshuxCms\Dacang\Model\ExtraReportItemModel','e')
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
							->addFrom('ScshuxCms\Dacang\Model\ExtraReportItemModel')
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
							->addFrom('ScshuxCms\Dacang\Model\ExtraReportItemModel')
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
	 * @desc	根据报表id   评分人id  获取额外的加减分项
	 * @param	$reportid
	 * @param	$userid			
	 * @return			
	 */
	public static function getExtrPoint($reportid,$userid)
	{
		$return  = array() ;
		if (!$reportid || !$userid)
		{
			return $return ;
		}
		
		$reportid = intval($reportid) ;
		$userid = intval($userid) ;
		
		$where = 'report_id = '.$reportid.' and report_user_id = '.$userid;
		
		$items = self::factory()->getModelsManager()->createBuilder()
								->columns('report_point')
								->addFrom('ScshuxCms\Dacang\Model\ExtraReportItemModel')
								->where($where)
								->getQuery()
								->execute()
								->toArray() ;
		if (count($items) > 0)
		{
			$return = $items[0]['report_point'] ;
		}
		
		return $return ;
		
		
	}
	
	
	
	/**
	 *
	 * @desc	情况报表的加减分信息
	 * @param	int  $reportId  报表id
	 * @return	bool
	 * @date	2017年5月3日
	 */
	public function clearPoint($reportId)
	{
		$return = false ;
		if (!$reportId)
		{
			return $return ;
		}
	
		$reportId = intval($reportId) ;
		$items = ExtraReportItemModel::find('report_id = '.$reportId);
		if($items)
		{
			foreach ($items as $item)
			{
				$item->report_time = 0;
				$item->report_point = 0;
	
				try {
					$item->save() ;
				}catch(\Exception $e){
					return false ; break;
				}
			}
			$return = true ;
		}
	
		return $return ;
	
	}
	
	
	
	
	/**
	 * @desc	删除单列对象
	 * @param			
	 * @return			
	 */
	public static function delFactory()
	{
		self::$_instance = null;
	}
	
	
}