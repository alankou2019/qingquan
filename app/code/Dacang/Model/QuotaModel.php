<?php
/**
 * 指标
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
class QuotaModel extends BaseModel
{

	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("quota");
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\QuotaModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new QuotaModel();
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
	 * @desc	判断指标类型
	 * @param	$quotaId		
	 * @return			
	 */
	public function quotaType($quotaId)
	{
		if (!$quotaId)
		{
			return false;
		}
		$quotaId=intval($quotaId);
		$quotaInfo = self::factory()->findFirst($quotaId);
		if (!$quotaInfo)
		{
			return false;
		}
		return $quotaInfo->type;
	}
	
	
	
	/**
	 * @desc	获得每种类型的指标 能够进行评分的最大值
	 * @param			
	 * @return			
	 */
	public function getQuotaMaxPoint($pointType)
	{
		$return=array(
				'1'=>'200',
				'2'=>'20',
				'3'=>'99999',
				'4'=>'99999',
				'5'=>'10',
		);
		
		if ($pointType && is_numeric($pointType))
		{
			return $return[$pointType];
		}
		return $return;
	}

}