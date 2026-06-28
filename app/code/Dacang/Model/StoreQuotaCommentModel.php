<?php
/**
 * 指标点评
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Helper;
class StoreQuotaCommentModel extends BaseModel
{

	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("store_quota_comment");
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\QuotaModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new StoreQuotaCommentModel();
		}
		return self::$_instance;
	}
	
	
	
	/**
	 * @desc	删除实例  循环添加的时候使用
	 * @param			
	 * @return			
	 */
	public static function delFactory()
	{
		self::$_instance = null ;
	}
	
	
	/**
	 * @desc	根据where条件获取指标点评
	 * @param	$where		
	 * @return			
	 */
	public function getComment($where,$page,$pageSize)
	{
		$return = array();
		if (!$where)
		{
			return $return;
		}
		$limit = ($page-1)*$pageSize;
		$items = self::factory()->getModelsManager()->createBuilder()
						->from('ScshuxCms\Dacang\Model\QuotaCommentModel')
						->where($where)
// 						->limit($limit,$pageSize)
						->getQuery()
						->execute();
		
		return $items;
	}
	
	
	
	/**
	 * @desc	指标点评归档
	 * @param	$reportId	报表id
	 * @param	$time		归档时间
	 * @return
	 */
	public function saveComment($reportId,$time)
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
		//拼接	sql
		$sql = "insert into ".self::getSource()." (rid,qid,user_id,content,created_at,storestime) " ;
		$sql.= "select rid,qid,user_id,content,created_at,{$time}  FROM ".self::getTableName('quota_comment')." where rid = ".$reportId ;
	
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
	 * @desc	清空点评
	 * @param	$reportId		
	 * @return			
	 */
	public function clearComment($reportId)
	{
		if (!$reportId)
		{
			return false;
		}
		$reportId=intval($reportId);
		return self::factory()->deleteBySql('rid='.$reportId);
	}
	
	/**
	 * @desc	判断用户是否已经对指标进行点评
	 * @param	$userId		
	 * @param	$quotaId		
	 * @return	boolean		
	 */
	public function isExists($userId,$quotaId)
	{
		$return=true;
		if (!$userId || !$quotaId)
		{
			return $return;
		}
		$userId=intval($userId);
		$quotaId=intval($quotaId);
		
		$item = self::factory()->findFirst('qid='.$quotaId.' and user_id='.$userId);
		if (!$item)
		{
			$return=false;
		}
		return $return;
	}
	
	/**
	 * @desc	渲染指标 点评数据
	 * @param			
	 * @return			
	 */
	public function renderComment($data)
	{
		if (!$data)
		{
			return  '';
		}
		$str = '';
		foreach ($data as $v)
		{
			$str.='<li class="clear"><div class="fl text">'.$v->content.'</div>
			<div class="fr time">'.Helper::factory()->getTime()->localDate('Y.m.d',$v->created_at).'</div></li>';
		}
		
		return $str;
	}
	
	
}