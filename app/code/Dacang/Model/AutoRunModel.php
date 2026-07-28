<?php
/**
 * 自动运行考核表设置
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;

use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper;
class AutoRunModel extends BaseModel
{

	protected static  $_instance=null;

	public function initialize()
	{
		$this->setSource($this->getTableName("auto_run"));
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\CompanyModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new AutoRunModel();
		}
		return self::$_instance;
	}
	
	/**
	 * @desc	保存一条数据
	 * @param	$reportId		
	 * @param	$rundate		
	 * @return			
	 */
	public function saveOne($reportId,$rundate)
	{
		if (!$reportId || !$rundate)
		{
			return false;
		}
		$reportId=intval($reportId);
		$rundate=intval($rundate);
		
		$data = array(
				'report_Id'=>$reportId,
				'run_date' =>$rundate,
		);
		$runInfo=self::factory()->findFirst('report_Id='.$reportId);
		if ($runInfo)
		{
			$runInfo->run_date=$rundate;
			$runInfo->save();
		}
		else
		{
			$data['created_at']=Helper::factory()->getTime()->gmtime();
			self::factory()->saveData($data);
		}
		return true;
	}
	
	
	/**
	 * @desc	单列 禁止实例化对象
	 * @param			
	 * @return			
	 */
	private function __clone()
	{

	}
	
}