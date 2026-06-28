<?php
/**
 * 前台 归档控制器
*/
namespace ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use ScshuxCms\User\Model\UserModel;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Core\Constants;
use ScshuxCms\Dacang\Model\DepartmentModel;
use ScshuxCms\Dacang\Model\ReportModel;
use ScshuxCms\Dacang\Model\ReportTplModel;
use ScshuxCms\Dacang\Model\ReportTplItemModel;
use ScshuxCms\Dacang\Model\ReportUserModel;
use ScshuxCms\Dacang\Model\ReportItemModel;
use ScshuxCms\Dacang\Model\QuotaModel;
use ScshuxCms\Dacang\Model\ReportStoresModel;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Profiler\Item;
use ScshuxCms\Dacang\Model\StoreQuotaModel;
use ScshuxCms\Dacang\Model\ExtraStoresReportItemModel;
use ScshuxCms\User\Model\UserManageRoleModel;
use ScshuxCms\Dacang\Model\QuotaCommentModel;
use ScshuxCms\Dacang\Model\StoreQuotaCommentModel;
class StoresController extends FrontendBaseController
{
	/**
	 *
	 * desc 归档列表
	 * @date 2017年4月21日
	 */
	public function listAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
		if($act == 'remove'){
			$this->_remove($_REQUEST['id']);
		}

