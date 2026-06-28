<?php
/**
 * 报表归档表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use Phalcon\Di\FactoryDefault;
class  ReportStoresModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function getSource()
	{	
		return $this->getTableName("report_stores");
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ReportStoresModel();
		}
		return self::$_instance;
	}
	
	
	
	/**
	 * 
	 * @desc	报表指标打分进行归档	
	 * @param	int  $reportId  报表id
	 * @return	bool
	 * @date	2017年5月3日
	 */
	public function saveStores($reportId,$time)
	{
		$return = false ;
		if (!$reportId)
		{
			return $return ;
		}
		
		$reportId = intval($reportId) ;
		if (!$time)
		{
			$time = Helper::factory()->getTime()->gmtime() ;
		}
				if (!$this->saveQuotaSnapshot($reportId))
		{
			return $return;
		}
//拼接	sql
		$sql = "insert into ".self::getSource()." (quota_id,quota_total,quota_value,user_id,report_user_id,report_time,report_point,report_id,storestime) " ;
		$sql.= "select quota_id,quota_total,quota_value,user_id,report_user_id,report_time,report_point,report_id,{$time}  FROM ".self::getTableName('report_item')." where report_id = ".$reportId ;
		
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
	 * Archive quota snapshot so archived details and exports keep names and types.
	 */
	protected function saveQuotaSnapshot($reportId)
	{
		$reportId = intval($reportId);
		if (!$reportId)
		{
			return false;
		}

		$sql = "insert ignore into ".self::getTableName('store_quota')." (id,name,remark,company_id,point_desc,type,depart_id) ";
		$sql.= "select distinct q.id,q.name,q.remark,q.company_id,q.point_desc,q.type,q.depart_id ";
		$sql.= "from ".self::getTableName('quota')." q ";
		$sql.= "inner join ".self::getTableName('report_item')." i on i.quota_id = q.id ";
		$sql.= "where i.report_id = ".$reportId;

		try {
			$res = FactoryDefault::getDefault()->getdb()->query($sql);
			return $res ? true : false;
		} catch (\Exception $e) {
			return false;
		}
	}
	/**
	 *
	 * @desc	根据报表id  计算此报表的总分
	 * @param	int $reportid
	 * @param	int $sid
	 * @return	float
	 * @date	2017年5月4日
	 */
	public function getTotalPoint($reportId,$sid)
	{
		$return = 0 ;
		if(!$reportId || !$sid)
		{
			return $return ;
		}
		$reportId = intval($reportId) ;
		$sid = intval($sid) ;
		
		//查询归档时间
		$reportstoresinfo = ReportStoresModel::findFirst($sid) ;
		if (!$reportstoresinfo)
		{
			return  $return ;
		}
		$storestime = $reportstoresinfo->storestime ;
	
		$where = 's.report_id = '.$reportId .' and storestime = '.$storestime;
		
		$items = ReportStoresModel::factory()->getModelsManager()->createBuilder()
												->columns('s.quota_total,s.quota_value,s.report_point,q.type,q.name as qname')
												->addFrom('ScshuxCms\Dacang\Model\ReportStoresModel','s')
												->leftJoin('ScshuxCms\Dacang\Model\StoreQuotaModel','s.quota_id = q.id','q')
												->where($where)
												->getQuery()
												->execute() ;
		
		if($items)
		{
			$return = Utils::totalScore($items);
		}
		
		return $return;
	}

	
	public function getHasStoresList($where,$page,$pagesize)
	{
		$return = new \stdClass() ;
		$offset = ($page-1) * $pagesize ;
	
		$columns='u.id,s.storestime as createdtime,r.id as reportId,u.name,u.avatar,d.name as dname,s.id as sid';
	
		//以需要当前用户评分的用户进行分组
		$return = ReportStoresModel::getModelsManager()->createBuilder()
						->columns($columns)
						->addFrom('ScshuxCms\Dacang\Model\ReportStoresModel','s')
						->leftJoin('ScshuxCms\Dacang\Model\ReportModel','r.id = s.report_id','r')
						->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','s.user_id = u.id','u')
						->leftJoin('ScshuxCms\Dacang\Model\DepartmentModel','d.dingding_id = u.department_id','d')
						->groupBy('r.id,s.storestime')
						->where($where)
						->orderBy('r.id desc')
						->limit($pagesize,$offset)
						->getQuery()
						->execute() ;
	
		return $return ;
	
	}
	
	
	
	/**
	 * @desc	 获取已经归档的的用户列表
	 * @param	int  $where
	 * @return	obj
	 * @date	2017年5月3日
	 */
	public function getPointHasStoresList($where,$page,$pagesize)
	{
		$return = new \stdClass() ;
		$offset = ($page-1) * $pagesize ;
	
		$columns='u.id,s.storestime as createdtime,r.id as reportId,u.name,u.avatar,d.name as dname,s.id as sid';
	
		//以需要当前用户评分的用户进行分组
		$return = ReportStoresModel::getModelsManager()->createBuilder()
						->columns($columns)
						->addFrom('ScshuxCms\Dacang\Model\PointStoresReportItemModel','s')
						->leftJoin('ScshuxCms\Dacang\Model\PointReportModel','r.id = s.report_id','r')
						->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','s.user_id = u.id','u')
						->leftJoin('ScshuxCms\Dacang\Model\DepartmentModel','d.dingding_id = u.department_id','d')
						->groupBy('s.user_id,s.storestime')
						->where($where)
						->orderBy('r.id asc')
						->limit($pagesize,$offset)
						->getQuery()
						->execute() ;
	
		return $return ;
	
	}
	
	/**
	 * @desc	根据报表id    获取已经归档的具体指标  
	 * @param	int  $reportId		报表id
	 * @param	int  $storestime	归档时间
	 * @return	obj
	 * @date	2017年5月3日
	 */
	public function getHasStores($reportId,$storestime,$userId)
	{
		$return = new \stdClass() ;
		if(!$reportId)
		{
			return  $return ;
		}
		
		$reportId = intval($reportId) ;
		$storestime = trim($storestime) ;
		$userId = intval($userId);
		
		$where = "s.report_time != 0 and s.report_id = ".$reportId.' and s.storestime = '.$storestime ;
		$offset = ($page-1) * $pagesize ;
		
		
		$columns = 'q.name as qname,q.remark,q.type,q.point_desc,s.quota_value,s.id,s.quota_id,s.report_time,';
		$columns.= 's.report_point,s.quota_total,u.name as reportusername,qc.id as qc_id,qc.content as qc_comment';
		
		//按照指标进行分组  
		$return = ReportStoresModel::getModelsManager()->createBuilder()
														->columns($columns)
														->addFrom('ScshuxCms\Dacang\Model\ReportStoresModel','s')
														->leftJoin('ScshuxCms\Dacang\Model\StoreQuotaModel','q.id = s.quota_id','q')
														->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','s.report_user_id = u.id','u')
														->leftJoin('ScshuxCms\Dacang\Model\QuotaCommentModel','qc.qid = q.id and qc.user_id='.$userId,'qc')
														->where($where)
														->orderBy('s.id asc')
														->limit($pagesize,$offset)
														->getQuery()
														->execute() ;
		if ($return)
		{
			$data=array();
			foreach ($return as $val)
			{
				$data[$val['quota_id']]['qname']  = $val['qname'];
				$data[$val['quota_id']]['remark'] = $val['remark'];
				$data[$val['quota_id']]['type']   = $val['type'];
				$data[$val['quota_id']]['point_desc']  = $val['point_desc'];
				$data[$val['quota_id']]['quota_value'] = $val['quota_value'];
				$data[$val['quota_id']]['id'] = $val['id'];
				$data[$val['quota_id']]['quota_id'] = $val['quota_id'];
				$data[$val['quota_id']]['report_time'] = $val['report_time'];
				$data[$val['quota_id']]['quota_total'] = $val['quota_total'];
				$data[$val['quota_id']]['report_point'] += Utils::workScode($val);
			}
			$return=(object)array_values($data);
			unset($data);
		}
		return $return ;
	}
	
	
	
	/**
	 * @desc	根据报表id    获取已经归档的具体指标
	 * @param	int  $reportId		报表id
	 * @param	int  $storestime	归档时间
	 * @return	obj
	 * @date	2017年5月3日
	 */
	public function getHasStoresDetail($reportId,$storestime)
	{
		$return = new \stdClass() ;
		if(!$reportId)
		{
			return  $return ;
		}
	
		$reportId = intval($reportId) ;
		$storestime = trim($storestime) ;
	
		$where = "s.report_time != 0 and s.report_id = ".$reportId.' and s.storestime = '.$storestime ;
		$offset = ($page-1) * $pagesize ;
	
	
		$columns = 'q.name as qname,q.remark,q.type,q.point_desc,s.quota_value,s.id,s.quota_id,s.report_time,s.quota_total,';
		$columns.= 's.report_point ,u.name as reportusername';
	
		//按照指标进行分组
		$return = ReportStoresModel::getModelsManager()->createBuilder()
													->columns($columns)
													->addFrom('ScshuxCms\Dacang\Model\ReportStoresModel','s')
													->leftJoin('ScshuxCms\Dacang\Model\StoreQuotaModel','q.id = s.quota_id','q')
													->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','s.report_user_id = u.id','u')
													->where($where)
													->orderBy('s.id asc')
													->limit($pagesize,$offset)
													->getQuery()
													->execute() ;
	
		return $return;
	}
	
	
	
}
