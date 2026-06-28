<?php
/**
 * 区域管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Common\Model\AreaModel;
use ScshuxCms\Core\Helper;
class AreaController extends AdminBaseController
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
			$this->view->render('area','index');
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
				"controller" => "area",
  				 "action" => "edit"
		]);
	}

	/**
	 * Edit action
	 */
	public function editAction()
	{
		$id = isset($_REQUEST['id'])?intval($_REQUEST['id']):0;
		$parent_id = isset($_REQUEST['parent_id'])?intval($_REQUEST['parent_id']):0;
		if($id>0){
			$item = AreaModel::factory()->findFirst('id='.$id);
			if(empty($item))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			};
			if($item->parent_id>0)
			{
				$parent = AreaModel::factory()->findFirst('id='.$item->parent_id);
				$item->parent_name = $parent->name;
			}else{
				$item->parent_name = '中国';
			}
			$this->view->setVar('item', $item);
		}
		if($parent_id>0)
		{
			$parentItem = AreaModel::factory()->findFirst('id='.$parent_id);
			if($parentItem)
			{
				$path = '';
				if(empty($parentItem->path)){
					$path = $parent_id;
				}else{
					$path = $parentItem->path;
				}
				$new_path = str_replace('_', ',', $path);
				$this->view->setVar('path', $new_path);
			}
		}
	}

	/**
     * Save action
     */
    public function saveAction()
    {
    	$backUrl = $this->getHelper()->createUrl(array('p'=>'area/index'));
    	if($this->request->isPost())
    	{
    		$postData = $_POST;
    		if(empty($postData['name']))
    		{
    			Utils::showMsg('请求的参数不正确!',$backUrl);
    		}

    		if(empty($postData['pinyin']))
    		{
    			$postData['pinyin'] = $this->getHelper()->pinyin($postData['name']);
    		}
    		if(empty($postData['id'])){

    			unset($postData['id']);

    			//清理层级关系
    			$path  = explode('_', $postData['path']);
    			$pathArr = array();
    			foreach ($path as $key=>$value)
    			{
    				if(empty($value))
    				{
    					break;
    				}
    				$pathArr[] = $value;
    			}
    			if(empty($pathArr))
    			{
    				$postData['deep'] = 1;
    				$postData['path'] = '';
    				$postData['parent_id'] = 0;
    			}else{
    				$postData['deep'] = count($pathArr)+1;
    				$postData['path'] = join('_', $pathArr);
    				$postData['parent_id'] = $pathArr[count($pathArr)-1];
    			}
    			$result = AreaModel::factory()->save($postData);
    			$backUrl = $this->getHelper()->createUrl(array('p'=>'area/index','parent_id'=>$postData['parent_id']));

    		}else{
    			$areaModel = AreaModel::factory()->findFirst('id='.intval($postData['id']));
    			if(empty($areaModel))
    			{
    				Utils::showMsg('修改的记录不存在!',$backUrl);
    			}
    			$result =$areaModel->save($postData);
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
	 * 返回上一级
	 */
	public  function  backAction()
	{

		$child_id  = isset($_REQUEST['child_id'])?intval($_REQUEST['child_id']):0;
		if($child_id<1)
		{
			$this->redirect('area/index');
		}
		//读取上级
		$area = AreaModel::findFirst('id='.$child_id);
		if(empty($area) || empty($area->parent_id))
		{
			$this->redirect('area/index');
		}
		$area = AreaModel::findFirst('id='.$area->parent_id);
		if(empty($area) || empty($area->parent_id))
		{
			$this->redirect('area/index');
		}

		$this->redirect(array('p'=>'area/index','parent_id'=>$area->parent_id));
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
		$parentId  = isset($_REQUEST['parent_id'])?intval($_REQUEST['parent_id']):0;

		$filter = array();
		$where = 'a.parent_id='.$parentId;
		if(isset($_REQUEST['filter']) && $_REQUEST['filter'] && isset($_REQUEST['keywords'])){
			$filter['filter'] = trim($_REQUEST['filter']);
			$filter['keywords'] = trim($_REQUEST['keywords']);
			$where .= " AND  {$filter['filter']}  LIKE '%{$filter['keywords']}%'";
		}

		$dataList = new \stdClass();
		//生产查询构建对象
		$queryBuilder = AreaModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Common\Model\AreaModel','a')
		->andWhere($where);

		//统计
		$countInfo = $queryBuilder->columns('COUNT(*) as num')
		->getQuery()
		->execute();

		$dataList->count = $countInfo[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize = $pagesize;
		$dataList->pageCount= ceil($dataList->count/$dataList->pageSize);

		/*加载数据*/
		$offset = ($page-1)*$pagesize;
		$items = $queryBuilder->columns('a.*')
		->orderBy("a.sort asc")
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
			$areas = AreaModel::factory()->find('id in('.$ids.')');
			foreach ($areas as $area){
				 $area->delete();
			}
		}
	}

}