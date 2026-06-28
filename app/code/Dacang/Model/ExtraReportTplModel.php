<?php
/**
 * 报表模版
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
class  ExtraReportTplModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function getSource()
	{
		return $this->getTableName("extra_report_item_tpl");
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ExtraReportTplModel();
		}
		return self::$_instance;
	}
	
	
	/**
	 * @desc	保存指标的加减分项
	 * @param			
	 * @return			
	 */
	public static function addmore($extrareportuserid,$reportId)
	{
		$return = false ;
		if (!$extrareportuserid || !$reportId)
		{
			return $return ;
		}
		
		$reportId = intval($reportId) ;
		$extrareportuserid = trim($extrareportuserid) ;
		$userarr = explode(',', $extrareportuserid) ;
		
		self::factory()->deleteBySql('report_id = '.$reportId) ;
		foreach ($userarr as $user)
		{
			$data = array(
					'report_id'=> $reportId,
					'report_user_id' => $user
			) ;
				
			self::factory()->save($data) ;
				
				
			self::delFactory() ;
		}
	}
	
	
	
	/**
	 * @desc	根据模版id  获取加减分的评分人的名称  id
	 * @param	$tplid		
	 * @return			
	 */
	public static function getExtraData($tplid)
	{
		$return = array() ;
		if (!$tplid)
		{
			return $return ;
		}
		$tplid = intval($tplid) ;
		
		$where = 'e.report_id = '.$tplid ;
		$items = self::factory()->getModelsManager()->createBuilder()
								->columns('u.id,u.name')
								->addFrom('ScshuxCms\Dacang\Model\ExtraReportTplModel','e')
								->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','e.report_user_id = u.id','u')
								->where($where)
								->getQuery()
								->execute() ;
	
		if (count($items) > 0)
		{
			foreach ($items as $item)
			{
				$return['username'] .= $item->name.',';
				$return['userid'] .= $item->id.',';
			}
			
			$return['username'] = rtrim($return['username'],',') ;
			$return['userid'] = rtrim($return['userid'],',') ;
		}
		
		return $return ;
	}
	
	/**
	 * @desc	删除单列
	 * @param			
	 * @return			
	 */
	public static function delfactory()
	{
		self::$_instance = null ;
	}
	
	
}