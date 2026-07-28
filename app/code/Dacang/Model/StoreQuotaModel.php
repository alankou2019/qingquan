<?php
/**
 * 归档指标
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
class StoreQuotaModel extends BaseModel
{

	protected static  $_instance=null;

	public function initialize()
	{
		$this->setSource($this->getTableName("store_quota"));
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\QuotaModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new StoreQuotaModel();
		}
		return self::$_instance;
	}
}