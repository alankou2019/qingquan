<?php
/**
 * 接口权限表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Common\Model;
use ScshuxCms\Core\Model\BaseModel;
class AppModel extends BaseModel
{

	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("app");
	}
	
	
	/**
	 * 加载数
	 * @param string $appkey
	 * @return \ScshuxCms\Common\Model\AppModel
	 */
	public function  loadByAppKey($appkey='')
	{
		return AppModel::findFirst("app_key='{$appkey}'");
	}

	
	/**
	 * 返回实例
	 * @return \ScshuxCms\Common\Model\AppModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new AppModel();
		}
		return self::$_instance;
	}



}