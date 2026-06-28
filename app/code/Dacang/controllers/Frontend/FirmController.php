<?php
/**
 * 前台 公司管理
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

class FirmController extends FrontendBaseController
{

	/**
	 *
	 * @desc 人员列表
	 * @date 2017年4月1日
	 */
	public  function  staffAction()
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
			$this->view->render('firm','staff');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}


	

	
	/**
	 *
	 * @desc 简单人员列表   读取当前公司的人员   获取考核人员的时候使用
	 * @date 2017年4月1日
	 */
	public  function  simpleListAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
	
		$dataList = $this->_getDataList();
		
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('firm','simplelist');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	
	}
	
	
	
	/**
	 * 
	 * @desc	设置权限		
	 * @date	2017年5月5日
	 */
	public function setRightAction()
	{
		$isajax = $this->request->isAjax();
		if($isajax)
		{
			$request = $this->request ;
			$id = $request->get('id') ;
			$right = $request->get('right');
			
			if(!$id || !$right)
			{
				$this->sendErrorResult('参数错误') ;
			}
			
			$id = intval($id) ;
			$right = intval($right) ;
			
			$userinfo = CompanyUserModel::findFirst($id) ;
			if(!$userinfo)
			{
				$this->sendErrorResult('用户不存在') ;
			}
			
			$userinfo->right = $right ;
			if ($userinfo->save())
			{
				$this->sendSuccessResult('设置成功') ;
			}
			else 
			{
				$this->sendErrorResult('保存错误，请稍后再试') ;
			}
		}
		else 
		{
			$this->sendErrorResult('请求方式错误');
		}
	}
	
	
	
	/**
	 * 
	 * @desc	添加考核管理人员		
	 * @date	2017年5月16日
	 */
	public function addReportAction()
	{
		$isajax = $this->request->isAjax() ;
		if($isajax)
		{
			$ids = $this->request->get('id') ;
			
			if($ids){
				//限制人数
				$companyinfo = CompanyModel::findFirst($this->companyId);
				$personlimit = $companyinfo->personlimit ;
				
				//已经添加人数
				$hasnum = CompanyUserModel::factory()->getHasNum($this->companyId) ;
				
				if($hasnum >= $personlimit)
				{
					$this->sendErrorResult('可添加数量已达到限制，请联系管理员') ;
				}
				
				//获取可添加人数
				$addnum = $personlimit - $hasnum ;
				
				$items = CompanyUserModel::factory()->find('id in('.$ids.')');
				$incrnum = 1 ;
				foreach ($items as $item){
					if($incrnum > $addnum)
					{
						$this->sendErrorResult('数量已达到限制，添加成功'.$incrnum.'个') ;
						break ;
					}
					
					if($item->addreport == 1 )
					{
						continue ;
					}
					else
					{
						$item->addreport = 1 ;
						$item->save();
					}
				}
				
				$this->sendSuccessResult('添加成功') ;
			}
			else 
			{
				$this->sendErrorResult('请上传参数') ;
			}
			$this->sendSuccessResult('设置成功');
		}
		else 
		{
			$this->sendErrorResult('请求方式错误') ;
		}
		
	}
	
	
	
	/**
	 *
	 * @desc	添加考核管理人员
	 * @date	2017年5月16日
	 */
	public function addGroupAction()
	{
		$isajax = $this->request->isAjax() ;
		if($isajax)
		{
			$ids = $this->request->get('id') ;
				
			if($ids){
				$res=CheckGroupModel::addToGroup($ids,$this->companyId);
				if ($res)
				{
					$this->sendSuccessResult('添加成功') ;
				}
				$this->sendErrorResult('添加失败，请稍后再试');
			}
			else
			{
				$this->sendErrorResult('error') ;
			}
		}
		else
		{
			$this->sendErrorResult('error') ;
		}
	
	}
	
	
	
	
	/**
	 *
	 * @desc	移除考核管理人员
	 * @date	2017年5月16日
	 */
	public function removeReportAction()
	{
		$isajax = $this->request->isAjax() ;
		if($isajax)
		{
			$ids = $this->request->get('id') ;
				
			if($ids){
				$items = CompanyUserModel::factory()->find('id in('.$ids.')');
				foreach ($items as $item){
					if($item->addreport == 0 )
					{
						continue ;
					}
					else
					{
						$item->addreport = 0 ;
						$item->save();
					}
				}
	
				$this->sendSuccessResult('移除成功') ;
			}
			else
			{
				$this->sendErrorResult('请上传参数') ;
			}
			$this->sendSuccessResult('移除成功');
		}
		else
		{
			$this->sendErrorResult('请求方式错误') ;
		}
	
	}
	
	
	
	
	/**
	 *
	 * @desc 考核组人员
	 * @date 2017年4月1日
	 */
	public  function  reportListAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
	
		if($act == 'remove'){
			$this->_removeReport($_REQUEST['id']);
		}
	
		$dataList = $this->_getReportDataList();
	
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('firm','reportlist');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}
	
	
	
	
	/**
	 *
	 * @desc 考核组人员
	 * @date 2017年4月1日
	 */
	public  function  pointReportListAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
	
		if($act == 'remove'){
			$this->_removeReport($_REQUEST['id']);
		}
	
		$dataList = $this->_getReportDataList();
	
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('firm','pointreportlist');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}
	
	
	
	
	/**
	 *
	 * @desc 获取公司人员列表
	 * @date 2017年4月1日
	 */
	protected  function _getDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$filter = array();
	
		$where = ' u.company_id = '.$this->companyId.' and d.company_id = '.$this->companyId ;
	
		if($_REQUEST['name']){
			$filter['name'] = trim($_REQUEST['name']);
			$where .= " and u.name  like '%{$filter['name']}%'";
		}
	
	
		if($_REQUEST['department_id']){
			$filter['department_id'] = intval($_REQUEST['department_id']);
			$where .= " and d.id  = ".$filter['department_id'];
		}
	
		
		$dataList = new \stdClass();
	
		/*统计*/
		$countInfo = $this->modelsManager->createBuilder()
										->columns('count(*) as num')
										->addFrom('ScshuxCms\Dacang\Model\CompanyUserModel','u')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyDepartModel','d.dingding_id = u.department_id','d')
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
										->columns('u.id,u.name,u.dingding_user_id,u.is_admin,u.is_leader,u.email,d.name as departmentname')
										->addFrom('ScshuxCms\Dacang\Model\CompanyUserModel','u')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyDepartModel','d.dingding_id = u.department_id','d')
										->where($where)
										->orderBy('d.id desc')
										->limit($pagesize,$offset)
										->getQuery()
										->execute()
										->toArray();
	
		$dataList->items = $items;
	
		$this->view->setVar('isadmin', Constants::isAdmin()) ;
		$this->view->setVar('isleader', Constants::isLeader()) ;
		$this->view->setVar('departlist', DepartmentModel::TreeDepartList($this->companyId)) ;
	
		
		return $dataList;
	}
	
	
	
	
	/**
	 *
	 * @desc 获取公司考核组人员列表
	 * @date 2017年4月1日
	 */
	protected  function _getReportDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$filter = array();
	
		$where = ' u.company_id = '.$this->companyId.' and d.company_id = '.$this->companyId.' and u.addreport = 1';
		$andwhere=UserManageRoleModel::factory()->getWhereByUserManageRole('d.id');
		$where = $andwhere?$where.' and '.$andwhere:$where;
		
		if($_REQUEST['name']){
			$filter['name'] = trim($_REQUEST['name']);
			$where .= " and u.name  like '%{$filter['name']}%'";
		}
	
	
		if($_REQUEST['department_id']){
			$filter['department_id'] = intval($_REQUEST['department_id']);
			$where .= " and d.id  = ".$filter['department_id'];
		}
	
	
		$dataList = new \stdClass();
	
		/*统计*/
		$countInfo = $this->modelsManager->createBuilder()
									->columns('count(*) as num')
									->addFrom('ScshuxCms\Dacang\Model\CompanyUserModel','u')
									->leftJoin('ScshuxCms\Dacang\Model\CompanyDepartModel','d.dingding_id = u.department_id','d')
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
	
	
		//为0 的话 则为不限制
		if ($personlimit != 0)
		{
			if(($page * $pagesize) > $personlimit)
			{
				$pagesize = $personlimit - $offset ;
			}
		}
	
		$items = $this->modelsManager->createBuilder()
								->columns('u.id,u.name,u.dingding_user_id,u.is_admin,u.is_leader,u.email,d.name as departmentname')
								->addFrom('ScshuxCms\Dacang\Model\CompanyUserModel','u')
								->leftJoin('ScshuxCms\Dacang\Model\CompanyDepartModel','d.dingding_id = u.department_id','d')
								->where($where)
								->orderBy('d.id desc')
								->limit($pagesize,$offset)
								->getQuery()
								->execute()
								->toArray();
	
		$dataList->items = $items;
	
		$this->view->setVar('isadmin', Constants::isAdmin()) ;
		$this->view->setVar('isleader', Constants::isLeader()) ;
		$this->view->setVar('departlist', DepartmentModel::TreeDepartList($this->companyId)) ;
	
		return $dataList;
	}
	
	
	
	
	/**
	 * 删除数据
	 * @param  $ids
	 */
	protected  function  _remove($ids)
	{
		if($ids){
			$items = CompanyUserModel::factory()->find('id in('.$ids.')');
			
			
			foreach ($items as $item){
				if($item->is_admin == 1 )
				{
					continue ;
				}
				else 
				{
					$item->delete();
				}
			}
		}
	}
	
	
	
	
	/**
	 * 删除考核组人员
	 * @param  $ids
	 */
	protected  function  _removeReport($ids)
	{
		if($ids){
			$items = CompanyUserModel::factory()->find('id in('.$ids.')');
				
				
			foreach ($items as $item){
				if($item->addreport == 0 )
				{
					continue ;
				}
				else
				{
					$item->addreport = 0 ;
					$item->save();
				}
			}
		}
	}

}