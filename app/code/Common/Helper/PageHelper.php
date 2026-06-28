<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Common\Helper;
use ScshuxCms\Advert\Model\AdvertModel;
use ScshuxCms\Core\Helper;
use ScshuxCms\Common\Model\MenuModel;
use ScshuxCms\Cms\Model\PageModel;
class  PageHelper
{
	
	/**
	 * 获取指定位置广告
	 * @param string $keycode
	 * @param number $limit
	 */
	public  function getAd($keycode,$limit=6)
	{
		$nowtime = Helper::factory()->getTime()->gmtime();
		$items = AdvertModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Advert\Model\AdvertModel','a')
		->leftJoin('\ScshuxCms\Advert\Model\AdvertPositionModel','p.id=a.position_id','p')
		->andWhere("p.key_code='{$keycode}' and a.start_time <={$nowtime} and a.end_time >={$nowtime}")
		->columns('a.id,a.name,a.link,a.start_time,a.end_time,a.content,p.name as position_name')->limit($limit,0)
		->orderBy(["a.sort desc","a.id desc"])
		->getQuery()
		->execute();
		return $items;
	}
	
	
	/**
	 * 获取菜单
	 * @return 
	 */
	public function getMenus()
	{
		$menus = MenuModel::factory()->find(array(
				'order' => 'sort desc,id asc'
		))->toArray();
		return toLayer($menus);
	}
	
	
	/**
	 * 获取单页
	 */
	public function  getPageList($keycode='',$limit=10)
	{
		//生产查询构建对象
		$items = PageModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Cms\Model\PageModel','a')
		->leftJoin('\ScshuxCms\Cms\Model\PagecategoryModel','r.id=a.cat_id','r')
		->columns('a.id,a.name,a.cat_id,a.sort,a.created,r.name as cat_name,r.keycode,a.seo_keywords,a.pic,a.seo_description')
		->andWhere("r.keycode='{$keycode}'")
		->orderBy(['a.sort desc','a.id desc'])
		->limit($limit,0)
		->getQuery()
		->execute();
		return $items;
	}
	
	
	/**
	 * 获取单页
	 */
	public function  getArticleList($cat_id='',$page=1,$pagesize=15)
	{
		//生产查询构建对象
		$skip = ($page-1) * $pagesize;
		$items = PageModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Cms\Model\ArticleModel','a')
		->leftJoin('\ScshuxCms\Cms\Model\ArticlecategoryModel','r.id=a.cat_id','r')
		->columns('a.id,a.title,a.pic,a.cat_id,r.name as cat_name')
		->andWhere("a.cat_id='{$cat_id}'")
		->orderBy(['a.sort desc','a.id desc'])
		->limit($pagesize,$skip)
		->getQuery()
		->execute();
		return $items;
	}
	
}