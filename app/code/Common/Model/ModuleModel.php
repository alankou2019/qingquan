<?php
/**
 * 系统模组
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Common\Model;
use ScshuxCms\Core\Model\BaseModel;
class ModuleModel extends BaseModel
{
	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("module");
	}
	
	/**
	 * 返回实例
	 * @return \ScshuxCms\Common\Model\ModuleModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ModuleModel();
		}
		return self::$_instance;
	}



}