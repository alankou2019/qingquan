<?php
/**
 * 报表-》用户
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
class  ReportUserModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("report_user"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ReportUserModel();
		}
		return self::$_instance;
	}
	
	
	/**
	 * 
	 * @desc	根据报表id  被考核人id  修改状态
	 * @param	int $reportId
	 * @param	int $uid
	 * @return	bool	
	 * @date	2017年5月3日
	 */
	public function setStatus($reportId,$uid)
	{
		$return = false ;
		if(!$reportId || !$uid)
		{
			return $return ;
		}
		$reportId = intval($reportId) ;
		$uid = intval($uid) ;
		
		$reportuser = ReportUserModel::findFirst('report_id = '.$reportId.' and user_id = '.$uid) ;
		
		if($reportuser)
		{
			$reportuser->state = 1 ;
			if($reportuser->save())
			{
				$return = true	;
			}
		}
		
		return  $return	 ;
	}
}