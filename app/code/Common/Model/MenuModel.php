<?php
/**
 * 菜單管理表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Common\Model;
use ScshuxCms\Core\Model\BaseModel;
class MenuModel extends BaseModel
{

	protected static  $_instance=null;

	public function initialize()
	{
		$this->setSource($this->getTableName("menus"));
	}


	/**
	 * 返回实例
	 * @return \ScshuxCms\Common\Model\MenuModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new MenuModel();
		}
		return self::$_instance;
	}



}