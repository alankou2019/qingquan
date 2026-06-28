<?php
/**
 * 广告位管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Advert\Model\AdvertPositionModel;
use ScshuxCms\Core\Helper\Utils;
class  AdvertpositionController extends AdminBaseController
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
			$this->view->render('advertposition','index');
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
						"controller" => "advertposition",
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
			$item = AdvertPositionModel::factory()->findFirst('id='.$itemId);
			if(empty($item))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
			$this->view->setVar('item', $item);
		}

	}

	/**
	 * Save action
	 */
	public function saveAction()
	{
		$backUrl = $this->getHelper()->createUrl(array('p'=>'advertposition/index'));
		if($this->request->isPost())
		{
			$postData = $_POST;
			if(empty($postData['name']) || empty($postData['key_code']))
			{
				Utils::showMsg('请求的参数不正确!',$backUrl);
			}

			if(empty($postData['id']))
    		{
    			if(empty($postData['key_code'])){
					Utils::showMsg('请求的参数不正确!',$backUrl);
    			}else{
		    		//查询key_code唯一
					$appinfo = AdvertPositionModel::factory()->findFirst('key_code="'.$postData['key_code'].'"');
					if($appinfo)
					{
						Utils::showMsg('应用标识已经存在',$backUrl);
					}
    			}
    		}

			if(empty($postData['id'])){
				unset($postData['id']);
				$result = AdvertPositionModel::factory()->save($postData);
			}else{
				$item = AdvertPositionModel::factory()->findFirst('id='.intval($postData['id']));
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
		$filter = array();
		$where = '1=1';
		if(isset($_REQUEST['filter']) && $_REQUEST['filter'] && isset($_REQUEST['keywords'])){
			$filter['filter'] = trim($_REQUEST['filter']);
			$filter['keywords'] = trim($_REQUEST['keywords']);
			$where .= " AND  {$filter['filter']}  LIKE '%{$filter['keywords']}%'";
		}

		$dataList = new \stdClass();

		/*统计*/
		$countInfo = AdvertPositionModel::query()
		->where($where)
		->columns('count(*) as num')->execute();


		$dataList->count = $countInfo[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize = $pagesize;
		$dataList->pageCount= ceil($dataList->count/$dataList->pageSize);

		/*加载数据*/
		$offset = ($page-1)*$pagesize;
		$items = AdvertPositionModel::query()
		->where($where)
		->limit($pagesize,$offset)
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
			$items = AdvertPositionModel::factory()->find('id in('.$ids.')');
			foreach ($items as $item){
				$item->delete();
			}
		}
	}

}