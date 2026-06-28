<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Core;
class  Seo
{
	protected   static  $config = array();

	/**
	 * 在view里为ecbuilder页面调整title、keywords、description
	 * @param  $config array('title'=>'','keywords'=>'','description'=>'')
	 */
	public static function set($title = '',$keywords='',$description='')
	{
		self::$config = array('title'=>$title,'keywords'=>$keywords,'description'=>$description);
	}
	
	/**
	 * 处理seo
	 */
	public static  function  handle($html=null)
	{
		if(empty($html) || empty(self::$config)){
			return $html;
		}
		$config = self::$config;
		preg_match("!<head>(.*?)</head>!ius",$html,$m);
	
		//如果页面本来就没有head头，则直接返回
		if(!isset($m[0]) || $m[0]=="")
			return $html;
	
		$head = $m[1];
		if(isset($config['title']))
		{
			$title = "<title>{$config['title']}</title>";
			if(preg_match('!<title>.*?</title>!',$head))
			{
				$head = preg_replace("!<title>.*?</title>!ui",$title,$head,1);
			}
			else
			{
				$head .= "\n".$title;
			}
		}
	
		if(isset($config['keywords']))
		{
			$keywords = "<meta name=\"keywords\" content=\"{$config['keywords']}\">";
			if(preg_match("!<meta\s.*?name=['\"]keywords!ui",$head))
			{
				$head = preg_replace("!<meta\s.*?name=['\"]keywords.*?/?>!ui",$keywords,$head,1);
			}
			else
			{
				$head .= "\n".$keywords;
			}
		}
	
		if(isset($config['description']))
		{
			$description = "<meta name=\"description\" content=\"{$config['description']}\">";
			if(preg_match("!<meta\s.*?name=['\"]description!ui",$head))
			{
				$head = preg_replace("!<meta\s.*?name=['\"]description.*?/?>!ui",$description,$head,1);
			}
			else
			{
				$head .= "\n".$description;
			}
		}
	
		$head = "<head>{$head}</head>";
		$html = preg_replace("!<head>(.*?)</head>!ius",$head,$html);
		return $html;
	}
	
}