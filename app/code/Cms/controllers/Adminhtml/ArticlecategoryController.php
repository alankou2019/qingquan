<?php
/**
 * 文章分類管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Cms\Model\ArticlecategoryModel;
use ScshuxCms\Core\Helper\Utils;
class  ArticlecategoryController extends AdminBaseController
{
	/**
	 * Index action
	 */
	public  function  indexAction()
	{
		$act = isset($_REQUEST['act'])?$_REQUEST['act']:'';
		$isAjax = isset($_REQUEST['is_ajax'])?$_REQUEST['is_ajax']:false;

		$dataList = $this->_getDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('articlecategory','index');
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
						"controller" => "articlecategory",
						"action" => "edit"
				]);
	}

	/**
	 * Edit action
	 */
	public function editAction()
	{
		$itemId = isset($_REQUEST['id'])?intval($_REQUEST['id']):'';
		$haskey = isset($_REQUEST['haskey'])?intval($_REQUEST['haskey']):'';
		if($itemId>0 && empty($haskey)){
			$item = ArticleCategoryModel::factory()->findFirst('id='.$itemId);
			if(empty($item))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
			$this->view->setVar('item', $item);
		}
		if($haskey)   //新增
		{
			$item = new \stdClass();
			$item->parent_id = $itemId;
			$this->view->setVar('item', $item);
		}
		//dump($item);
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
		$backUrl = $this->getHelper()->createUrl(array('p'=>'articlecategory/index'));
		if($this->request->isPost())
		{
			$postData = $_POST;
			if(empty($postData['name']) || $postData['parent_id']=='')
			{
				Utils::showMsg('请求的参数不正确!',$backUrl);
			}
			//上传图片
			if($_FILES['pic']["name"])
			{
				 $filename = Utils::uploadFile('pic','articlecategory');
				 $postData['pic'] = $filename;
			}
			if(empty($postData['id'])){

				$result = ArticlecategoryModel::factory()->saveData($postData);

			}else{

				$item = ArticlecategoryModel::factory()->findFirst('id='.intval($postData['id']));
				if(empty($item))
				{
					Utils::showMsg('修改的记录不存在!',$backUrl);
				}
				$result =$item->saveData($postData);
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
	 * 删除操作
	 */
	public function removeAction()
	{
		$ids = trim($_REQUEST['ids']);
		if($ids){
			$items = ArticlecategoryModel::factory()->find('id in('.$ids.')');
			foreach ($items as $item){
				$item->delete();
			}
			$this->sendSuccessResult();
		}else{
			$this->sendErrorResult();
		}
	}

	/**
	 * 修改字段值
	 */
	public function changeFieldAction()
	{
		$id = trim($_REQUEST['id']);
		$field = trim($_REQUEST['field']);
		if($field == 'name'){
			$value = trim($_REQUEST['value']) ? trim($_REQUEST['value']) : '';
			if($value){
				$item = ArticlecategoryModel::factory()->findFirst('id='.$id);
				$item->saveData(array('name'=>$value));
			}else{
				$this->sendErrorResult();
			}
		}elseif($field == 'sort'){
			$value = trim($_REQUEST['value']) ? intval(trim($_REQUEST['value'])) : 0;
			$item = ArticlecategoryModel::factory()->findFirst('id='.$id);
			$item->saveData(array('sort'=>$value));
		}else{
			$this->sendErrorResult('暂不支持此字段');
		}
		$this->sendSuccessResult();
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
		if(isset($_REQUEST['filter']) && $_REQUEST['filter'] && isset($_REQUEST['keywords'])){
			$filter['filter'] = trim($_REQUEST['filter']);
			$filter['keywords'] = trim($_REQUEST['keywords']);
			$where .= " AND  {$filter['filter']}  LIKE '%{$filter['keywords']}%'";
		}
		//dump($_REQUEST);
		$dataList = new \stdClass();

		/*统计*/
		$countInfo = ArticlecategoryModel::query()
		->where($where)
		->columns('count(*) as num')->execute();


		$dataList->count = $countInfo[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize = $pagesize;
		$dataList->pageCount= ceil($dataList->count/$dataList->pageSize);

		/*加载数据*/
		$offset = ($page-1)*$pagesize;
		$items = ArticlecategoryModel::query()
		->where($where)
		->orderBy('sort asc')
		->limit($pagesize,$offset)
		->execute()->toArray();

		$items = toLayer($items,'child',0);
		$dataList->items = $items;
		return $dataList;

	}


}