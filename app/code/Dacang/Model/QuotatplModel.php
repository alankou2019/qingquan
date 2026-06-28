<?php
/**
 * 指标模版
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;

use ScshuxCms\Core\Model\BaseModel;

class QuotatplModel extends BaseModel
{

	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("quota_tpl");
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\QuotaModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new QuotatplModel();
		}
		return self::$_instance;
	}
}