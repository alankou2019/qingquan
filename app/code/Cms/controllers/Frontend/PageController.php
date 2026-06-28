<?php
/**
 * 单页
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Cms\Model\PageModel;
use ScshuxCms\Cms\Model\PagecategoryModel;
class PageController extends FrontendBaseController
{
	
	/**
	 * 单页展示
	 */
	public  function showAction()
	{
		//读取
		$id = intval($_REQUEST['id']);
		if(empty($id)){
			$this->redirect('/');
		}
		$pageItem = PageModel::findFirst('id='.$id);
		if(empty($pageItem)){
			$this->redirect('/');
		}
		//修改预览次数
		$pageItem->save(array(
				'click' => $pageItem->click+1
		));
		$pageItem->created = $this->getHelper()->getTime()->localDate('Y-m-d',$pageItem->created);
		$this->view->setVar('item', $pageItem);
		
		//读取分类
		$pageCategory = PagecategoryModel::findFirst('id='.intval($pageItem->cat_id));
		$this->view->setVar('pagecate', $pageCategory);
		
		//读取该分类下的文章
		$pageItems = PageModel::find(array(
				'cat_id'=>intval($pageItem->cat_id),
				'order' => 'id desc',
				'limit' => 8
		));
		$this->view->setVar('pageItems', $pageItems);
		
		
	}
	
}