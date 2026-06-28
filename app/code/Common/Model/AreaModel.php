<?php
/**
 * 地区表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Common\Model;
use ScshuxCms\Core\Model\BaseModel;
class AreaModel extends BaseModel
{
	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("area");
	}

	/**
	 * 返回实例
	 * @return \ScshuxCms\Common\Model\AreaModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new AreaModel();
		}
		return self::$_instance;
	}

	/**
	 * 查询地区
	 * @param  $id
	 */
	public static function getData($id)
	{
		$data =  self::factory()->findFirst('id='.intval($id));
		return $data->name;
	}
}