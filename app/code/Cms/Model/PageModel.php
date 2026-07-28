<?php
/**
 * 单页列表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Cms\Model;
use ScshuxCms\Core\Model\BaseModel;
class PageModel extends BaseModel
{
	protected static  $_instance=null;

	public function initialize()
	{
		$this->setSource($this->getTableName("page"));
	}

	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Cms\Model\PageModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new PageModel();
		}
		return self::$_instance;
	}

}
