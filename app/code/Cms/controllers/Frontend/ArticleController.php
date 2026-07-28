<?php
/**
 * 文章
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Cms\Model\ArticleModel;
use ScshuxCms\Cms\Model\ArticlecategoryModel;
use ScshuxCms\Common\Helper\PageHelper;
use ScshuxCms\Cms\Model\PageModel;
class ArticleController extends FrontendBaseController
{
	
	/**
	 * 文章列表
	 */
	public  function indexAction()
	{
		
		$catid = intval($_REQUEST['cat_id']);
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		
		if(empty($catid)){
			$this->redirect('/');
		}
		$articleCategory = ArticlecategoryModel::findFirst('id='.$catid);
		if(empty($articleCategory)){
			$this->redirect('/');
		}
		$this->view->setVar('articleCategory', $articleCategory);
		
		//读取该分类下的文章
		$articleCategories =ArticlecategoryModel::find(array(
				'order' => 'id desc',
				'limit' => 8
		));
		$this->view->setVar('articleCategories', $articleCategories);
		$this->setSeo($articleCategory->name);
		
		//生产查询构建对象
		$pagesize =  15;
		$skip = ($page-1) * $pagesize;
		$queryBuilder = PageModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Cms\Model\ArticleModel','a')
		->leftJoin('\ScshuxCms\Cms\Model\ArticlecategoryModel','r.id=a.cat_id','r')
		->andWhere("a.cat_id='{$catid}'");
		
		$countInfo = $queryBuilder->columns('count(*) as num')
		->getQuery()
		->execute();
		
		$items = $queryBuilder->columns('a.id,a.title,a.pic,a.cat_id,r.name as cat_name,a.created')
		->orderBy(['a.sort desc','a.id desc'])
		->limit($pagesize,$skip)
		->getQuery()
		->execute();
		
		$this->view->setVar('items', $items);
		
	}
	
	/**
	 * 文章详情
	 */
	public  function showAction()
	{
		//读取
		$id = intval($_REQUEST['id']);
		if(empty($id)){
			$this->redirect('/');
		}
		$articleItem = ArticleModel::findFirst('id='.$id);
		if(empty($articleItem)){
			$this->redirect('/');
		}
		//修改预览次数
		$articleItem->saveData(array(
				'click' => $articleItem->click+1
		));
		$articleItem->created = $this->getHelper()->getTime()->localDate('Y-m-d',$articleItem->created);
		$this->view->setVar('item', $articleItem);
		
		//读取分类
		$articleCategory = ArticlecategoryModel::findFirst('id='.intval($articleItem->cat_id));
		$this->view->setVar('articlecate', $articleCategory);
		
		//读取该分类下的文章
		$articleCategories =ArticlecategoryModel::find(array(
				'order' => 'id desc',
				'limit' => 8
		));
		$this->view->setVar('articleCategories', $articleCategories);
		
		$this->setSeo($articleItem->title);
		
		
	}
	
}