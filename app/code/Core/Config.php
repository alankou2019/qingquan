<?php
/**
 * 配置处理文件
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Core;
class  Config
{

	/**
	 * 获取系统配置
	 * @return \Phalcon\Config
	 */
	public static   function get()
	{
		$configFile = APPROOT.'/etc/config.xml';
		if(!file_exists($configFile)){
			  exit('Please config appliction!');
		}
		$configArr = xmlFileToArr($configFile);

		$configArr['controllerFiles'] = array();
		$configArr['defaultControllerNameSpace'] = '';

		return  new \Phalcon\Config($configArr);
	}


}