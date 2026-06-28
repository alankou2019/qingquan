<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Core;
class  Constants 
{
	
	/**
	 * 系统配置缓存
	 * @var \stdClass
	 */
	const CACHE_SYSTEM_CONFIG = 'system_config';
	
	
	
	/**
	 * 
	 * @desc 是否钉钉管理员 
	 * @date 2017年4月20日
	 */
	public static function isAdmin()
	{
		return  array(
				'1' => '是',
				'0' => '不是'
		) ;
	}
	
	
	/**
	 * 
	 * @desc 是否是部门主管 
	 * @date 2017年4月20日
	 */
	public static function isLeader()
	{
		return array(
				'1' => '是',
				'0' => '不是'
		) ;
	}
	
	
	/**
	 * 
	 * @desc 报表完成状态 
	 * @date 2017年4月21日
	 */
	public static function reportStatus()
	{
		return array(
				'0' => '未完成',
				'1' => '已完成'
		) ;
	}
	
	
	/**
	 * 
	 * @desc	获取指标的打分方式	
	 * @date	2017年5月2日
	 */
	public static function getQuotaType()
	{
		return  array(
				'1' => '百分制',
				'2' => '十分制',
				'3' => '权重制',
				'4' => '加减分',
				'5' => '五分制',
		);
	}
	
	
	/**
	 * @desc	获取积分考评表  指标点评状态
	 * @param			
	 * @return			
	 */
	public static function getPointCommentStatus($status)
	{
		$arr=array(
				'0'=>'审核中',
				'1'=>'已通过'
		);
		if (is_numeric($status) && in_array($status, array_keys($arr)))
		{
			return $arr[$status];
		}
		return $arr;
	}
	
}