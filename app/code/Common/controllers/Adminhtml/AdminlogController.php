<?php
/**
 * 系统日志
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Common\Model\AdminLogModel;
use ScshuxCms\Core\Helper\Utils;
class AdminlogController extends AdminBaseController
{

    /**
     * Index action
     */
	public  function  indexAction()
	{
		$act = isset($_REQUEST['act'])?$_REQUEST['act']:'';
		$isAjax = isset($_REQUEST['is_ajax'])?$_REQUEST['is_ajax']:false;
		$month = isset($_REQUEST['month'])?intval($_REQUEST['month']) : 0;
		if($act == 'remove'){
			$this->_remove($_REQUEST['id'],$month);
		}
		$dataList = $this->_getDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('adminlog','index');
			$this->view->finish();
		    $dataList->content = $this->view->getContent();
		    $this->sendSuccessResult($dataList);
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
		if(isset($_REQUEST['start_time']) && !empty($_REQUEST['start_time']))
		{
			 $startTime = $this->getHelper()->getTime()->gmstr2time($_REQUEST['start_time'].' 00:00:00');
			 $where .= ' AND a.created >='.$startTime;
			 $filter['start_time'] = $_REQUEST['start_time'];
		}

		if(isset($_REQUEST['end_time']) && !empty($_REQUEST['end_time']))
		{
 			 $endTime = $this->getHelper()->getTime()->gmstr2time($_REQUEST['end_time'].' 23:59:59');
			 $where .= ' AND a.created <='.$endTime;
			 $filter['end_time'] = $_REQUEST['end_time'];
		}

		$dataList = new \stdClass();
		//生产查询构建对象
		$queryBuilder = AdminLogModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Common\Model\AdminLogModel','a')
		->leftJoin('\ScshuxCms\Common\Model\AdminUserModel','a.admin_id=u.user_id','u')
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
		$items = $queryBuilder->columns('a.id,a.content,a.created,a.ip,u.realname')->limit($pagesize,$offset)
		->getQuery()
		->execute();

		$dataList->items = $items;
		return $dataList;

	}

	/**
	 * 删除数据
	 * @param  $ids
	 */
	protected  function  _remove($ids=0,$month=0)
	{
		if($ids){
			$adminLogs = AdminLogModel::factory()->find('id in('.$ids.')');
		} else if($month>0){
			$diffSec = 3600*24*30*$month;
			$lastTime= $this->getHelper()->getTime()->gmtime() - $diffSec;
			$adminLogs = AdminLogModel::factory()->find('created <'.$lastTime);
		}
		foreach ($adminLogs as $adminLog){
			$adminLog->delete();
		}
	}

}