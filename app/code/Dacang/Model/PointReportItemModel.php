<?php
/**
 * 积分考评表具体指标
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use Phalcon\Di\FactoryDefault;
class  PointReportItemModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function getSource()
	{	
		return $this->getTableName("point_report_item");
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new PointReportItemModel();
		}
		return self::$_instance;
	}
	
	
	
	/**
	 * 
	 * @desc	情况报表的打分信息	
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
		$items = ReportItemModel::find('report_id = '.$reportId);
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
	 * @desc	根据用户id  获取需要该用户评分的用户列表
	 * @param	int  $userId	
	 * @return	obj
	 * @date	2017年5月3日
	 */
	public function getNeedPointList($userId,$page,$pagesize)
	{
		$return = new \stdClass() ;
		if(!$userId)
		{
			return  $return ;
		}
		$userId = intval($userId) ;
		$where = "i.report_user_id = ".$userId." and ru.state = 0 and r.ispoint = 1" ;
		$offset = ($page-1) * $pagesize ;
		
		//以需要当前用户评分的用户进行分组
		$return = self::factory()->getModelsManager()->createBuilder()
													->columns('u.id,r.created,r.id as reportId,u.name,u.avatar,d.name as dname,r.name as rname')
													->addFrom('ScshuxCms\Dacang\Model\PointReportItemModel','i')
													->leftJoin('ScshuxCms\Dacang\Model\PointReportModel','r.id = i.report_id','r')
													->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.user_id = u.id','u')
													->leftJoin('ScshuxCms\Dacang\Model\DepartmentModel','d.dingding_id = u.department_id','d')
													->leftJoin('ScshuxCms\Dacang\Model\PointReportUserModel','i.report_id = ru.report_id','ru')
													->groupBy('i.report_id')
													->where($where)
													->orderBy('r.id asc')
													->limit($pagesize,$offset)
													->getQuery()
													->execute() ;
		
		return $return ;
		
	}
	
	
	
	/**
	 * @desc	根据用户id  获取需要该用户评分的用户列表数量
	 * 积分考评可以一直评价  所以只要考评表是未完成状态    都需要统计
	 * @param	int  $userId
	 * @return	int
	 * @date	2017年5月3日
	 */
	public function getNeedPointListNum($userId)
	{
		$return = 0 ;
		if(!$userId)
		{
			return  $return ;
		}
		$userId = intval($userId) ;
		$where = "i.report_user_id = ".$userId." and ru.state = 0 and r.ispoint = 1" ;
	
		//以需要当前用户评分的用户进行分组
		$items = self::factory()->getModelsManager()->createBuilder()
													->columns('r.id as reportId')
													->addFrom('ScshuxCms\Dacang\Model\PointReportItemModel','i')
													->leftJoin('ScshuxCms\Dacang\Model\PointReportModel','r.id = i.report_id','r')
													->leftJoin('ScshuxCms\Dacang\Model\PointReportUserModel','i.report_id = ru.report_id','ru')
													->groupBy('i.report_id')
													->where($where)
													->getQuery()
													->execute() ;
	
		$return = $items->count() ;
		return $return ;
	
	}
	
	
	
	/**
	 * @desc	根据用户id  获取该用户已经评分的用户列表
	 * @param	int  $userId
	 * @return	obj
	 * @date	2017年5月3日
	 */
	public function getHasPointList($userId,$page,$pagesize)
	{
		$return = new \stdClass() ;
		if(!$userId)
		{
			return  $return ;
		}
		$userId = intval($userId) ;
		$where = "i.report_user_id = ".$userId." and i.report_time > 0 " ;
		$offset = ($page-1) * $pagesize ;
	
		//以需要当前用户评分的用户进行分组
		$return = ReportItemModel::getModelsManager()->createBuilder()
													->columns('u.id,i.report_time as created,r.id as reportId,u.name,u.avatar,d.name as dname,r.name as rname')
													->addFrom('ScshuxCms\Dacang\Model\ReportItemModel','i')
													->leftJoin('ScshuxCms\Dacang\Model\ReportModel','r.id = i.report_id','r')
													->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.user_id = u.id','u')
													->leftJoin('ScshuxCms\Dacang\Model\DepartmentModel','d.dingding_id = u.department_id','d')
													->groupBy('i.report_id')
													->where($where)
													->orderBy('r.id asc')
													->limit($pagesize,$offset)
													->getQuery()
													->execute() ;
		return $return ;
	
	}
	
	
	
	
	/**
	 * @desc	根据报表id  被考核人id	 获取需要评分的具体指标
	 * @param	int  $reportId		报表id	
	 * @param	int  $userid		 被考核人id
	 * @param 	itn	 $uid			考核人id
	 * @return	obj
	 * @date	2017年5月3日
	 */
	public function getNeedPointDetail($reportId, $userid, $uid)
	{
		$return = new \stdClass() ;
		if(!$reportId || !$userid)
		{
			return  $return ;
		}
		$reportId = intval($reportId) ;
		$userid   = intval($userid) ;
		$uid      = intval($uid) ;
		
		
		$where = "i.report_user_id = ".$uid." and i.report_id = ".$reportId.' and i.user_id = '.$userid ;
		$offset = ($page-1) * $pagesize ;
		$columns= 'q.name as qname,q.remark,q.type,q.point_desc,i.quota_value,i.id,i.quota_id,qc.id as qc_id,qc.content as qc_comment,'.
					'sum(if(id.status=1,id.point,0)) as report_point ';
	
		$return = self::factory()->getModelsManager()->createBuilder()->columns($columns)
													->addFrom('ScshuxCms\Dacang\Model\PointReportItemModel','i')
													->leftJoin('ScshuxCms\Dacang\Model\QuotaModel','q.id = i.quota_id','q')
													->leftJoin('ScshuxCms\Dacang\Model\QuotaCommentModel','qc.qid = q.id and qc.user_id='.$userid,'qc')
													->leftJoin('ScshuxCms\Dacang\Model\PointReportItemDetailModel','id.report_id=i.report_id and id.quota_id=i.quota_id','id')
													->groupBy('q.id')
													->where($where)
													->orderBy('i.id asc')
													->limit($pagesize,$offset)
													->getQuery()
													->execute();
		return $return ;
	
	}
	
	
	
	/**
	 * @desc	根据报表id  被考核人id	 获取已经评分的具体指标
	 * @param	int  $reportId		报表id
	 * @param	int  $userid		 被考核人id
	 * @param 	itn	 $uid			 考核人id
	 * @return	obj
	 * @date	2017年5月3日
	 */
	public function getHasPointDetail($reportId, $userid, $uid)
	{
		$return = new \stdClass() ;
		if(!$reportId || !$userid)
		{
			return  $return ;
		}
		$reportId = intval($reportId) ;
		$userid   = intval($userid) ;
		$uid      = intval($uid) ;
	
	
		$where = "i.report_user_id = ".$uid." and i.report_time != 0 and i.report_id = ".$reportId.' and i.user_id = '.$userid ;
		$offset = ($page-1) * $pagesize ;
	
		$columns= 'q.name as qname,q.remark,q.type,q.point_desc,i.quota_value,i.id,i.quota_id,i.report_time,i.report_point,'.
				'qc.id as qc_id,qc.content as qc_comment';
		
		$return = ReportItemModel::getModelsManager()->createBuilder()
													->columns($columns)
													->addFrom('ScshuxCms\Dacang\Model\ReportItemModel','i')
													->leftJoin('ScshuxCms\Dacang\Model\QuotaModel','q.id = i.quota_id','q')
													->leftJoin('ScshuxCms\Dacang\Model\QuotaCommentModel','qc.qid = q.id and qc.user_id='.$userid,'qc')
													->groupBy('q.id')
													->where($where)
													->orderBy('i.id asc')
													->limit($pagesize,$offset)
													->getQuery()
													->execute();
		
		
		return $return ;
	
	}
	
	
	
	
	
	/**
	 * 
	 * @desc	根据报表id 被考核人id  判断针对此考核人的考评 是否已经考核完成
	 * @param	int  $reportId
	 * @param	int  $uid
	 * @return	bool
	 * @date	2017年5月3日
	 */
	public function isOver($reportId,$uid)
	{
		$return = true ;
		if(!$reportId || !$uid)
		{
			return $return ;
		}
		
		$reportId = intval($reportId) ;
		$uid      = intval($uid) ;
		
		
		$where = "i.report_id = ".$reportId.' and i.user_id = '.$uid ;
		$offset = ($page-1) * $pagesize ;
		
		$items = ReportItemModel::getModelsManager()->createBuilder()
													->columns('i.report_time,i.report_point')
													->addFrom('ScshuxCms\Dacang\Model\ReportItemModel','i')
													->where($where)
													->orderBy('i.id asc')
													->limit($pagesize,$offset)
													->getQuery()
													->execute() ;
		
		if($items)
		{
			foreach ($items as $item)
			{
				if(!$item->report_time || !$item->report_point)
				{
					$return = false ;
				}
			}
		}
		
		return $return ;
	}
	
	
	
	/**
	 * 
	 * @desc	通过考核表id  获取参与此考核表打分的公司人员的钉钉id
	 * @param	$reportId 		
	 * @return	str		
	 * @date	2017年5月9日
	 */
	public function getDdUserid($reportId)
	{
		$return = '';
		if(!$reportId)
		{
			return $return ;
		}
		$reportId = intval($reportId) ;
		$where = 'i.report_id = '.$reportId;
		$items = ReportItemModel::factory()->getModelsManager()->createBuilder()
										->columns('u.dingding_user_id as dduserid')
										->addFrom('ScshuxCms\Dacang\Model\PointReportItemModel','i')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.report_user_id = u.id','u')
										->where($where)
										->getQuery()
										->execute() ;
		if($items)
		{
			$arr = array() ;
			foreach ($items as $item)
			{
				$arr[] = $item->dduserid ;
			}
				
			$return = join('|',array_unique($arr));
		}
		return $return ;
	}
	
	
	
	/**
	 *
	 * @desc	通过考核表id  获取被考核人的姓名
	 * @param	$reportId
	 * @return	str
	 * @date	2017年5月9日
	 */
	public function getReportUserName($reportId)
	{
		$return = '';
		if(!$reportId)
		{
			return $return ;
		}
		$reportId = intval($reportId) ;
		$where = 'i.report_id = '.$reportId;
		$items = ReportItemModel::factory()->getModelsManager()->createBuilder()
											->columns('u.name')
											->addFrom('ScshuxCms\Dacang\Model\PointReportItemModel','i')
											->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.user_id = u.id','u')
											->where($where)
											->limit(1)
											->getQuery()
											->execute() ;
		
		if($items)
		{
			$return = $items[0]->name ;
		}
		return $return ;
	}
	
	
	
	/**
	 * 
	 * @desc	根据用户Id  获取当前正在对用户进行评分的考核表  
	 * @param	$uid		
	 * @return	obj	
	 * @date	2017年5月10日
	 */
	public function getReportIngList($uid,$page,$pagesize)
	{
		$return = new \stdClass() ;
		if(!$uid)
		{
			return  $return ;
		}
		$uid = intval($uid) ;
		$where = "i.user_id = ".$uid.' and r.ispoint = 1 ' ;
		$offset = ($page-1) * $pagesize ;
		
		//以需要当前用户评分的用户进行分组
		$return = self::factory()->getModelsManager()->createBuilder()
												->columns('u.id,r.created,r.id as reportId,u.name,u.avatar,r.name as rname')
												->addFrom('ScshuxCms\Dacang\Model\PointReportItemModel','i')
												->leftJoin('ScshuxCms\Dacang\Model\PointReportModel','r.id = i.report_id','r')
												->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.user_id = u.id','u')
												->groupBy('i.report_id')
												->where($where)
												->orderBy('r.id asc')
												->limit($pagesize,$offset)
												->getQuery()
												->execute() ;
		
		return $return ;
	}
	
	
	
	
	/**
	 *
	 * @desc	根据用户Id  获取当前正在对用户进行评分的考核表数量
	 * @param	$uid
	 * @return	int
	 * @date	2017年5月10日
	 */
	public function getReportIngListNum($uid)
	{
		$return = 0 ;
		if(!$uid)
		{
			return  $return ;
		}
		$uid = intval($uid) ;
		$where = "i.user_id = ".$uid.' and r.ispoint = 1 ' ;
	
		//以需要当前用户评分的用户进行分组
		$items = self::factory()->getModelsManager()->createBuilder()
												->columns('r.id as reportId')
												->addFrom('ScshuxCms\Dacang\Model\PointReportItemModel','i')
												->leftJoin('ScshuxCms\Dacang\Model\PointReportModel','r.id = i.report_id','r')
												->groupBy('i.report_id')
												->where($where)
												->getQuery()
												->execute() ;
		$return = $items->count();
		return $return ;
	}
	
	
	/**
	 *
	 * @desc	根据报表id  获取当前用户已经进行打分的详情
	 * @param	$reportId
	 * @param	$uid
	 * @return	obj
	 * @date	2017年5月10日
	 */
	public function getIntPointDetail($reportId,$uid)
	{
		$return = new \stdClass() ;
		if(!$uid || !$reportId)
		{
			return  $return ;
		}
		
		$reportId = intval($reportId) ;
		$uid = intval($uid) ;
		
		$where = "i.user_id = ".$uid." and i.report_id = ".$reportId ;
		$offset = ($page-1) * $pagesize ;
		$columns='q.id,q.name as qname,q.type,q.point_desc,i.report_id';
		
		$return = self::factory()->getModelsManager()->createBuilder()
										->columns($columns)
										->addFrom('ScshuxCms\Dacang\Model\PointReportItemModel','i')
										->leftJoin('ScshuxCms\Dacang\Model\QuotaModel','q.id = i.quota_id','q')
										->groupBy('q.id')
										->where($where)
										->orderBy('q.id asc')
										->limit($pagesize,$offset)
										->getQuery()
										->execute()->toArray();
		
		//计算分数
		if ($return)
		{
			foreach ($return as $key=>$item)
			{
				$owhere='status=1 and report_id='.$item['report_id'].' and quota_id='.$item['id'];
				$tempinfo=self::factory()->getModelsManager()->createBuilder()
									->columns('sum(point) as report_point')
									->addFrom('ScshuxCms\Dacang\Model\PointReportItemDetailModel')
									->where($owhere)
									->getQuery()
									->execute()->toArray();
				$item['report_point']=current($tempinfo)['report_point'];
				$return[$key]=$item;
			}
		}
		
		return $return ;
	}
	
	
	/**
	 *
	 * @desc	根据报表id  获取指标的集合
	 * @param	$reportid
	 * @return	return array
	 * @date	2017年5月17日
	 */
	public function getQuotaIds($reportid)
	{
		$return = array();
		if(!$reportid)
		{
			return $return ;
		}
		$reportid = intval($reportid) ;
		$where = 'report_id = '.$reportid;
	
		$items = ReportItemModel::getModelsManager()->createBuilder()
										->columns('group_concat(distinct(quota_id)) as quotaids')
										->addFrom('ScshuxCms\Dacang\Model\ReportItemModel')
										->groupBy('report_id')
										->where($where)
										->getQuery()
										->execute();
		if(count($items) > 0)
		{
			$return = explode(',', $items[0]->quotaids) ;
		}
	
		return $return ;
	}
	
	
	
	/**
	 * 
	 * @desc	根据报表id  指标id  获取参与此指标评分的评分人id集合
	 * @param	$reportid		
	 * @param	$quotaid		
	 * @return	return array	
	 * @date	2017年5月17日
	 */
	public function getReportUserIds($reportid,$quotaid)
	{
		$return = array() ;
		if(!$reportid || !$quotaid)
		{
			return $return ;
		}
		
		$reportid = intval($reportid) ;
		$quotaid = intval($quotaid) ;
		
		$where = 'report_id = '.$reportid.' and quota_id = '.$quotaid;
		
		$items = ReportItemModel::getModelsManager()->createBuilder()
										->columns('group_concat(distinct(report_user_id)) as reportuserids')
										->addFrom('ScshuxCms\Dacang\Model\ReportItemModel')
										->groupBy('quota_id')
										->where($where)
										->getQuery()
										->execute();

		if(count($items) > 0)
		{
			$return = explode(',', $items[0]->reportuserids) ;
		}
		return $return ;
		
		
	}
	
	
	
	/**
	 * 
	 * @desc	根据条件删除数据
	 * @param	$where
	 * @return	bool
	 * @date	2017年5月17日
	 */
	public static  function deleteAll($where)
	{
		if(!$where)
		{
			return false ;
		}
		
		$where = trim($where) ;
		$table = self::factory()->getSource();
		$sql = 'delete from '.$table.' where '.$where;
		
		try {
			$res = FactoryDefault::getDefault()->getdb()->query($sql);
			if($res)
			{
				$return = true ;
			}
				
		}catch (\Exception $e){
			$return = false ;
		}
	}
	
	/**
	 * @desc	撤销考评
	 * @param	$reportId
	 * @return
	 */
	public function comeback($reportId)
	{
		if (!$reportId)
		{
			return false;
		}
		$reportId=intval($reportId);
		$where='report_id='.$reportId;
		$data=array(
				'report_time' =>0,
				'report_point'=>0,
		);
		return  self::factory()->updateBySql($where, $data);
	}
	
	
	
	/**
	 * @desc	获取未完成的报表的  参与考评的用户 的相信信息
	 * @param	$reportId		
	 * @return			
	 */
	public function getReportUserDesc($reportId)
	{
		$return='';
		if (!$reportId || !is_numeric($reportId))
		{
			return $return;
		}
		$reportId=intval($reportId);
		$items=self::factory()->getModelsManager()->createBuilder()
							->columns('min(r.report_time) as reporttime,cu.name')
							->addFrom('ScshuxCms\Dacang\Model\ReportItemModel','r')
							->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','cu.id=r.report_user_id','cu')
							->where('r.report_id='.$reportId)
							->groupBy('r.report_user_id')
							->getQuery()
							->execute();
		if ($items)
		{
			foreach ($items as $val)
			{
				if (!$val->reporttime)
				{
					$return.='<span class="red">'.$val->name.'</span>&nbsp;';
					continue;
				}
				$return.='<span class="blue">'.$val->name.'</span>&nbsp;';
			}
		}
		return $return;
	}
	
	
	
	/**
	 *
	 * @desc	根据报表id  计算此报表的总分
	 * @param	int $reportID
	 * @return	float
	 * @date	2017年5月4日
	 */
	public function getTotalPoint($reportId)
	{
		$return = 0 ;
		if(!$reportId)
		{
			return $return ;
		}
		$reportId = intval($reportId) ;
		$where = 'd.report_id = '.$reportId.' and d.status=1';
		$columns='d.id,d.point,q.type,i.quota_value,d.quota_id';
		$items = self::factory()->getModelsManager()->createBuilder()->columns($columns)
		->addFrom('ScshuxCms\Dacang\Model\PointReportItemDetailModel','d')
		->leftJoin('ScshuxCms\Dacang\Model\QuotaModel','d.quota_id=q.id','q')
		->leftJoin('ScshuxCms\Dacang\Model\PointReportItemModel','i.quota_id=d.quota_id and d.report_id=i.report_id','i')
		->where($where)->groupBy('d.id')->getQuery()->execute();
		if($items)
		{
			$return = Utils::PointTotalScore($items);
		}
		return $return;
	}
}