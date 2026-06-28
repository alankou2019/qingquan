<?php
/**
 * 单页分类
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Cms\Model;
use ScshuxCms\Core\Model\BaseModel;
class PagecategoryModel extends BaseModel
{
	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("page_category");
	}

	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Cms\Model\PagecategoryModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new PagecategoryModel();
		}
		return self::$_instance;
	}

}
