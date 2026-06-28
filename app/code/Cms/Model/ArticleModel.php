<?php
/**
 * 文章
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Cms\Model;
use ScshuxCms\Core\Model\BaseModel;
class ArticleModel extends BaseModel
{
	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("article");
	}

	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Cms\Model\ArticleModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ArticleModel();
		}
		return self::$_instance;
	}

}
