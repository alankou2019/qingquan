<?php
/**
 * 单页列表管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Cms\Model\PagecategoryModel;

use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Cms\Model\PageModel;
use ScshuxCms\Core\Helper\Utils;
class  PageController extends AdminBaseController
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
		//单页分类
		$categorys = PagecategoryModel::query()
		->orderBy("sort")
		->execute()
		->toArray();
		$categorys = toLevel($categorys);
		$this->view->setVar('categorys', $categorys);

		$dataList = $this->_getDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('page','index');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}

	}

	/**
	 * Create  action
	 */
	public function   newAction()
	{
		$this->dispatcher->forward(
				[
						"controller" => "page",
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
			$item = PageModel::factory()->findFirst('id='.$itemId);
			if(empty($item))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
			$this->view->setVar('item', $item);
		}

		//单页分类
		$categorys = PagecategoryModel::query()
		->execute()->toArray();

		//分类分级处理
		$categorys = toLevel($categorys);

		$this->view->setVar('categorys', $categorys);
	}

	/**
	 * Save action
	 */
	public function saveAction()
	{
		$backUrl = $this->getHelper()->createUrl(array('p'=>'page/index'));
		if($this->request->isPost())
		{
			$postData = $_POST;
			if(empty($postData['name']) || empty($postData['cat_id']) )
			{
				Utils::showMsg('请求的参数不正确!',$backUrl);
			}
			//上传图片
			if($_FILES['pic']["name"])
			{
				 $filename = Utils::uploadFile('pic','page');
				 $postData['pic'] = $filename;
			}

			if(empty($postData['pic']))
			{
				unset($postData['pic']);
			}

			if(empty($postData['id'])){
				unset($postData['id']);
				$postData['created'] = $this->getHelper()->getTime()->gmtime();
				$result = PageModel::factory()->saveData($postData);
			}else{
				$item = PageModel::factory()->findFirst('id='.intval($postData['id']));
				if(empty($item))
				{
					Utils::showMsg('修改的记录不存在!',$backUrl);
				}
				$result = $item->saveData($postData);

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
			$where .= " AND a.cat_id = ".intval($_REQUEST['cat_id']);
			$filter['cat_id'] = $_REQUEST['cat_id'];
		}
		if($_REQUEST['title'])
		{
			$where .= " AND a.title LIKE '%".trim($_REQUEST['title'])."%'";
			$filter['title'] = $_REQUEST['title'];
		}
		$dataList = new \stdClass();
		
		//生产查询构建对象
		$queryBuilder = PageModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Cms\Model\PageModel','a')
		->leftJoin('\ScshuxCms\Cms\Model\PagecategoryModel','r.id=a.cat_id','r')
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
		$items = $queryBuilder->columns('a.id,a.name,a.cat_id,a.sort,a.created,r.name as cat_name,r.keycode')
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
			$items = PageModel::factory()->find('id in('.$ids.')');
			foreach ($items as $item){
				$item->delete();
			}
		}
	}

}