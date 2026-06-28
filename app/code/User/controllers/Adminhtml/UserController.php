<?php
/**
 * 会员
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\User\Model\UserModel;
use ScshuxCms\Core\Helper\Utils;
class UserController extends AdminBaseController
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
			$this->view->render('user','index');
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
				"controller" => "user",
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
			$user = UserModel::factory()->findFirst('user_id='.$userId);
			if(empty($user))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
			$user->password = '';
			$this->view->setVar('item', $user);
		}

	}

	/**
     * Save action
     */
    public function saveAction()
    {
    	$backUrl = $this->getHelper()->createUrl(array('p'=>'user/index'));
    	if($this->request->isPost())
    	{
    		$postData = $_POST;
    		if(empty($postData['user_name'])  || empty($postData['phone'] ))
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

    		//上传头像
			if($_FILES['avatar']["name"])
			{
				 $filename = Utils::uploadFile('avatar','user');
				 $postData['avatar'] = $filename;
			}

			if(empty($postData['avatar']))
			{
				unset($postData['avatar']);
			}

			$check_username = UserModel::factory()->loadUserByUserName($postData['user_name']);
    		$check_phone = UserModel::factory()->loadUserByPhone($postData['phone']);

    		if(empty($postData['user_id'])){

	    		if($check_username)
	    		{
					Utils::showMsg('用户名已经存在!',$backUrl);
	    		}
	    		if($check_phone)
	    		{
					Utils::showMsg('电话号码已经存在!',$backUrl);
	    		}

    			$postData['created'] = $this->getHelper()->getTime()->gmtime();
    			$postData['reg_ip'] = $this->getHelper()->getIp();
    			unset($postData['user_id']);
    			$result = UserModel::factory()->save($postData);
    		}else{

    			$UserModel = UserModel::factory()->findFirst('user_id='.intval($postData['user_id']));

    			if($check_username &&  ($check_username->user_id!=$UserModel->user_id))
    			{
    				Utils::showMsg('用户名已经存在!',$backUrl);
    			}
    			if($check_phone &&  ($check_phone->user_id!=$UserModel->user_id))
    			{
    				Utils::showMsg('电话号码已经存在!',$backUrl);
    			}

    			if(empty($UserModel))
    			{
    				Utils::showMsg('修改的记录不存在!',$backUrl);
    			}
    			$result =$UserModel->save($postData);

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
		if(isset($_REQUEST['created']) && !empty($_REQUEST['created']))
		{
			$created_start = $this->getHelper()->getTime()->gmstr2time($_REQUEST['created'].' 00:00:00');
			$created_end = $this->getHelper()->getTime()->gmstr2time($_REQUEST['created'].' 23:59:59');
			$where .= " AND a.created >=".$created_start." AND a.created <=".$created_end;
			$filter['created'] = $_REQUEST['created'];
		}

		$dataList = new \stdClass();

		//生产查询构建对象
		$queryBuilder = UserModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\User\Model\UserModel','a')
		->join('\ScshuxCms\Dacang\Model\CompanyModel','a.company_id=c.id','c')
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
		$items = $queryBuilder->columns('a.user_name,a.user_id,a.login_num,a.last_time,a.created,a.last_ip,a.company_id,c.name as company_name,a.is_admin')
		->orderBy('a.user_id desc')
		->limit($pagesize,$offset)
		->getQuery()
		->execute();
		
		$dataList->items = $items;
		$this->view->setVar('useradmin', UserModel::getAdminName());
		return $dataList;

	}

	/**
	 * 删除数据
	 * @param  $ids
	 */
	protected  function  _remove($ids)
	{
		if($ids){
			$users = UserModel::factory()->find('user_id in('.$ids.')');
			foreach ($users as $user){
				 $user->delete();
			}
		}
	}

}