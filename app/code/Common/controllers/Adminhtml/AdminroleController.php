<?php
/**
 * 角色管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Common\Model\AdminRoleModel;
class  AdminroleController extends AdminBaseController
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
			$this->view->render('adminrole','index');
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
						"controller" => "adminrole",
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
			$item = AdminRoleModel::factory()->findFirst('id='.$itemId);
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
		$backUrl = $this->getHelper()->createUrl(array('p'=>'adminrole/index'));
		if($this->request->isPost())
		{
			$postData = $_POST;
			if(empty($postData['name']))
			{
				Utils::showMsg('请求的参数不正确!',$backUrl);
			}
			if(empty($postData['id'])){

				$result = AdminRoleModel::factory()->save($postData);
				 
			}else{
				 
				$item = AdminRoleModel::factory()->findFirst('id='.intval($postData['id']));
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
		
		//生产查询构建对象
		$queryBuilder = AdminRoleModel::factory()->getModelsManager()->createBuilder()
					->from('\ScshuxCms\Common\Model\AdminRoleModel')
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
		$items = $queryBuilder->columns('*')->limit($pagesize,$offset)
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
			$items = AdminRoleModel::factory()->find('id in('.$ids.')');
			foreach ($items as $item){
				$item->delete();
			}
		}
	}
	
}