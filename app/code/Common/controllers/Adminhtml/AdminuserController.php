<?php
/**
 * 系统用户
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Common\Model\AdminUserModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Common\Model\AdminRoleModel;
class AdminuserController extends AdminBaseController
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
			$this->view->render('adminuser','index');
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
				"controller" => "adminuser",
  				 "action" => "edit"
		]);
	}

	/**
	 * Edit action
	 */
	public function editAction()
	{
		$userId = isset($_REQUEST['id'])?intval($_REQUEST['id']):'';
		if($userId>0){
			$adminUser = AdminUserModel::factory()->findFirst('user_id='.$userId);
			if(empty($adminUser))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
			$adminUser->password = '';
			$this->view->setVar('item', $adminUser);
		}
		$adminRoles = AdminRoleModel::find();
		$this->view->setVar('adminRoles', $adminRoles);

	}

	/**
     * Save action
     */
    public function saveAction()
    {
    	$backUrl = $this->getHelper()->createUrl(array('p'=>'adminuser/index'));
    	if($this->request->isPost())
    	{
    		$postData = $_POST;
    		if(empty($postData['username'])  || empty($postData['realname']))
    		{
    			Utils::showMsg('请求的参数不正确!',$backUrl);
    		}


    		if(!empty($postData['email']) && !Utils::isEmail($postData['email']))
    		{
    			Utils::showMsg('Email格式不正确!',$backUrl);
    		}

    		if(empty($postData['password'])){
    			unset($postData['password']);
    		}else{
    			$postData['password'] = md5($postData['password']);
    		}

    		if(empty($postData['user_id'])){

    			$postData['created'] = $this->getHelper()->getTime()->gmtime();
    			unset($postData['user_id']);
				$result = AdminUserModel::factory()->saveData($postData);

    		}else{

    			$adminUserModel = AdminUserModel::factory()->findFirst('user_id='.intval($postData['user_id']));
    			if(empty($adminUserModel))
    			{
    				Utils::showMsg('修改的记录不存在!',$backUrl);
    			}
    			$postData['modified'] = $this->getHelper()->getTime()->gmtime();
				$result =$adminUserModel->saveData($postData);

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
		$queryBuilder = AdminUserModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Common\Model\AdminUserModel','a')
		->leftJoin('\ScshuxCms\Common\Model\AdminRoleModel','r.id=a.role_id','r')
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
		$items = $queryBuilder->columns('a.user_id,a.role_id,a.created,
				a.realname,a.username,a.lognum,r.name as role_name,a.email,a.last_ip,a.is_active')
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
			$adminUsers = AdminUserModel::factory()->find('user_id in('.$ids.')');
			foreach ($adminUsers as $adminUser){
				 $adminUser->delete();
			}
		}
	}

}