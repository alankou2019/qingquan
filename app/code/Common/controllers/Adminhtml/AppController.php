<?php
/**
 * 应用授权管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Common\Model\AppModel;
use ScshuxCms\Core\Helper\Utils;
class AppController extends  AdminBaseController
{
	/**
	 *  应用列表
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
			$this->view->render('app','index');
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
				"controller" => "app",
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
			$app = AppModel::factory()->findFirst('id='.$id);
			if(empty($app))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
			$app->password = '';
			$this->view->setVar('item', $app);
		}
	}

	/**
	 * 保存
	 */
	public function saveAction()
	{
		$backUrl = $this->getHelper()->createUrl(array('p'=>'app/index'));
		if($this->request->isPost())
		{
    		$postData = $_POST;
    		if(empty($postData['app_name']) || empty($postData['public_key']) || empty($postData['private_key']) ||empty($postData['app_key']))
    		{
    			Utils::showMsg('请求的参数不正确!',$backUrl);
    		}
    		
    		if(empty($postData['id'])){  //添加
    			
    			$postData['app_key'] = md5(microtime(true));
    			$postData['created'] = $this->getHelper()->getTime()->gmtime();
    			unset($postData['id']);
    			$result = AppModel::factory()->save($postData);
    			

    		}else{ //编辑

    			$appModel = AppModel::factory()->findFirst('id='.intval($postData['id']));
    			
    			$checkApp = AppModel::factory()->loadByAppKey($postData['app_key']);
    			if($checkApp &&  ($checkApp->id!=$appModel->id))
    			{
    				Utils::showMsg('appkey已经存在!',$backUrl);
    			}
    			
    			if(empty($appModel))
    			{
    				Utils::showMsg('修改的记录不存在!',$backUrl);
    			}
    			$result =$appModel->save($postData);

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
	 * 获取应用列表数据
	 */
	private function _getDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$where = '1=1';
		if(isset($_REQUEST['filter']) && $_REQUEST['filter'] && isset($_REQUEST['keywords'])){
			$filter['filter'] = trim($_REQUEST['filter']);
			$filter['keywords'] = trim($_REQUEST['keywords']);
			$where .= " AND  {$filter['filter']}  LIKE '%{$filter['keywords']}%'";
		}

		$dataList = new \stdClass();

		/*统计*/
		$countInfo = AppModel::query()
		->where($where)
		->columns('count(*) as num')->execute();

		$dataList->count = $countInfo[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize = $pagesize;
		$dataList->pageCount= ceil($dataList->count/$dataList->pageSize);


		/*加载数据*/
		$offset = ($page-1)*$pagesize;
		$items = AppModel::query()
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
			$apps = AppModel::factory()->find('id in('.$ids.')');
			foreach ($apps as $app){
				 $app->delete();
			}
		}
	}
}