		$dataList = $this->_getDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		$this->view->setVar('unique_key',Helper::factory()->getUniqueKey());     	 //导出csv唯一key
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('stores','list');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}


	/**
	 *
	 * @desc 报表详情
	 * @date 2017年4月24日
	 */
	public function detailAction()
	{
		$backurl = $this->getHelper()->createUrl(array('p'=>'stores/list'));  //默认跳回到人员列表

		$request = $this->request ;
		$id  = $request->get('id') ;
		$sid = $request->get('sid') ; 

		if(!$id)
		{
			Utils::showMsg('请选择考核表', $backurl) ;
		}
		$id = intval($id) ;
		$reporttem = ReportModel::findFirst($id) ;
		if(!$reporttem)
		{
			Utils::showMsg('考核数据不存在', $backurl) ;
		}

		$this->view->setVar('item', $reporttem) ;
		$this->view->setVar('userstr', $this->_getUserStr($id)) ;
		$this->view->setVar('reportquotalist', $this->_getReportItemDataList()) ;
		$this->view->setVar('extrareport', ExtraStoresReportItemModel::getExtraUser($id,$sid)) ;
	}

	/**
	 *
	 * @desc 根据报表id 获取用户
	 * @date 2017年4月24日
	 */
	public function _getUserStr($reportId)
	{
		$return = '';
		if(!$reportId)
		{
			return  $return;
		}

		$reportId = intval($reportId) ;

		$items = $this->modelsManager->createBuilder()
									->columns('group_concat(u.name) as name')
									->addFrom('ScshuxCms\Dacang\Model\ReportUserModel','ru')
									->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','ru.user_id = u.id','u')
									->groupBy('ru.report_id')
									->where('ru.report_id = '.$reportId)
									->getQuery()
									->execute() ;					
		if(count($items) > 0 )
		{
			$return =  $items[0]->name ;
		}
		return $return ;
	}



	/**
	 *
	 * @desc	查看指标具体评分情况
	 * @date	2017年5月3日
	 */
	public function showPointAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;

		$dataList = $this->_getShowPointList();

		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);

		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('report','showpoint');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}



	
	
	
	/**
	 *
	 * @desc 导出数据
	 * @date 2017年4月14日
	 */
	public function exportAction()
	{
		$pagesize = 1000;
		$_REQUEST['pagesize'] = $pagesize;
			
		$dataList   = $this->_getDataList();
		
		$data = $dataList->items ;
		$dapartlist = DepartmentModel::departListOne($this->companyId) ;
		foreach ($data as &$item)
		{
			$quotapoint = '';
			$tmp = ReportStoresModel::factory()->getHasStores($item['id'],$item['created']) ;
			foreach ($tmp as $num=>$stores)
			{
				$quotapoint .= $stores['qname'].'  得分：'.floatval($stores['report_point'])."\n" ;
			}
			
			$quotapoint = trim($quotapoint,"\n") ;
			$item['quotapoint'] = '"'.$quotapoint.'"';
			
			$item['dname'] = $dapartlist[$item['department_id']] ;
		}
		$total_page = intval($dataList->pageCount);
	
		$new_name = '月度考核评';
		$helper = Helper::factory();
		$data   = $helper->exportCsv($data,$total_page,$new_name,1);
			
		$this->sendSuccessResult($data);
	}
	
	
	
	
	
	/**
	 * @desc	导出详细
	 * @param
	 * @return
	 */
	public function exportDetailAction()
	{
		$id = $this->request->get('id');
		$sid= $this->request->get('sid');
		if (!$id)
		{
			$this->sendErrorResult('参数错误');
		}
		$_REQUEST['id'] = $id;
		$_REQUEST['sid']= $sid;
			
		$dataList   = $this->_getDataList();
		$data = $dataList->items ;
		if (empty($data))
		{
			$this->sendErrorResult('暂无数据');
		}
	
		$dapartlist = DepartmentModel::departListOne($this->companyId) ;
		$quotaType  =  Constants::getQuotaType();
		$newdata=array();
		foreach ($data as $item)
		{
			$temp = ReportStoresModel::factory()->getHasStoresDetail($item['id'],$item['created']);
			foreach ($temp as $key=>$stores)
			{
				$newdata[$key]['username']  = $item['uname'];
				$newdata[$key]['reportname']= $item['reportname'];
				$newdata[$key]['quotaname'] = $stores->qname;
				$newdata[$key]['quotaType'] = $quotaType[$stores->type];
				$newdata[$key]['reportusername'] = $stores->reportusername;
				$newdata[$key]['reportpoint'] = floatval($stores->report_point);
				$newdata[$key]['reporttime']  = Helper::factory()->getTime()->localDate('Y-m-d H:i',$stores->report_time);
				$newdata[$key]['dname'] = $dapartlist[$item['department_id']] ;
			}	
		}
		unset($data,$temp);
		$total_page = 1;
		$new_name = '月度考核评';
		$helper = Helper::factory();
		$data   = $helper->exportCsv($newdata,$total_page,$new_name,2);
			
		$this->sendSuccessResult($data);
	}
	
	
	/**
	 * @desc	指标点评
	 * @param
	 * @return
	 */
	public function quota_commentAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
		if($act == 'remove'){
			$this->_remove_comment($_REQUEST['id']);
		}
			
		$dataList = $this->_getCommentDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('quota','quota_comment');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
				
				
			$this->sendSuccessResult($dataList);
		}
	
	}
	
	
	/**
	 *
	 * @desc 获取报表对应的指标数据
	 * @date 2017年4月1日
	 */
	protected  function _getReportItemDataList()
	{
		$backurl = $this->getHelper()->createUrl(array('p'=>'report/list'));  //默认跳回到人员列表

		$id = $this->request->get('id') ;
		$sid = $this->request->get('sid') ;
		if(!$id)
		{
			Utils::showMsg('请选择报表', $backurl) ;
		}

		$sid = intval($sid);
		$storesinfo = ReportStoresModel::findFirst($sid);
		if(!$storesinfo)
		{
			Utils::showMsg('归档数据不存在', $backurl) ;
		}
		$storestime = $storesinfo->storestime ;
		
		$where = ' i.report_id = '.$id.' and i.storestime = '.$storestime;
		$columns='i.id,i.quota_id,i.quota_total,i.quota_value,i.report_user_id,q.type,'.
			'group_concat(distinct(u.name)) as report_user_name,q.name as quota_name,q.type as quota_type';

		$dataList = new \stdClass();
		$offset = ($page-1)*$pagesize;
		$items = $this->modelsManager->createBuilder()
									->columns($columns)
									->addFrom('ScshuxCms\Dacang\Model\ReportStoresModel','i')
									->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.report_user_id = u.id','u')
									->leftJoin('ScshuxCms\Dacang\Model\StoreQuotaModel','i.quota_id = q.id','q')
									->groupBy('i.quota_id')
									->where($where)
									->orderBy('i.id desc')
									->limit($pagesize,$offset)
									->getQuery()
									->execute() ;
		$dataList->items = $items;
		$this->view->setVar('quotatype', Constants::getQuotaType()) ;
		return $dataList;

	}



	/**
	 *
	 * @desc 获取报表列表
	 * @date 2017年4月1日
	 */
	protected  function _getDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$filter = array();

		$where = ' s.id > 0 and r.company_id = '.$this->companyId;
		$andwhere=UserManageRoleModel::factory()->getWhereByUserManageRole('u.department_id');
		$where = $andwhere?$where.' and '.$andwhere:$where;
		
		
		//导出详细所做的处理
		if($_REQUEST['id'] && $_REQUEST['act']!='remove'){
			$where .= " and  s.report_id=".intval($_REQUEST['id']);
		}
		if($_REQUEST['sid'] && $_REQUEST['act']!='remove'){
			$sid = intval($_REQUEST['sid']);
			$storesinfo = ReportStoresModel::findFirst($sid);
			$where .= " and  s.storestime=".$storesinfo->storestime;
		}
		//根据人员名称
		if($_REQUEST['name']){
			$filter['name'] = trim($_REQUEST['name']);
			$where .= " and  u.name  like '%{$filter['name']}%'";
		}
		//根据部门
		if($_REQUEST['department_id']){
			$filter['department_id'] = intval($_REQUEST['department_id']);
			$where .= " and  u.department_id  = ".$filter['department_id'];
		}
		//开始时间
		if($_REQUEST['start_time']){
			$filter['start_time'] = trim($_REQUEST['start_time']);
			$where .= " and  s.storestime > ".strtotime($filter['start_time'].'00:00:00');
		}
		//结束时间
		if($_REQUEST['end_time']){
			$filter['end_time'] = trim($_REQUEST['end_time']);
			$where .= " and  s.storestime  < ".strtotime($filter['end_time'].'23:59:59');
		}

		$dataList = new \stdClass();

		/*统计*/
		$countInfo = ReportModel::factory()->getModelsManager()->createBuilder()
											->addFrom('ScshuxCms\Dacang\Model\ReportModel','r')
											->leftJoin('ScshuxCms\Dacang\Model\ReportStoresModel','r.id = s.report_id','s')
											->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','s.user_id = u.id','u')
											->columns('r.id')
											->groupBy('r.id,s.storestime')
											->where($where)
											->getQuery()
											->execute();
		
		$dataList->count       = $countInfo->count();
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		$dataList->filter      = $filter ;
		/*加载数据*/


		$offset = ($page-1)*$pagesize;
		$columns = 'u.name as uname,u.department_id,r.name as reportname,r.id,r.remark,s.storestime as created,';
		$columns.= 's.id as sid';
		
		$items = ReportModel::factory()->getModelsManager()->createBuilder()
										->columns($columns)
										->addFrom('ScshuxCms\Dacang\Model\ReportModel','r')
										->leftJoin('ScshuxCms\Dacang\Model\ReportStoresModel','r.id = s.report_id','s')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','s.user_id = u.id','u')
										->groupBy('r.id,s.storestime')
										->where($where)
										->orderBy('r.id desc')
										->limit($pagesize,$offset)
										->getQuery()
										->execute()
										->toArray();
	
		//计算总分
		if($items)
		{
			foreach ($items as &$item)
			{
				$item['totalpoint'] = ReportStoresModel::factory()->getTotalPoint($item['id'],$item['sid']);
			}
		}	
		
		$this->view->setVar('reportstatus',Constants::reportStatus()) ;
		$this->view->setVar('departlist',  DepartmentModel::TreeDepartList($this->companyId)) ;
		$this->view->setVar('departone',  DepartmentModel::departListOne($this->companyId)) ;

		$dataList->items = $items;
		return $dataList;

	}


	/**
	 *
	 * @desc	获取具体评分情况
	 * @date	2017年5月3日
	 */
	protected function _getShowPointList()
	{
		$request  = $this->request ;
		$reportId = $request->get('reportId') ;
		$quotaId  = $request->get('quotaId') ;
		$sid  = $request->get('sid') ;

		//没有传递page参数的时候 表示是第一次进入 必须要有参数reportId and quotaId  有传page参数的时候 是调用的phalcon自带的分页 可以从session里面获取这两个参数
		$session = FactoryDefault::getDefault()->get('session');
		if(!$_REQUEST['page'])
		{
			if (!$reportId || !$quotaId)
				return  $dataList ;
					
				$session->set('showpointlistparam',$reportId.'_'.$quotaId.'_'.$sid);
		}
		else
		{
			$param=explode('_', $session->get('showpointlistparam'));
			$reportId = $param[0];
			$quotaId = $param[1];
			$sid = $param[2];
		}
		
		$dataList = new \stdClass() ;
		if(!$reportId || !$quotaId || !$sid)
		{
			return  $dataList ;
		}

		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$offset = ($page-1)*$pagesize;

		//获取数据列表
		$dataList = ReportModel::factory()->showStoresPoint($reportId,$quotaId,$sid,$this->companyId,$page,$pagesize);

		return $dataList ;
	}


	/**
	 * 删除归档数据
	 * @param  $ids
	 */
	protected  function  _remove($ids)
	{
		if($ids){
			//为防止把同一个报表的历史归档记录都删掉了      先找出再删掉
			$idsArr=explode(',', $ids);
			foreach ($idsArr as $id)
			{
				$storesReport=ReportStoresModel::factory()->findFirst($id);
				if (!$storesReport)
				{
					continue;
				}
				$reportId=$storesReport->report_id;
				$storestime=$storesReport->storestime;
				ReportStoresModel::factory()->deleteBySql('report_id='.$reportId.' and storestime='.$storestime);
			}
		}
	}
	/**
	 *
	 * @desc 获取指标点评列表
	 * @date 2017年4月1日
	 */
	protected  function _getCommentDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$filter =array();
		$reportId= intval($this->request->get('report_id'));
		$quotaId = intval($this->request->get('quota_id'));
		$sid=intval($this->request->get('sid'));
	
		$dataList = new \stdClass();
		if (!$reportId || !$quotaId || !$sid)
		{
			return $dataList;
		}
		
		$reportStores = ReportStoresModel::factory()->findFirst($sid);
		if (!$reportStores)
		{
			return $reportStores;
		}
		$storestime=$reportStores->storestime;
		
		$where .= ' qid = '.$quotaId.' and rid='.$reportId.' and storestime='.$storestime;
		$filter['report_id'] = $reportId;
		$filter['quota_id']  = $quotaId;
		$filter['sid'] = $sid;
		
		if($_REQUEST['keyword']){
			$filter['keyword'] = trim($_REQUEST['keyword']);
			$where .= " and  content  like '%{$filter['keyword']}%'";
		}
	
		$dataList = new \stdClass();
	
		/*统计*/
		$countInfo = StoreQuotaCommentModel::query()
		->where($where)
		->columns('count(*) as num')
		->execute();
	
		$dataList->count       = isset($countInfo[0]) ? $countInfo[0]->num : 0 ;
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		$dataList->filter      = $filter ;
		/*加载数据*/
	
		$offset = ($page-1)*$pagesize;
		$items = StoreQuotaCommentModel::query()
		->where($where)
		->orderBy('id desc')
		->limit($pagesize,$offset)
		->execute()
		->toArray();
	
		$dataList->items = $items;
		return $dataList;
	
	}



}