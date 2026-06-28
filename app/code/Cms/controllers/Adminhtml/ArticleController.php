<?php
/**
 * 文章列表管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Cms\Model\ArticlecategoryModel;

use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Cms\Model\ArticleModel;
use ScshuxCms\Core\Helper\Utils;
class  ArticleController extends AdminBaseController
{
	/**
	 * Index action
	 */
	public  function  indexAction()
	{
		$act = isset($_REQUEST['act'])?$_REQUEST['act']:'';
		$isAjax = isset($_REQUEST['is_ajax'])?$_REQUEST['is_ajax']:false;

		if($act == 'remove'){
			$this->_remove($_REQUEST['id']);
		}

		$dataList = $this->_getDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('article','index');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
		//文章分类
		$categorys = ArticlecategoryModel::getCat();
		$categorys = toLevel($categorys);
		$this->view->setVar('categorys', $categorys);
	}

	/**
	 * Create  action
	 */
	public function   newAction()
	{
		$this->dispatcher->forward(
				[
						"controller" => "article",
						"action" => "edit"
				]);
	}

	/**
	 * Edit action
	 */
	public function editAction()
	{
		$itemId = isset($_REQUEST['id'])?intval($_REQUEST['id']):'';
		if($itemId>0){
			$item = ArticleModel::factory()->findFirst('id='.$itemId);
			if(empty($item))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
			$this->view->setVar('item', $item);
		}
		//文章分类
		$categorys = ArticlecategoryModel::getCat();
		$categorys = toLevel($categorys);
		$this->view->setVar('categorys', $categorys);
	}

	/**
	 * Save action
	 */
	public function saveAction()
	{
		$backUrl = $this->getHelper()->createUrl(array('p'=>'article/index'));
		if($this->request->isPost())
		{
			$postData = $_POST;

			if(empty($postData['title']) || empty($postData['cat_id']))
			{
				Utils::showMsg('请求的参数不正确!',$backUrl);
			}
			//上传图片
			if($_FILES['pic']["name"])
			{
				 $filename = Utils::uploadFile('pic','article');
				 $postData['pic'] = $filename;
			}
			if($postData['flag']){
				$postData['flag'] = join(',', $postData['flag']);
			}
			if(empty($postData['id'])){
				unset($postData['id']);
				$postData['created'] = $this->getHelper()->getTime()->gmtime();
				$result = ArticleModel::factory()->save($postData);
			}else{
				$item = ArticleModel::factory()->findFirst('id='.intval($postData['id']));
				if(empty($item))
				{
					Utils::showMsg('修改的记录不存在!',$backUrl);
				}
				$result = $item->save($postData);

			}
			if($result){
				Utils::showMsg('操作成功!',$backUrl);
			}else{
				Utils::showMsg('操作失败!',$backUrl);
			}

		}else
		{
			Utils::showMsg('不支持的请求方式!',$backUrl);
		}
	}


	/**
	 * Get data list
	 */
	protected  function _getDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$filter = array();
		$where = '1=1';
		if($_REQUEST['cat_id'])
		{
			$where .= " AND a.cat_id = ".intval(trim($_REQUEST['cat_id']));
			$filter['cat_id'] = $_REQUEST['cat_id'];
		}
		if($_REQUEST['title'])
		{
			$where .= " AND a.title LIKE '%".trim($_REQUEST['title'])."%'";
			$filter['title'] = $_REQUEST['title'];
		}
		$dataList = new \stdClass();

		//生产查询构建对象
		$queryBuilder = ArticleModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Cms\Model\ArticleModel','a')
		->leftJoin('\ScshuxCms\Cms\Model\ArticlecategoryModel','r.id=a.cat_id','r')
		->andWhere($where);

		//统计
		$countInfo = $queryBuilder->columns('COUNT(*) as num')
		->getQuery()
		->execute();

		$dataList->count = $countInfo[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize = $pagesize;
		$dataList->pageCount= ceil($dataList->count/$dataList->pageSize);
		$dataList->filter = $filter;


		/*加载数据*/
		$offset = ($page-1)*$pagesize;
		$items = $queryBuilder->columns('a.id,a.title,a.cat_id,a.sort,a.created,a.url,a.flag,r.name as cat_name')
						->orderBy('a.id desc')
						->limit($pagesize,$offset)
						->getQuery()
						->execute();

		$dataList->items = $items;
		return $dataList;

	}

	/**
	 * 删除数据
	 * @param  $ids
	 */
	protected  function  _remove($ids)
	{
		if($ids){
			$items = ArticleModel::factory()->find('id in('.$ids.')');
			foreach ($items as $item){
				$item->delete();
			}
		}
	}

}