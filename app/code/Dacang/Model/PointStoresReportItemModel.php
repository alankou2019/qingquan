<?php
/**
 * 积分考评表 归档具体指标
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use Phalcon\Di\FactoryDefault;
class  PointStoresReportItemModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("point_stores_report_item"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new PointStoresReportItemModel();
		}
		return self::$_instance;
	}
	
	
	/**
	 * @desc	积分考评  归档操作 保存具体指标
	 * @param	$reportId		考评表		
	 * @param	$time			归档时间	
	 * @return	boolean		
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
		$sql = "insert into ".self::getSource()." (quota_id,quota_value,user_id,report_user_id,report_id,storestime) " ;
		$sql.= "select quota_id,quota_value,user_id,report_user_id,report_id,{$time} FROM ".self::getTableName('point_report_item').
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
	
		$where = " s.report_id = ".$reportId.' and s.storestime = '.$storestime ;
		$offset = ($page-1) * $pagesize ;
	
	
		$columns = 'q.name as qname,q.remark,q.type,q.point_desc,s.quota_value,s.id,s.quota_id,';
		$columns.= 'u.name as reportusername,qc.id as qc_id,qc.content as qc_comment,sum(d.point) as report_point ';
		//按照指标进行分组
		$return = self::factory()->getModelsManager()->createBuilder()
		->columns($columns)
		->addFrom('ScshuxCms\Dacang\Model\PointStoresReportItemModel','s')
		->leftJoin('ScshuxCms\Dacang\Model\PointStoresReportItemDetailModel','d.report_id=s.report_id and d.quota_id=s.quota_id and d.storestime=s.storestime','d')
		->leftJoin('ScshuxCms\Dacang\Model\StoreQuotaModel','q.id = s.quota_id','q')
		->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','s.report_user_id = u.id','u')
		->leftJoin('ScshuxCms\Dacang\Model\QuotaCommentModel','qc.qid = q.id and qc.user_id='.$userId,'qc')
		->where($where)
		->groupBy('q.id')
		->orderBy('s.id asc')
		->limit($pagesize,$offset)
		->getQuery()->execute()->toArray();
	
		return $return ;
	}
	
	
	/**
	 *
	 * @desc	根据报表id  计算此报表的总分
	 * @param	int $reportID
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
		$reportstoresinfo = PointStoresReportItemModel::findFirst($sid);
		if (!$reportstoresinfo)
		{
			return  $return ;
		}
		$storestime = $reportstoresinfo->storestime ;
		$where = 'd.report_id = '.$reportId .' and d.storestime = '.$storestime.' and i.storestime='.$storestime;
		$columns='d.id,d.point,q.type,i.quota_value,d.quota_id';
		$items = self::factory()->getModelsManager()->createBuilder()->columns($columns)
		->addFrom('ScshuxCms\Dacang\Model\PointStoresReportItemDetailModel','d')
		->leftJoin('ScshuxCms\Dacang\Model\StoreQuotaModel','d.quota_id=q.id','q')
		->leftJoin('ScshuxCms\Dacang\Model\PointStoresReportItemModel','i.quota_id=d.quota_id and d.report_id=i.report_id','i')
		->where($where)
		->groupBy('d.id')
		->getQuery()
		->execute();
		if($items)
		{
			$return = Utils::PointTotalScore($items);
		}
	
		return $return;
	}
}