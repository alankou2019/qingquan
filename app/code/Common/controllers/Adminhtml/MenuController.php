<?php
/**
 * 菜单管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Common\Model\MenuModel;
use ScshuxCms\Core\Helper\Utils;
class MenuController extends  AdminBaseController
{
	/**
	 *  菜单列表
	 */
	public function indexAction()
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
			$this->view->render('menu','index');
			$this->view->finish();
		    $dataList->content = $this->view->getContent();
		    $this->sendSuccessResult($dataList);
		}

	}

	/**
     * 添加
     */
	public function   newAction()
	{
		$this->dispatcher->forward(
				[
				"controller" => "menu",
  				 "action" => "edit"
		]);
	}

	/**
	 * 编辑
	 */
	public function editAction()
	{
		$id = isset($_REQUEST['id'])?intval($_REQUEST['id']):'';
		if($id>0){
			$app = MenuModel::factory()->findFirst('id='.$id);
			if(empty($app))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
			$app->password = '';
			$this->view->setVar('item', $app);
		}
		$menus = MenuModel::query()
		->execute()->toArray();;
		$menus = toLevel($menus);
		$this->view->setVar('categorys', $menus);
	}

	/**
	 * 保存
	 */
	public function saveAction()
	{
		$backUrl = $this->getHelper()->createUrl(array('p'=>'menu/index'));
		if($this->request->isPost())
		{
    		$postData = $_POST;
    		if(empty($postData['name']))
    		{
    			Utils::showMsg('请求的参数不正确!',$backUrl);
    		}

    		if(empty($postData['id'])){  //添加
    			unset($postData['id']);
				$result = MenuModel::factory()->saveData($postData);
    		}else{ //编辑
    			$menuModel = MenuModel::factory()->findFirst('id='.intval($postData['id']));
    			if(empty($menuModel))
    			{
    				Utils::showMsg('修改的记录不存在!',$backUrl);
    			}
				$result = $menuModel->saveData($postData);

    		}
    		if($result){
    			Utils::showMsg('操作成功!',$backUrl);
    		}else{
    			Utils::showMsg('操作失败!',$backUrl);
    		}
    	}
    	else
    	{
    		Utils::showMsg('不支持的请求方式!',$backUrl);
    	}
	}

	/**
	 * 获取菜单列表数据
	 */
	private function _getDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):25;
		$where = '1=1';

		$dataList = new \stdClass();

		/*统计*/
		$countInfo = MenuModel::query()
		->where($where)
		->columns('count(*) as num')->execute();

		$dataList->count = $countInfo[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize = $pagesize;
		$dataList->pageCount= ceil($dataList->count/$dataList->pageSize);


		/*加载数据*/
		$offset = ($page-1)*$pagesize;
		$items = MenuModel::query()
		->where($where)
		->orderBy("sort asc,id asc")
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
			$menus = MenuModel::factory()->find('id in('.$ids.')');
			foreach ($menus as $menu){
				 $check = MenuModel::factory()->findFirst("parent_id=".$menu->id);
				 if($check)
				 {
				 	$this->sendJson(array('status'=>'n','msg'=>'该菜单下还有子菜单'));
				 }

				 $menu->delete();
			}
		}
	}
}