<?php
/**
 * 广告位
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Advert\Model;
use ScshuxCms\Core\Model\BaseModel;
class AdvertPositionModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("ad_position"));
	}
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Advert\Model\AdvertPositionModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new AdvertPositionModel();
		}
		return self::$_instance;
	}
}
