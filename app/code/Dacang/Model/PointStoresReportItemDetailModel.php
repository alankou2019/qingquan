<?php
/**
 * 积分考评  打分详细归档表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Constants;
class  PointStoresReportItemDetailModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("point_stores_report_item_detail"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new PointStoresReportItemDetailModel();
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
		//拼接sql
		$sql = "insert into ".self::getSource()." (report_id,quota_id,user_id,point,reason,created_at,storestime) " ;
		$sql.= "select report_id,quota_id,user_id,point,reason,created_at,{$time} FROM ".self::getTableName('point_report_item_detail').
			   " where report_id = ".$reportId ;
		
		try {
			$db =self::factory()->getDB();
			$res=$db->query($sql);
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
	
	
	
	/**
	 * @desc	 获取已经归档的的用户列表
	 * @param	int  $where
	 * @return	obj
	 * @date	2017年5月3日
	 */
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
	
	
	/**
	 * @desc	积分考评 指标评分记录
	 * @param	$reportId		报表id
	 * @param	$quotaId		指标id
	 * @return
	 */
	public static function showPoint($reportId,$quotaId,$storestime)
	{
		$return = new \stdClass();
		if(!$reportId || !$quotaId)
		{
			return $return ;
		}
		$offset = ($page-1)*$pagesize;
		$where = ' i.quota_id = '.intval($quotaId).' and i.report_id = '.intval($reportId).' and i.storestime='.$storestime;
	
		$dataList = new \stdClass();
		$count = self::factory()->modelsManager->createBuilder()
								->columns("count(*) as num")
								->addFrom('ScshuxCms\Dacang\Model\PointStoresReportItemDetailModel','i')
								->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.user_id = u.id','u')
								->where($where)
								->getQuery()
								->execute() ;
	
		$items = self::factory()->modelsManager->createBuilder()
								->columns("i.id,i.created_at as report_time,i.point as report_point,u.name,i.reason")
								->addFrom('ScshuxCms\Dacang\Model\PointStoresReportItemDetailModel','i')
								->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.user_id = u.id','u')
								->where($where)
								->orderBy('u.id asc')
								->limit($pagesize,$offset)
								->getQuery()
								->execute() ;
	
		$dataList->count       = empty($count)?0:$count[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		$dataList->items = $items;
		return  $dataList ;
	}
	
	
	/**
	 * @desc	获取导出数据
	 * @param	$reportId		
	 * @param	$sid		
	 * @return			
	 */
	public static function getExportData($reportId,$sid)
	{
		if (!$reportId || !$sid)
		{
			return false;
		}
		$reportId=intval($reportId);
		$sid=intval($sid);
		
		$storesItem=PointStoresReportItemModel::findFirst($sid);
		if (!$storesItem)
		{
			return false;
		}
		$storestime=$storesItem->storestime;
		$where='d.storestime='.$storestime.' and d.report_id='.$reportId;
		$columns='d.report_id,d.quota_id,d.user_id,d.point,d.reason,d.created_at,u.name as username,q.name as quotaname,q.type as quotatype';
		
		$items=self::factory()->getModelsManager()->createBuilder()->columns($columns)
			->where($where)->addFrom('ScshuxCms\Dacang\Model\PointStoresReportItemDetailModel','d')
			->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','d.user_id=u.id','u')
			->leftJoin('ScshuxCms\Dacang\Model\StoreQuotaModel','d.quota_id=q.id','q')
			->getQuery()->execute()->toArray();
		
		//补充被考评人名称  考评表名称
		if ($items)
		{
			$reportname='';
			$reportusername='';
			$quotatype=Constants::getQuotaType();
			foreach ($items as $key=>$val)
			{
				if ($key==0)
				{
					$reportInfo=PointReportModel::findFirst($val['report_id']);
					$reportname=$reportInfo->name;
					$userInfo=CompanyUserModel::findFirst($storesItem->user_id);
					$reportusername=$userInfo->name;
				}
				$items[$key]['reportname']=$reportname;
				$items[$key]['reportusername']=$reportusername;
				$items[$key]['quotatypename']=$quotatype[$val['quotatype']];
				$items[$key]['created_at']=Helper::factory()->getTime()->localDate('Y-m-d H:i',$val['created_at']);
			}
		}
		
		return $items;
	}
	
}
