<?php
/**
 * 留言管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Message\Model\MessageModel;
use ScshuxCms\Core\Helper\Utils;
class MessageController extends AdminBaseController
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
			$this->view->render('message','index');
			$this->view->finish();
		    $dataList->content = $this->view->getContent();
		    $this->sendSuccessResult($dataList);
		}

	}

	/**
	 * view action
	 */
	public function viewAction()
	{
		$id = isset($_REQUEST['id'])?intval($_REQUEST['id']):'';
		if($id>0){
			$user = MessageModel::factory()->findFirst('id='.$id);
			if(empty($user))
			{
				Utils::showMsg('查看的记录不存在!',$backUrl);
			}
			$this->view->setVar('item', $user);
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
			 $startTime = $_REQUEST['start_time'].' 00:00:00';
			 $startTime = strtotime($startTime);
			 $where .= ' AND a.inputtime >="'.$startTime.'"';
			 $filter['start_time'] = $_REQUEST['start_time'];
		}

		if(isset($_REQUEST['end_time']) && !empty($_REQUEST['end_time']))
		{
 			 $endTime = $_REQUEST['end_time'].' 23:59:59';
 			 $endTime = strtotime($endTime);
			 $where .= ' AND a.inputtime <="'.$endTime.'"';
			 $filter['end_time'] = $_REQUEST['end_time'];
		}
		$dataList = new \stdClass();

		//生产查询构建对象
		$queryBuilder = MessageModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Message\Model\MessageModel','a')
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
			$messages = MessageModel::factory()->find('id in('.$ids.')');
			foreach ($messages as $message){
				 $message->delete();
			}
		}
	}

}