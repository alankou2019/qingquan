<?php
/**
 * 积分考评表模版
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
class  PointReportTplModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("point_report_tpl"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new PointReportTplModel();
		}
		return self::$_instance;
	}
	
	
	/**
	 * 
	 * @desc 添加报表模版
	 * @param  $data       post发送数据
	 * @param  $compantId  公司id
	 * @return bool || int
	 * @date 2017年4月21日
	 */
	public function addreporttpl($data,$compantId)
	{
		if(!$data || !$compantId)
		{
			return false ;
		}
		
		$this->name   = $data->get('name') ? trim($data->get('name')) : '';
		$this->remark = $data->get('remark') ? trim($data->get('remark')) : '' ;
		$this->company_id = $compantId;
		$this->created = Helper::factory()->getTime()->gmtime() ;
		
		if($this->save()){
			//返回自增id
			return $this->id ;
		}
		else 
		{
			return false ;
		}
	}
	
	
	
	/**
	 * 
	 * @desc	根据公司id  判断此公司可以生成的模版数 是否已经达到限制
	 * @param	$companyId		
	 * @return	bool		
	 * @date	2017年5月5日
	 */
	public function checkTplNum($companyId)
	{
		//默认返回 true  表示已经到达限制条数
		$return = true ;
		
		if(!$companyId)
		{
			return  $return ;
		}
		
		$companyId = intval($companyId) ;
		$company = CompanyModel::findFirst($companyId) ;
		$reporttpllimit = $company->reporttpllimit ;
		
		//获取已经保存的模版数量
		$tplnum = $this->getTplNum($companyId) ;
		
		if($reporttpllimit > $tplnum)
		{
			$return = false ;
		}
		return $return ;
		
	}
	
	
	/**
	 * 
	 * @desc	根据公司id 判断此公司已经生成的模版数
	 * @param	int  $companyId		
	 * @return	int		
	 * @date	2017年5月5日
	 */
	public function getTplNum($companyId)
	{
		$return = 9999999;
		if(!$companyId)
		{
			return $return ;
		}
		return Utils::getTplNum($companyId);
	}
	
}