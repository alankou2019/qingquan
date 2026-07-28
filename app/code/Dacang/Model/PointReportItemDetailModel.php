<?php
/**
 * 积分考评表 具体评分详情
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use Phalcon\Di\FactoryDefault;
class  PointReportItemDetailModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("point_report_item_detail"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new PointReportItemDetailModel();
		}
		return self::$_instance;
	}

	
	/**
	 * @desc	积分考评 指标评分记录
	 * @param	$reportId		报表id		
	 * @param	$quotaId		指标id		
	 * @return			
	 */
	public static function showPoint($reportId,$quotaId)
	{
		$return = new \stdClass();
		if(!$reportId || !$quotaId)
		{
			return $return ;
		}
		$offset = ($page-1)*$pagesize;
		$where = ' i.quota_id = '.intval($quotaId).' and i.report_id = '.intval($reportId);
		$dataList = new \stdClass();
		$count = self::factory()->modelsManager->createBuilder()
										->columns("i.id")
										->addFrom('ScshuxCms\Dacang\Model\PointReportItemDetailModel','i')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.user_id = u.id','u')
										->leftJoin('ScshuxCms\Dacang\Model\PointCheckModel','i.id=c.item_detail_id','c')
										->where($where)
										->groupBy('i.id')
										->getQuery()
										->execute() ;
		
		$items = self::factory()->modelsManager->createBuilder()
										->columns("i.id,i.created_at as report_time,i.point as report_point,u.name,i.reason,c.status")
										->addFrom('ScshuxCms\Dacang\Model\PointReportItemDetailModel','i')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.user_id = u.id','u')
										->leftJoin('ScshuxCms\Dacang\Model\PointCheckModel','i.id=c.item_detail_id','c')
										->where($where)
										->groupBy('i.id')
										->orderBy('u.id asc')
										->limit($pagesize,$offset)
										->getQuery()
										->execute() ;
		
		$dataList->count       = $count->count();
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		$dataList->items = $items;
		return  $dataList ;
	}
	
	
	/**
	 * @desc	清除打分信息	
	 * @param	$reportId		
	 * @return			
	 */
	public static function clearPoint($reportId)
	{
		$reportId=intval($reportId);
		$where='report_id='.$reportId;
		$return=self::factory()->deleteBySql($where);
		return $return;
	}
	
	
	
	/**
	 * @desc	点评记录
	 * @param	$where		
	 * @return			
	 */
	public static function getDatalist($where,$page,$pageSize)
	{
		$where=trim($where);
		$page =$page?intval($page):1;
		$pageSize=$pageSize?intval($pageSize):10;
		$offset=($page-1)*$pageSize;
		
		$columns='point,reason,created_at';
		$items=self::factory()->getModelsManager()->createBuilder()->columns($columns)
					->addFrom('ScshuxCms\Dacang\Model\PointReportItemDetailModel')
					->where($where)->getQuery()->execute()->toArray();
		return $items;
	}
	
	
	
	/**
	 * @desc	渲染历史记录
	 * @param	$dataList		
	 * @return			
	 */
	public static function renderDetail($dataList)
	{
		if (!$dataList)
		{
			return '';
		}
		$html='';
		foreach ($dataList as $val)
		{
			$html.='<li class="clear">
			            <div>
			                <span class="bspointspanwidth">评分缘由：</span>
			                <span>'.$val['reason'].'</span>
			            </div>
			            <div>
			                <span class="bspointspanwidth">分值：</span>
			                <span class="fr">'.$val['point'].'</span>		
			            </div>
                		<div>
			                <span class="bspointspanwidth">时间：</span>
			                <span class="fr">'.Helper::factory()->getTime()->localDate('Y-m-d H:i',$val['created_at']).'</span>
			            </div>
						
					</li>';
		}
		return $html;
	}
	
	
	/**
	 * @desc	根据积分记录id   获取被考评人的部门	
	 * @param	$id		
	 * @return			
	 */
	public static function getDepartById($id)
	{
		if (!$id)
		{
			return false;
		}
		$id=intval($id);
		
		$where='d.id='.$id;
		$columns='u.department_id,d.status';
		$item=self::factory()->getModelsManager()->createBuilder()->columns($columns)
				->addFrom('ScshuxCms\Dacang\Model\PointReportItemDetailModel','d')
				->leftJoin('ScshuxCms\Dacang\Model\PointReportItemModel','i.report_id=d.report_id','i')
				->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.user_id=u.id','u')
				->where($where)->groupBy('d.id')->getQuery()->execute()->toArray();
		if (!$item)
		{
			return false;
		}
		$item=current($item);
		//将钉钉的部门id  转换为我们系统的部门id
		$item['department_id']=CompanyDepartModel::adpterId($item['department_id']);
		return $item;
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
		return self::factory()->deleteBySql($where);
	}
	
	
	
	/**
	 * @desc	修改积分记录状态
	 * @param	$id
	 * @return
	 */
	public static function UpStatus($id)
	{
		if (!$id)
		{
			return false;
		}
		$obj=self::findFirst($id);
		$obj->status=1;
		return $obj->save();
	}
	
	
	/**
	 * @desc	删除积分记录
	 * @param	$id		
	 * @return			
	 */
	public static function delItem($id)
	{
		if (!$id)
		{
			return false;
		}
		$id=intval($id);
		$where='id='.$id;
		self::factory()->deleteBySql($where);
		
		//删除点评记录
		$checkWhere='item_detail_id='.$id;
		PointCheckModel::factory()->deleteBySql($checkWhere);
		
		return true;
	}
	
}