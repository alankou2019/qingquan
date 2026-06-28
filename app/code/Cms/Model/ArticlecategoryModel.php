<?php
/**
 * 文章列表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Cms\Model;
use ScshuxCms\Core\Model\BaseModel;
class ArticlecategoryModel extends BaseModel
{
	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("article_category");
	}

	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Cms\Model\ArticlecategoryModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ArticlecategoryModel();
		}
		return self::$_instance;
	}

	/**
	 * 获取分类
	 */
	public static  function getCat()
	{
		$categorys = ArticlecategoryModel::query()
		->execute()
		->toArray();
		return toLevel($categorys);

	}

}
