<?php
/**
 * 系统配置表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Common\Model;
use ScshuxCms\Core\Model\BaseModel;
class ConfigModel extends BaseModel
{
	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("config");
	}
	
	/**
	 * 获取系统配置组
	 */
	public  function getConfigGroup()
	{
		return  array(
				1=>'基本设定',
				2=>'高级设定',
				3=>'邮件配置',
				4=>'钉钉配置',
				5=>'联系信息'
		);
	}
	
	/**
	 * 返回实例
	 * @return \ScshuxCms\Common\Model\ConfigModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ConfigModel();
		}
		return self::$_instance;
	}



}