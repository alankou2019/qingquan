<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Helper;
use ScshuxCms\Core\Helper;

class Render
{
	/**
	 * @desc	首页前端渲染
	 * @param	$dataList		
	 * @return	string		
	 */
	public static function renderNeedIndex($dataList,$controller='bs')
	{
		if (!$dataList)
		{
			return '';
		}
		$return='';
		$controller=$controller?trim($controller):'bs';
		foreach ($dataList as $val)
		{
			$params = array('p'=>$controller.'/pointdetail','id'=>$val->reportId,'uid'=>$val->id);
			if (isset($val->reporttime) && intval($val->reporttime) > 0)
			{
				$params['state'] = 1;
			}
			$url=Helper::factory()->createUrl($params);
			$return.='
			<li>
			<a href="'.$url.'" class="clear">
			<img src="'.$val->avatar.'" class="header_img fl" alt="'.$val->name.'" onerror="this.src=\'/favicon.ico\'"/>
			<div class="fl user_msg">
			<div class="name">'.$val->name.$val->rname.'</div>
			<div>'.$val->dname.'</div>
			</div>
			<div class="fr time">'.Helper::factory()->formatDateTime($val->created,'Y.m.d').'</div>
			</a>
			</li>';
		}
		
		
		return $return;
	}
	
	/**
	 * @desc	首页前端渲染
	 * @param	$dataList
	 * @return	string
	 */
	public static function renderHasIndex($dataList)
	{
		if (!$dataList)
		{
			return '';
		}
		$return='';
		foreach ($dataList as $val)
		{
			$url=Helper::factory()->createUrl(array('p'=>'bs/pointdetail','id'=>$val->reportId,'uid'=>$val->id,'state'=>1));
			$return.='
			<li>
			<a href="'.$url.'" class="clear">
			<img src="'.$val->avatar.'" class="header_img fl" alt="'.$val->name.'" onerror="this.src=\'/favicon.ico\'"/>
			<div class="fl user_msg">
			<div class="name">'.$val->name.$val->rname.'</div>
			<div>'.$val->dname.'</div>
			</div>
			<div class="score_total">总分 '.Helper::factory()->del0($val->totalpoint).'</div>
			<div class="score_submit_stat">已提交分数'.intval($val->submitted_count).'人（共'.intval($val->total_count).'人）</div>
			<div class="fr time">
			'.Helper::factory()->formatDateTime($val->created,'Y.m.d').'
			</div>
			</a>
			</li>';
		}
	
	
		return $return;
	}
	
	
	/**
	 * @desc	首页前端渲染
	 * @param	$dataList
	 * @return	string
	 */
	public static function renderIngIndex($dataList)
	{
		if (!$dataList)
		{
			return '';
		}
		$return='';
		foreach ($dataList as $val)
		{
			$url=Helper::factory()->createUrl(array('p'=>'bs/reportingdetail','id'=>$val->reportId));
			$return.='
			<li>
			<a href="'.$url.'" class="clear">
			<div class="fl user_msg">
			<div style="padding-top:6px;font-size: 0.8rem;color: black;font-weight:bold;">'.$val->rname.'</div>
			</div>
			<div class="fr time">'.Helper::factory()->formatDateTime($val->created,'Y.m.d').'</div>
			</a>
			</li>';
		}
	
	
		return $return;
	}
	
	
	/**
	 * @desc	审核进度页面渲染
	 * @param			
	 * @return			
	 */
	public static function renderCheckSpeed($dataList)
	{
		if (!$dataList)
		{
			return '';
		}
		$return='';
		foreach ($dataList as $val)
		{
			$status=$val['checkstatus']=='y'?'已审':'未审核';
			$return.='<li class="clear">
			            <div>
			                <span class="bspointspanwidth">审核人：</span>
			                <span>'.$val['username'].'</span>
			            </div>
			            <div>
			                <span class="bspointspanwidth">状态：</span>
			                <span class="statustext txt">'.$status.'</span>
			            </div>
					</li>';
		}
		return $return;
	}
	
}
