<?php
/**
 * 前台 审核组管理
*/
namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use ScshuxCms\User\Model\UserModel;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Core\Constants;
use ScshuxCms\Dacang\Model\DepartmentModel;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\User\Model\UserManageRoleModel;
use ScshuxCms\Dacang\Model\CheckGroupModel;
use ScshuxCms\Dacang\Model\CheckGroupUserModel;

class GroupController extends FrontendBaseController
{

	
	/**
	 * @desc	审核组列表
	 * @param
	 * @return
	 */
	public function indexAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
		
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
			$this->view->render('group','index');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}
	
	
	/**
	 *
	 * @desc 审核组编辑
	 * @date 2017年4月1日
	 */
	public function editAction()
	{
		$this->layout='';
		$gourl=Helper::factory()->createUrl(array('p'=>'group/index'));
		
		$role=array();
		$id=$this->request->get('id');
		if ($id)
		{
			$groupInfo=CheckGroupModel::findFirst($id);
			if (!$groupInfo)
			{
				Utils::showMsg('审核组不存在', $gourl);
			}
			if ($groupInfo->company_id != $this->companyId)
			{
				Utils::showMsg('error!', $gourl);
			}
			//当前审核组拥有的权限
			$role=array();
			$role=explode(',', $groupInfo->depart_ids);
		}
		
		//当前公司的所有部门列表
		$departList=DepartmentModel::TreeDepartList($this->companyId);
		if ($departList)
		{
			foreach ($departList as $depart)
			{
				$depart->isChecked=0;
				if (in_array($depart->id, $role))
				{
					$depart->isChecked=1;
				}
			}
		}
		$departList=Utils::formatTree($departList);
		$this->view->setVar('departList', $departList);
		$this->view->setVar('group', $groupInfo);
	}
	
	
	
	/**
	 * @desc	保存审核组权限
	 * @param			
	 * @return			
	 */
	public function saveAction()
	{
		$gourl =Helper::factory()->createUrl(array('p'=>'group/index'));		
		$res=CheckGroupModel::saveData($this->request);
		if (!$res)
		{
			Utils::showMsg(CheckGroupModel::getError(), $gourl);
		}
		
		Utils::showMsg('操作成功', $gourl);
	}
	
	
	
	
	
	/**
	 *
	 * @desc 审核组人员
	 * @date 2017年4月1日
	 */
	public  function  groupUserAction()
	{	
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
		if($act == 'remove'){
			$this->_removeUser($_REQUEST['id']);
		}
		
		$dataList = $this->_getUserDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		$this->view->setVar('grouplist',CheckGroupModel::getGroup());
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('group','groupuser');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}
	
	
	/**
	 * @desc	添加用户到审核组
	 * @param			
	 * @return			
	 */
	public function addUserToGroupAction()
	{
		$isAjax=$this->request->isAjax();
		$isPost=$this->request->isPost();
		if ($isAjax && $isPost)
		{
			$userId =trim($this->request->get('user_id'));
			$groupId=intval($this->request->get('group_id'));
			if (!$userId || !$groupId)
			{
				$this->sendErrorResult('error');
			}
			$res=CheckGroupUserModel::addToGroup($userId, $groupId);
			if ($res)
			{
				$this->sendSuccessResult('Success');
			}
			$this->sendErrorResult('添加失败，请稍后再试');
		}
		else 
		{
			$this->sendErrorResult('error');
		}
	}


	
	
	/**
	 *
	 * @desc 获取审核组列表
	 * @date 2017年4月1日
	 */
	protected  function _getDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$filter = array();
	
		$where = ' company_id = '.$this->companyId;
		if($_REQUEST['name']){
			$filter['name'] = trim($_REQUEST['name']);
			$where .= " and (name  like '%{$filter['name']}%')";
		}
	
	
		$dataList = new \stdClass();
	
		/*统计*/
		$countInfo = $this->modelsManager->createBuilder()
		->columns('count(*) as num')
		->addFrom('ScshuxCms\Dacang\Model\CheckGroupModel')
		->where($where)
		->getQuery()
		->execute();
		
		$dataList->count       = isset($countInfo[0]) ? $countInfo[0]->num : 0 ;
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		$dataList->filter      = $filter ;
		/*加载数据*/
		$offset = ($page-1)*$pagesize;
		
		$items = $this->modelsManager->createBuilder()
		->addFrom('ScshuxCms\Dacang\Model\CheckGroupModel')
		->where($where)
		->orderBy('id desc')
		->limit($pagesize,$offset)
		->getQuery()
		->execute()
		->toArray();
		
		$dataList->items = $items;
		return $dataList;
	}
	
	
	/**
	 *
	 * @desc 获取审核组人员
	 * @date 2017年4月1日
	 */
	protected  function _getUserDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$dataList = new \stdClass();
		
		$where='c.company_id='.$this->companyId;
		$filter = array();
		if ($_REQUEST['group_id'])
		{
			$filter['group_id'] = trim($_REQUEST['group_id']);
			$where .= " and g.group_id =".$filter['group_id'];
		}
		if($_REQUEST['name']){
			$filter['name'] = trim($_REQUEST['name']);
			$where .= " and (u.name  like '%{$filter['name']}%')";
		}
	
		/*统计*/
		$countInfo = $this->modelsManager->createBuilder()
		->columns('count(*) as num')
		->addFrom('ScshuxCms\Dacang\Model\CheckGroupModel','c')
		->leftJoin('ScshuxCms\Dacang\Model\CheckGroupUserModel','g.group_id=c.id','g')
		->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','g.user_id = u.id','u')
		->where($where)
		->getQuery()
		->execute();
	
		$dataList->count       = isset($countInfo[0]) ? $countInfo[0]->num : 0 ;
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		$dataList->filter      = $filter ;
		/*加载数据*/
		$offset = ($page-1)*$pagesize;
	
		$items = $this->modelsManager->createBuilder()
		->columns('g.id,g.group_id,u.name')
		->addFrom('ScshuxCms\Dacang\Model\CheckGroupModel','c')
		->leftJoin('ScshuxCms\Dacang\Model\CheckGroupUserModel','g.group_id=c.id','g')
		->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','g.user_id = u.id','u')
		->where($where)
		->orderBy('g.id desc')
		->limit($pagesize,$offset)
		->getQuery()
		->execute()
		->toArray();
		$dataList->items = $items;
		return $dataList;
	}
	
	
	/**
	 * @desc	删除审核组
	 * @param			
	 * @return			
	 */
	protected function _remove($ids)
	{
		if ($ids)
		{
			$where=is_numeric($ids)?'id='.$ids:'id in('.$ids.')';
			CheckGroupModel::factory()->deleteBySql($where);
			//同时删除审核组人员
			$andwhere=is_numeric($ids)?'group_id='.$ids:'group_id in('.$ids.')';
			CheckGroupUserModel::factory()->deleteBySql($andwhere);
		}
	}
	
	/**
	 * @desc	删除考核组成员
	 * @param			
	 * @return			
	 */
	protected function _removeUser($ids)
	{
		if ($ids)
		{
			$where=is_numeric($ids)?'id='.$ids:'id in('.$ids.')';
			CheckGroupUserModel::factory()->deleteBySql($where);
		}
	}
}