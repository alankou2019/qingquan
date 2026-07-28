<?php
/**
 * 额外加减分 评分说明
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;

use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;

class  ExtraReportDescModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("extra_report_desc"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\ExtraReportItemModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ExtraReportDescModel();
		}
		return self::$_instance;
	}
	
	/**
	 * @desc	增加
	 * @param	$report_id 			报表id	
	 * @param	$desc				评分说明
	 * @return			
	 */
	public static function add($report_id,$desc)
	{
		$return = false ;
		if (!$report_id)
		{
			return $return ;
		}
		
		$report_id = intval($report_id) ;
		$desc      = trim($desc) ;
		
		//判断是否存在
		$descinfo = self::factory()->findFirst($report_id) ;
		if ($descinfo)
		{
			$descinfo->desc = $desc ;
			$descinfo->save() ;
			
			return true ;
		}

		
		$data = array(
				'rid'  => $report_id,
				'desc' => $desc
		) ;
		
		$res = self::factory()->saveData($data) ;
		if ($res)
		{
			$return = true ;
		}
		
		return $return ;
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
	 * @desc	删除单列对象
	 * @param			
	 * @return			
	 */
	public static function delFactory()
	{
		self::$_instance = null;
	}
	
	
}