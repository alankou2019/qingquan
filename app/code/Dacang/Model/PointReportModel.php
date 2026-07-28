<?php
/**
 * 积分考评表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
class  PointReportModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("point_report"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new PointReportModel();
		}
		return self::$_instance;
	}
	
	
	/**
	 * 
	 * @desc 添加积分考评表 
	 * @param  $data       post发送数据
	 * @param  $compantId  公司id
	 * @return bool || int
	 * @date 2017年4月21日
	 */
	public function addreport($data,$compantId)
	{
		if(!$data || !$compantId)
		{
			return false ;
		}
		
		$this->name   = $data->get('name') ? trim($data->get('name')) : '';
		$this->remark = $data->get('remark') ? trim($data->get('remark')) : '' ;
		$this->from   = $data->get('reportfrom') ? trim($data->get('reportfrom')) : '';
		$this->sum    = $data->get('sum') ? intval($data->get('sum')) : '';
		$this->company_id = $compantId;
		$this->created    = Helper::factory()->getTime()->gmtime() ;
		$this->auto_run_date = $data->get('auto_run_date') ? intval($data->get('auto_run_date')) : '';
		
		if($this->save()){
			if (!empty($data->get('auto_run_date')))
			{
				AutoRunModel::factory()->saveOne($this->id,$data->get('auto_run_date'));
			}
			//获取自增id
			return $this->id ;
		}
		else 
		{
			return false ;
		}
	}
	
	
	
	/**
	 *
	 * @desc   修改报表
	 * @param  $data       post发送数据
	 * @return bool || int
	 * @date 2017年4月21日
	 */
	public function savaReport($data)
	{
		$return  = false ;
		$id = $data->get('id');
		if(!$id)
		{
			return $return ;
		}
		$id = intval($id) ;
		$reportinfo = PointReportModel::findFirst($id) ;
		if(!$reportinfo)
		{
			return $return ;
		}
		
		$reportinfo->name   = $data->get('name') ? trim($data->get('name')) : '';
		$reportinfo->remark = $data->get('remark') ? trim($data->get('remark')) : '' ;
		$reportinfo->sum    = $data->get('sum') ? intval($data->get('sum')) : '';
		$reportinfo->auto_run_date = $data->get('auto_run_date') ? intval($data->get('auto_run_date')) : '';
		
		if($reportinfo->save())
		{
			if (!empty($data->get('auto_run_date')))
			{
				AutoRunModel::factory()->saveOne($reportinfo->id,$data->get('auto_run_date'));
			}
			$return = $reportinfo->id ;
		}
		return $return ;
	}
	
	
	
	/**
	 * 
	 * @desc	获取指标具体评分情况
	 * @param	int   $reportId		
	 * @param	int   $quotaId		
	 * @return	int	  $compantId
	 * @return  obj		
	 * @date	2017年5月3日
	 */
	public function showPoint($reportId,$quotaId,$compantId,$page,$pagesize)
	{
		$return = new \stdClass();
		if(!$reportId || !$quotaId || !$compantId)
		{
			return $return ;
		}
		
		
		
		$offset = ($page-1)*$pagesize;
		$where = ' i.quota_id = '.intval($quotaId).' and i.report_id = '.intval($reportId).' and u.company_id = '.$compantId;
		
		$dataList = new \stdClass();
		$count = $this->modelsManager->createBuilder()
										->columns("count(*) as num")
										->addFrom('ScshuxCms\Dacang\Model\ReportItemModel','i')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.report_user_id = u.id','u')
										->where($where)
										->getQuery()
										->execute() ;
		
		$items = $this->modelsManager->createBuilder()
										->columns("i.report_time,i.report_point,u.name,i.quota_total")
										->addFrom('ScshuxCms\Dacang\Model\ReportItemModel','i')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.report_user_id = u.id','u')
										->where($where)
										->orderBy('u.id asc')
										->limit($pagesize,$offset)
										->getQuery()
										->execute() ;
		
		$dataList->count       = $count[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		
		$dataList->items = $items;
		return  $dataList ;
	}
	
	
	
	/**
	 *
	 * @desc	获取归档具体评分情况
	 * @param	int   $reportId
	 * @param	int   $quotaId
	 * @param	int   $sid
	 * @return	int	  $compantId
	 * @return  obj
	 * @date	2017年5月3日
	 */
	public function showStoresPoint($reportId,$quotaId,$sid,$compantId,$page,$pagesize)
	{
		$return = new \stdClass();
		if(!$reportId || !$quotaId || !$compantId || !$sid)
		{
			return $return ;
		}
	
	
		$offset = ($page-1)*$pagesize;
		//获取storestime
		$sid = intval($sid) ;
		$storesinfo = ReportStoresModel::findFirst($sid) ;
		if(!$storesinfo)
		{
			return $return ;
		}
		
		$storestime = $storesinfo->storestime ;
		$where = ' s.quota_id = '.intval($quotaId).' and s.report_id = '.intval($reportId).' and u.company_id = '.$compantId.' and s.storestime='.$storestime;
	
		$count= $items = $this->modelsManager->createBuilder()
										->columns("count(*) as num")
										->addFrom('ScshuxCms\Dacang\Model\ReportStoresModel','s')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','s.report_user_id = u.id','u')
										->where($where)
										->getQuery()
										->execute() ;
		
		$dataList = new \stdClass();
		$items = $this->modelsManager->createBuilder()
										->columns("s.report_time,s.report_point,u.name,s.quota_total")
										->addFrom('ScshuxCms\Dacang\Model\ReportStoresModel','s')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','s.report_user_id = u.id','u')
										->where($where)
										->orderBy('u.id asc')
										->limit($pagesize,$offset)
										->getQuery()
										->execute() ;
	
		$dataList->count       = $count[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
	
		$dataList->items = $items;
		return  $dataList ;
	}
	
	
	/**
	 * 
	 * @desc	根据用户id  获取用户已经建立的报表数目
	 * @param	int  $uid 用户id
	 * @return	int
	 * @date	2017年5月5日
	 */
	public function getHasReport($uid)
	{
		$return = 0 ;
		if(!$uid)
		{
			return $return ;
		}
		
		$uid = intval($uid) ;
		$where = 'user_id = '.$uid;
		
		$count = ReportUserModel::factory()->getModelsManager()->createBuilder()
												->columns('count(*) as num')
												->addFrom('ScshuxCms\Dacang\Model\ReportUserModel')
												->where($where)
												->getQuery()
												->execute();
		if($count)
		{
			$return = $count[0]->num ;
		}
		
		return $return; 
	}
	
	
	
	
	
}