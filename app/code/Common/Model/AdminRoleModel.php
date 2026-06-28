<?php
/**
 * 用户权限表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Common\Model;
use ScshuxCms\Core\Model\BaseModel;
class AdminRoleModel extends BaseModel
{

	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("admin_role");
	}

	/**
	 * 返回实例
	 * @return \ScshuxCms\Common\Model\AdminRoleModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new AdminRoleModel();
		}
		return self::$_instance;
	}



}