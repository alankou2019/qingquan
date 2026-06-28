<?php
/**
 * 单页分类管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Cms\Model\PagecategoryModel;

use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Cms\Model\PageModel;
use ScshuxCms\Core\Helper\Utils;
class  PagecategoryController extends AdminBaseController
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
			$this->view->render('pagecategory','index');
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
						"controller" => "pagecategory",
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
			$item = PagecategoryModel::factory()->findFirst('id='.$itemId);
			if(empty($item))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
			$this->view->setVar('item', $item);
		}
		$categorys = PagecategoryModel::query()
		->execute()->toArray();;
		$categorys = toLevel($categorys);
		$this->view->setVar('categorys', $categorys);
	}

	/**
	 * Save action
	 */
	public function saveAction()
	{
		$backUrl = $this->getHelper()->createUrl(array('p'=>'pagecategory/index'));
		if($this->request->isPost())
		{
			$postData = $_POST;
			if(empty($postData['name']))
			{
				Utils::showMsg('请求的参数不正确!',$backUrl);
			}
			//上传图片
			if($_FILES['pic']["name"])
			{
				 $filename = Utils::uploadFile('pic','article');
				 $postData['pic'] = $filename;
			}
			if(empty($postData['keycode']))
			{
				$postData['keycode'] = Utils::pinyin($postData['name']);
			}
			
			//根据keycode查询
			$pagecategory = PagecategoryModel::factory()->findFirst("keycode='".$postData['keycode']."'");
			
			if(empty($postData['id'])){
				if(!empty($pagecategory))
				{
					Utils::showMsg('该分类标识已经使用!',$backUrl);
				}
				unset($postData['id']);
				$postData['created'] = $this->getHelper()->getTime()->localDate('Y-m-d H:i:s');
				$result = PagecategoryModel::factory()->save($postData);
				
			}else{
				
				if(!empty($pagecategory) && $pagecategory->id != $postData['id'])
				{
					Utils::showMsg('该分类标识已经使用!',$backUrl);
				}
				$item = PagecategoryModel::factory()->findFirst('id='.intval($postData['id']));
				if(empty($item))
				{
					Utils::showMsg('修改的记录不存在!',$backUrl);
				}
				$result =$item->save($postData);

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
		$where = '1=1';
		$dataList = new \stdClass();

		/*统计*/
		$countInfo = PageCategoryModel::query()
		->where($where)
		->columns('count(*) as num')->execute();


		$dataList->count = $countInfo[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize = $pagesize;
		$dataList->pageCount= ceil($dataList->count/$dataList->pageSize);

		/*加载数据*/
		$offset = ($page-1)*$pagesize;
		$items = PageCategoryModel::query()
		->where($where)
		->order('sort')
		->limit($pagesize,$offset)
		->execute()
		->toArray();

		$items = arrayToObject(toLevel($items));

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
			$items = PagecategoryModel::factory()->find('id in('.$ids.')');
			foreach ($items as $item){
				$item->delete();
			}
		}
	}

}