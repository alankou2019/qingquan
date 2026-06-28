<?php
/**
 * 前台 报表
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
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Core\Helper\Dding;
use ScshuxCms\Dacang\Model\ExtraReportModel;
use ScshuxCms\Dacang\Model\ExtraReportItemModel;
use ScshuxCms\Dacang\Model\ExtraReportDescModel;
use ScshuxCms\Dacang\Model\ExtraStoresReportItemModel;
use ScshuxCms\Dacang\Model\ExtraReportTplModel;
use ScshuxCms\User\Model\UserManageRoleModel;
use ScshuxCms\Dacang\Model\QuotaCommentModel;
use ScshuxCms\Dacang\Model\StoreQuotaCommentModel;
class ReportController extends FrontendBaseController
{

	/**
	 *
	 * @desc 新建KPI考评表
	 * @date 2017年4月21日
	 */
	public function newAction()
	{
		$backurl = $this->getHelper()->createUrl(array('p'=>'firm/staff'));  //默认跳回到人员列表

		$request = $this->request ;
		$userid  = $request->get('userid') ;

		if(!$userid)
		{
			Utils::showMsg('请选择需要生成KPI考评表的人员', $backurl) ;
		}
		
		//获取允许的每人可以建立的报表数目
		$companyinfo = CompanyModel::findFirst($this->companyId) ;
		$reportlimit = $companyinfo->reportlimit ;
		
		$hasreportnum = ReportModel::factory()->getHasReport($userid) ;
		if($hasreportnum >= $reportlimit)
		{
			Utils::showMsg('可生成考评表的数量已达限制，请联系管理员', $backurl) ;
		}
		
		//根据userids  获取到用户名
		$userstr = CompanyUserModel::getUserByids($userid,$this->companyId) ;
		$this->view->setVar('userid', $userid);
		$this->view->setVar('userstr', $userstr);
		$this->view->setVar('datearr', range(1, 31));
	}


	
	/**
	 *
	 * @desc KPI考评保存指标
	 * @date 2017年4月21日
	 */
	public function saveAction()
	{
		
		$request = $this->request ;
		$name    = $request->get('name') ; //报表名称
		$userid  = $request->get('userid') ;
		$istpl   = $request->get('istpl') ;
		$remark  = $request->get('remark') ;
		$quotaids= $request->get('quotaids') ;
		$quotaval= $request->get('quotavalue') ;
		$reportuserid = $request->get('reportuserid') ;
		$reportuserweight = $request->get('reportuserweight') ;   	//考核人权重
		//必要验证
		$this->checkvalue();
		
		//判断是新增还是修改
		$id = $request->get('id') ;
		if($id)
		{
			
			$res = ReportModel::factory()->savaReport($request);
			if(!$res)
			{
				$this->sendErrorResult('保存失败 ，请稍后再试') ;
			}
		}
		else 
		{
			//添加到报表库
			$res = ReportModel::factory()->addreport($request,$this->companyId) ;
		}
		
		if($res)
		{
			$backstr = ''; 	  //预定义返回的信息
			
			//判断是否需要添加模版
			if($istpl == 1)
			{
				//判断可保存的模版数 是否已经达到限制
				$islimit = ReportTplModel::factory()->checkTplNum($this->companyId);
				
				//如果还未达到限制的模版条数  则添加模版
				if(!$islimit)
				{
					$reportId = ReportTplModel::factory()->addreporttpl($request,$this->companyId);
					if($reportId)
					{
						//添加报表模版每项指标
						$this->saveReportTplItem($quotaids, $quotaval, $reportuserid, $reportId, $reportuserweight);
					}
				}
				else
				{
					$backstr = '模版已达到数量限制，请联系管理员';
				}
			}
			
			//保存报表每项指标
			$this->saveReportItem($quotaids, $quotaval, $userid, $reportuserid, $res, $reportuserweight) ;
			//保存报表用户对应表
			$this->addreportuser($userid,$res);
			
			//确定报表id
			if (!$id)
			{
				$id = ReportModel::factory()->id ;
			}
			
			
			$this->sendSuccessResult('KPI考评表建立成功'.$backstr) ;
		}
		else
		{
			$this->sendErrorResult('新建报表失败，请稍后再试') ;
		}
	}

	
	
	

	/**
	 *
	 * desc 报表列表
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
		
		if($dataList->items)
		{
			foreach ($dataList->items as $key=>$val)
			{
				//如果当前报表是未完成的状态  则查找评分人的信息
				$statusDesc='<span class="blue">报表已经完成</span>';
				if ($val['status']==0)
				{
					$statusDesc=ReportItemModel::factory()->getReportUserDesc($val['id']);
				}
				$dataList->items[$key]['statusdesc']=$statusDesc;
			}
		}
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('report','list');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}

	
	
	
	/**
	 *
	 * @desc 编辑报表
	 * @date 2017年4月24日
	 */
	public function editAction()
	{
		$backurl = $this->getHelper()->createUrl(array('p'=>'report/list'));  //默认跳回到人员列表
	
		$request = $this->request ;
		$id  = $request->get('id') ;
	
		if(!$id)
		{
			Utils::showMsg('请选择报表', $backurl) ;
		}
	
		$id = intval($id) ;
		$reportinfo = ReportModel::findFirst($id) ;
		if(!$reportinfo)
		{
			Utils::showMsg('报表数据不存在', $backurl) ;
		}
		
		if($reportinfo->ispoint == 1)
		{
			Utils::showMsg('此考核表正在进行考核，不能进行修改', $backurl) ;
		}
		
		$reportuser = ReportUserModel::findFirst('report_id = '.$reportinfo->id) ;
		if(!$reportuser)
		{
			Utils::showMsg('错误，不能进行修改', $backurl) ;
		}
		
		
		$this->view->setVar('item', $reportinfo) ;
		$this->view->setVar('userstr', $this->_getUserStr($id)) ;
		$this->view->setVar('reportquotalist', $this->_getReportItemDataList()) ;
		$this->view->setVar('quotatype', Constants::getQuotaType()) ;
		$this->view->setVar('userid', $reportuser->user_id) ;
		$this->view->setVar('datearr', range(1, 31));
		
	}
	
	
	
	/**
	 *
	 * @desc 报表详情
	 * @date 2017年4月24日
	 */
	public function detailAction()
	{
		$backurl = $this->getHelper()->createUrl(array('p'=>'report/list'));  //默认跳回到人员列表

		$request = $this->request ;
		$id  = $request->get('id') ;

		if(!$id)
		{
			Utils::showMsg('请选择报表', $backurl) ;
		}

		$id = intval($id) ;
		$reporttem = ReportModel::findFirst($id) ;
		if(!$reporttem)
		{
			Utils::showMsg('报表数据不存在', $backurl) ;
		}

		$this->view->setVar('item', $reporttem) ;
		$this->view->setVar('userstr', $this->_getUserStr($id)) ;
		$this->view->setVar('reportquotalist', $this->_getReportItemDataList());
		$this->view->setVar('quotatype', Constants::getQuotaType());
	}


	/**
	 *
	 * @desc 模版列表
	 * @date 2017年4月24日
	 */
	public function reportTplAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;

		if($act == 'remove'){
			$this->_removetpl($_REQUEST['id']);
		}

		$dataList = $this->_getTplDataList();

		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('report','reporttpl');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}


	/**
	 *
	 * @desc 选择 模版
	 * @date 2017年4月24日
	 */
	public function simpleReportTplAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;

		$dataList = $this->_getTplDataList();


		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('report','simplereporttpl');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}


	/**
	 *
	 * @desc 根据模版id  获取对应的模版指标
	 * @date 2017年4月24日
	 */
	public function reportTplItemAction()
	{
		$id = $this->request->get('id') ;
		if(!$id)
		{
			$this->sendErrorResult('参数错误') ;
		}

		$id = intval($id) ;
		$reporttpl = ReportTplModel::findFirst($id) ;
		if(!$reporttpl)
		{
			$this->sendErrorResult('模版不存在') ;
		}

		$dataList = $this->_getItemDataList() ;

		if(!$dataList)
		{
			$this->sendErrorResult('模版指标为空，请从新选择') ;
		}

		//进行数据渲染
		$this->view->setVar('dataList', $dataList);
		$this->view->setMainView(false);
		$this->view->start();
		$this->view->setVar('full_page',0);
		$this->view->render('report','reporttplitem');
		$this->view->finish();
		$dataList->content = $this->view->getContent();
		$this->sendSuccessResult($dataList);

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
	 * @desc	归档操作  将报表的打分情况给存储归档 并且清除报表的打分情况
	 * @date	2017年5月3日
	 */
	public function storesAction()
	{
		$isajax = $this->request->isAjax() ;
		if ($isajax)
		{
			$id = $this->request->get('id') ;
			if (!$id)
			{
				$this->sendErrorResult('参数错误') ;
			}
			//批量进行操作
			$arrid = explode(',', $id) ;
			foreach ($arrid as $id)
			{
				$id = intval($id) ;
				$reportinfo = ReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				//未公示的  不能进行归档
				if ($reportinfo->ispub!=1)
				{
					continue;
				}
				$reportuser = ReportUserModel::findFirst('report_id	= '.$id) ;
				if (!$reportuser)
				{
					continue ;
				}
				
				if ($reportuser->state != 1)
				{
					continue ;
				}

				//获取一次时间  然后以参数形式传递过去 保证两长表的storestime 一致 
				$nowtime = Helper::factory()->getTime()->gmtime() ;
				//查询具体的打分信息  并且进行归档
				$saveStores = ReportStoresModel::factory()->saveStores($id,$nowtime) ;
				if (!$saveStores)
				{
					continue ;
				}
				
				//清空具体打分信息
				$clearpoint = ReportItemModel::factory()->clearPoint($id);
				if(!$clearpoint)
				{
					continue ;
				}
				
				//指标点评归档
				$saveComment = StoreQuotaCommentModel::factory()->saveComment($id,$nowtime);
				if (!$saveComment)
				{
					continue;
				}
				//清空指标点评
				$clearComment = QuotaCommentModel::factory()->clearComment($id);
				if (!$clearComment)
				{
					continue;
				}
				//修改此条报表的状态
				$reportuser->state = 0 ;
				$reportuser->save() ;
					
				//修改公示状态
				$reportinfo->ispub = 0 ;
				$reportinfo->ispoint = 0 ;
				$reportinfo->save();
			}
			
			$this->sendSuccessResult('操作成功') ;
		}
		else
		{
			$this->sendErrorResult('请求方法错误') ;
		}
	}
	
	
	
	/**
	 *
	 * @desc	全员归档操作 
	 * @date	2017年5月3日
	 */
	public function allStoresAction()
	{
		$isajax = $this->request->isAjax() ;
		if ($isajax)
		{
			$allReport=$this->_getAllReportid();
			if (empty($allReport))
			{
				$this->sendSuccessResult('操作成功');
			}
			foreach ($allReport as $id)
			{
				$id = intval($id) ;
				$reportinfo = ReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				//未公示的  不能进行归档
				if ($reportinfo->ispub!=1)
				{
					continue;
				}
				$reportuser = ReportUserModel::findFirst('report_id	= '.$id) ;
				if (!$reportuser)
				{
					continue ;
				}
	
				if ($reportuser->state != 1)
				{
					continue ;
				}
	
				//获取一次时间  然后以参数形式传递过去 保证两长表的storestime 一致
				$nowtime = Helper::factory()->getTime()->gmtime() ;
				//查询具体的打分信息  并且进行归档
				$saveStores = ReportStoresModel::factory()->saveStores($id,$nowtime) ;
				if (!$saveStores)
				{
					continue ;
				}
	
				//清空具体打分信息
				$clearpoint = ReportItemModel::factory()->clearPoint($id) ;
				if(!$clearpoint)
				{
					continue ;
				}
	
				//指标点评归档
				$saveComment = StoreQuotaCommentModel::factory()->saveComment($id,$nowtime);
				if (!$saveComment)
				{
					continue;
				}
				//清空指标点评
				$clearComment = QuotaCommentModel::factory()->clearComment($id);
				if (!$clearComment)
				{
					continue;
				}
	
				//修改此条报表的状态
				$reportuser->state = 0 ;
				$reportuser->save() ;
					
				//修改公示状态
				$reportinfo->ispub = 0 ;
				$reportinfo->ispoint = 0 ;
				$reportinfo->save();
			}
				
			$this->sendSuccessResult('操作成功') ;
		}
		else
		{
			$this->sendErrorResult('请求方法错误') ;
		}
	}

	
	
	/**
	 *
	 * @desc	考评操作
	 * @date	2017年5月3日
	 */
	public function pointingAction()
	{
		$isajax = $this->request->isAjax() ;
		if ($isajax)
		{
			$id = $this->request->get('id') ;
			if (!$id)
			{
				$this->sendErrorResult('参数错误') ;
			}
			$arrid = explode(',', $id);
			foreach ($arrid as $id)
			{
				$id = intval($id) ;
					
				$reportinfo = ReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				if($reportinfo->ispoint == 1)
				{
					continue ;
				}
					
				//调用钉钉接口 发送消息给用户
				$touser = ReportItemModel::factory()->getDdUserid($id) ;
				if(!$touser)
				{
					continue ;
				}
				
				//获取被考核人的姓名
				$reportusername = ReportItemModel::factory()->getReportUserName($id);
				if($reportusername)
				{
					$tipstr = '亲， '.$reportusername.'的'.$reportinfo->name.'请您评分了，快去看看吧 ' ;
				}
				else 
				{
					$tipstr = '亲， '.$reportinfo->name.'  请您评分，快去看看吧 ' ;
				}
				
				//测试环境不发送消息
				$appenv=Helper::factory()->getConfig('application_env');
				if (empty($appenv) || $appenv!='dev')
				{
					//不判断消息成功与否
					$res = Dding::factory()->sendMsg($this->companyId,$touser,$tipstr);
				}
				
				
				$reportinfo->ispoint = 1;
				$reportinfo->save() ;
			}
			$this->sendSuccessResult('操作成功') ;
		}
		else
		{
			$this->sendErrorResult('请求方法错误') ;
		}
	}
	
	
	
	/**
	 *
	 * @desc	全员考评
	 * @date	2017年5月3日
	 */
	public function allPointingAction()
	{
		$isajax = $this->request->isAjax() ;
		if ($isajax)
		{
			$allReport = $this->_getAllReportid();
			if (empty($allReport))
			{
				$this->sendSuccessResult('操作成功');
			}
			foreach ($allReport as $id)
			{
				$id = intval($id) ;
				$reportinfo = ReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				if($reportinfo->ispoint == 1)
				{
					continue ;
				}
					
				//调用钉钉接口 发送消息给用户
				$touser = ReportItemModel::factory()->getDdUserid($id) ;
				if(!$touser)
				{
					continue ;
				}
	
				//获取被考核人的姓名
				$reportusername = ReportItemModel::factory()->getReportUserName($id);
				if($reportusername)
				{
					$tipstr = '亲， '.$reportusername.'的'.$reportinfo->name.'请您评分了，快去看看吧 ' ;
				}
				else
				{
					$tipstr = '亲， '.$reportinfo->name.'  请您评分，快去看看吧 ' ;
				}
	
				//测试环境不发送消息
				$appenv=Helper::factory()->getConfig('application_env');
				if (empty($appenv) || $appenv!='dev')
				{
					//不判断消息成功与否
					$res = Dding::factory()->sendMsg($this->companyId,$touser,$tipstr);
				}
				$reportinfo->ispoint = 1;
				$reportinfo->save() ;
			}
			$this->sendSuccessResult('操作成功') ;
		}
		else
		{
			$this->sendErrorResult('请求方法错误') ;
		}
	}
	
	
	
	
	
	/**
	 *
	 * @desc	撤销
	 * @date	2017年5月3日
	 */
	public function comebackAction()
	{
		$isajax = $this->request->isAjax() ;
		if ($isajax)
		{
			$id = $this->request->get('id') ;
			if (!$id)
			{
				$this->sendErrorResult('参数错误') ;
			}
			$arrid = explode(',', $id);
			foreach ($arrid as $id)
			{
				$id = intval($id) ;
				
				$reportinfo = ReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				//只能撤销已经在执行的考评
				if($reportinfo->ispoint != 1)
				{
					continue ;
				}
				//已经公示的不进行撤销
				if ($reportinfo->ispub==1)
				{
					continue;
				}
				
				//重置考评数据
				$res = ReportItemModel::factory()->comeback($id);
	
				$reportinfo->ispoint = 0;
				$reportinfo->save() ;
			}
			$this->sendSuccessResult('操作成功') ;
		}
		else
		{
			$this->sendErrorResult('请求方法错误') ;
		}
	}
	
	
	
	/**
	 *
	 * @desc	重置评分
	 * @date	2026年6月18日
	 */
	public function resetPointAction()
	{
		$isajax = $this->request->isAjax() ;
		if ($isajax)
		{
			$id = $this->request->get('id') ;
			if (!$id)
			{
				$this->sendErrorResult('参数错误') ;
			}
			$arrid = explode(',', $id);
			$success = 0;
			foreach ($arrid as $id)
			{
				$id = intval($id) ;
				if (!$id)
				{
					continue ;
				}
				
				$reportinfo = ReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				//只能重置已经开始、但尚未归档的考评表
				if($reportinfo->ispoint != 1)
				{
					continue ;
				}
				$reportuser = ReportUserModel::findFirst('report_id = '.$id) ;
				if (!$reportuser)
				{
					continue ;
				}
				
				$res = ReportItemModel::factory()->comeback($id);
				if (!$res)
				{
					continue ;
				}
				ExtraReportItemModel::factory()->clearPoint($id);
				QuotaCommentModel::factory()->clearComment($id);
				
				$reportuser->state = 0 ;
				$reportuser->save() ;
				
				$reportinfo->ispub = 0 ;
				$reportinfo->ispoint = 1 ;
				$reportinfo->save() ;
				$success++;
			}
			if ($success < 1)
			{
				$this->sendErrorResult('没有可重置的考核表') ;
			}
			$this->sendSuccessResult('重置成功') ;
		}
		else
		{
			$this->sendErrorResult('请求方法错误') ;
		}
	}
	
	
	
	
	/**
	 *
	 * @desc	公示
	 * @date	2017年5月3日
	 */
	public function pointedAction()
	{
		$isajax = $this->request->isAjax() ;
		if ($isajax)
		{
			$id = $this->request->get('id') ;
			if (!$id)
			{
				$this->sendErrorResult('参数错误') ;
			}
			$arrid = explode(',', $id) ;
			foreach ($arrid as $id)
			{
				$id = intval($id) ;
				
				$reportinfo = ReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				if($reportinfo->ispoint == 0)
				{
					continue ;
				}
				if($reportinfo->ispub == 1)
				{
					continue ;
				}
				//判断考核表是否已经全部打分完成
				$reportuser = ReportUserModel::findFirst('report_id = '.$id) ;
				if(!$reportuser)
				{
					continue ;
				}
					
				if($reportuser->state != 1)
				{
					continue ;
				}
					
				//获取被考核人的钉钉userid
				$dduseridobj = CompanyUserModel::findFirst($reportuser->user_id) ;
				$dduserid = $dduseridobj->dingding_user_id ;
				
				//测试环境不发送消息
				$appenv=Helper::factory()->getConfig('application_env');
				if (empty($appenv) || $appenv!='dev')
				{
					//调用钉钉接口 通知被考核人
					$res = Dding::factory()->sendMsg($this->companyId,$dduserid,'亲，您的'.$reportinfo->name.'已经完成咯  ，快去看看吧') ;
				}
				
				
				//修改是否已经公示字段
				$reportinfo->ispub = 1 ;
				$reportinfo->save() ;
			}
			$this->sendSuccessResult('设置成功') ;
		}
		else
		{
			$this->sendErrorResult('请求方法错误') ;
		}
	}
	
	
	
	/**
	 *
	 * @desc	全员公示
	 * @date	2017年5月3日
	 */
	public function allPointedAction()
	{
		$isajax = $this->request->isAjax() ;
		if ($isajax)
		{
			$allReport=$this->_getAllReportid();
			if (empty($allReport))
			{
				$this->sendSuccessResult('操作成功');
			}
			foreach ($allReport as $id)
			{
				$id = intval($id) ;
	
				$reportinfo = ReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				if($reportinfo->ispoint == 0)
				{
					continue ;
				}
				if($reportinfo->ispub == 1)
				{
					continue ;
				}
				//判断考核表是否已经全部打分完成
				$reportuser = ReportUserModel::findFirst('report_id = '.$id) ;
				if(!$reportuser)
				{
					continue ;
				}
					
				if($reportuser->state != 1)
				{
					continue ;
				}
					
				//获取被考核人的钉钉userid
				$dduseridobj = CompanyUserModel::findFirst($reportuser->user_id) ;
				$dduserid = $dduseridobj->dingding_user_id ;
	
				//测试环境不发送消息
				$appenv=Helper::factory()->getConfig('application_env');
				if (empty($appenv) || $appenv!='dev')
				{
					//调用钉钉接口 通知被考核人
					$res = Dding::factory()->sendMsg($this->companyId,$dduserid,'亲，您的'.$reportinfo->name.'已经完成咯  ，快去看看吧') ;
				}
	
	
				//修改是否已经公示字段
				$reportinfo->ispub = 1 ;
				$reportinfo->save() ;
			}
			$this->sendSuccessResult('设置成功') ;
		}
		else
		{
			$this->sendErrorResult('请求方法错误') ;
		}
	}
	
	
	
	
	
	/**
	 * @desc	获取模版的加减分
	 * @param			
	 * @return			
	 */
	public function getExtraDataAction()
	{
		$isajax = $this->request->isAjax() ;
		$ispost = $this->request->isPost() ;
		if ($isajax && $ispost)
		{
			$tplid = $this->request->get('tplid') ;
			if (!$tplid)
			{
				$this->sendErrorResult('参数错误') ;
			}
			
			$tplid = intval($tplid) ;
			$data = ExtraReportTplModel::getExtraData($tplid) ;
			if ($data)
			{
				$this->sendSuccessResult($data) ;
			}
			else 
			{
				$this->sendErrorResult('无加减分') ;
			}
		}
	}
	
	/**
	 *
	 * @desc 新建指标时   数据验证
	 * @date 2017年4月24日
	 */
	protected function checkvalue()
	{
		$request = $this->request ;
		$name    = $request->get('name') ; //报表名称
		$userid  = $request->get('userid') ;
		$istpl   = $request->get('istpl') ;
		$remark  = $request->get('remark') ;
		$quotaids= $request->get('quotaids') ;
		$quotaval= $request->get('quotavalue') ;
		$reportuserid = $request->get('reportuserid') ;
		$sum    = $request->get('sum') ;
		$reportuserweight = $request->get('reportuserweight') ;   //考核人权重
		//必要验证
		if(!$name)
		{
			$this->sendErrorResult('请填写报表名称') ;
		}

		if(!$userid)
		{
			$this->sendErrorResult('人员不能为空') ;
		}
		if(!is_numeric($sum))
		{
			$this->sendErrorResult('指标权重总额必须为数字') ;
		}
		if(!$sum)
		{
			//设置权重默认值为100
			$sum = 100 ;
		}

		//指标个数
		$quotanum = count($quotaids) ;

		if($quotanum < 1)
		{
			$this->sendErrorResult('指标不能为空，请选择指标') ;
		}

		$uniqiquotanum = count(array_unique($quotaids));
		if($quotanum != $uniqiquotanum)
		{
			$this->sendErrorResult('请去掉重复的指标') ;
		}

		//判断每一个指标值
		foreach ($quotaids as $quotaid)
		{
			if(!$quotaid)
			{
				$this->sendErrorResult('请补充完整所有指标') ;  break ;
			}

		}
		

		//判断考核人员
		if(count($reportuserid) < $quotanum)
		{
			$this->sendErrorResult('请将考核人员填写完整') ;
		}
		
		//判断每一个指标的考核人员
		foreach ($reportuserid as $ruser)
		{
			if(!$ruser)
			{
				$this->sendErrorResult('请补充完整指标的考核人员') ;  break ;
			}
		}
		
		//判断指标权重
		if(count($quotaval) < $quotanum)
		{
			$this->sendErrorResult('请将指标权重填写完整') ;
		}
		
		//判断每一个指标的权重
		foreach ($quotaval as $key=>$qval)
		{
			if(!$qval)
			{
				//判断指标是否是不参与权重的指标
				$quotaType = QuotaModel::factory()->quotaType($quotaids[$key]);
				if ($quotaType != Helper::factory()->getExtratype())
				{
					$this->sendErrorResult('请补充完整指标的权重值') ;  break ;
				}
			}
		}
		
		$point = '' ;
		foreach ($quotaval as $val)
		{
			$point += floatval($val) ;
		}
		
		//判断各项指标的权重值总和是否等于所设置值
		if($point != $sum)
		{
			$this->sendErrorResult('各项指标权重相加必须等于权重总额，请从新设置指标权重') ;
		}
		
		
		//判断考核人权重
		if(count($reportuserweight) < $quotanum)
		{
			$this->sendErrorResult('请将考核人员填写完整!') ;
		}
		
		//判断每一个指标的考核人员
		foreach ($reportuserweight as $rwuser)
		{
			if(!$rwuser)
			{
				$this->sendErrorResult('请补充完整指标的考核人员') ;  break ;
			}
		
			//判断多个考核人的权重相加是否等于100
			$arrweight = explode(',', $rwuser) ;
			$sumweight = 0 ;
			foreach($arrweight as $strweight)
			{
				$sumweight += floatval($strweight) ;
			}
				
			if($sumweight != 100)
			{
				$this->sendErrorResult('考核人员的权重设置有误，请从新设置') ;  break ;
			}
		}
	}



	/**
	 *
	 * @desc 保存报表每项指标
	 * @date 2017年4月21日
	 */
	protected function saveReportItem($quotaids,$quotaval,$userid,$reportuserid,$reportId,$reportuserweight)
	{		
		$reportId = intval($reportId) ;
		//根据 reportId  判断是新增  还是 修改
		$istrue = ReportItemModel::findFirst('report_id = '.$reportId) ;

		//记录已经存在  表示更新
		if($istrue)
		{
			//删除原来的数据
			$delwhere = 'report_id = '.$reportId;
			ReportItemModel::deleteAll($delwhere) ;
		}
		$sql = "insert into \ScshuxCms\Dacang\Model\ReportItemModel (quota_id,quota_total,quota_value,user_id,report_user_id,report_id) ";
		$sql.= 'values (:quota_id:,:quota_total:,:quota_value:,:user_id:,:report_user_id:,:report_id:)';
		foreach ($quotaids as $key=>$value)
		{
			//因为一个指标可以选择多个评分人   所以这里循环添加
			$arrreportuser = explode(',', $reportuserid[$key]) ;
			$arrreportuserweight = explode(',', $reportuserweight[$key]) ;
			foreach ($arrreportuser as $k=>$intreportuser)
			{
				$this->modelsManager->executeQuery($sql,array(
						'quota_id'    => $value,
						'quota_total' => $arrreportuserweight[$k],
						'quota_value' => $quotaval[$key],
						'user_id'     => $userid,
						'report_user_id' => $intreportuser,
						'report_id'   => $reportId
				)) ;
			}
		}
		
	}


	
	
	
	
	
	/**
	 *
	 * @desc 保存报表-用户对应表
	 * @date 2017年4月24日
	 */
	protected function addreportuser($userid,$reportId)
	{
		$userid = intval($userid);
		$reportId = intval($reportId) ;
		
		//判断是添加还是修改  只有当新添加的时候  才生成记录 
		$reportuser = ReportUserModel::findFirst('user_id = '.$userid.' and report_id = '.$reportId) ;
		if(!$reportuser)
		{
			$sql = "insert into \ScshuxCms\Dacang\Model\ReportUserModel (user_id,report_id,created) ";
			$sql.= 'values (:user_id:,:report_id:,:created:)';
				
			$this->modelsManager->executeQuery($sql,array(
					'user_id'    => $userid,
					'report_id' => $reportId,
					'created'   => Helper::factory()->getTime()->gmtime()
			)) ;
		}
	}

	
	


	/**
	 *
	 * @desc 保存报表模版每项指标
	 * @date 2017年4月21日
	 */
	protected function saveReportTplItem($quotaids,$quotaval,$reportuserid,$reportId,$reportuserweight)
	{
		$sql = "insert into \ScshuxCms\Dacang\Model\ReportTplItemModel (quota_id,quota_total,quota_value,report_user_id,report_id) ";
		$sql.= 'values (:quota_id:,:quota_total:,:quota_value:,:report_user_id:,:report_id:)';

		foreach ($quotaids as $key=>$value)
		{
			//因为一个指标可以选择多个评分人   所以这里循环添加
			$arrreportuser = explode(',', $reportuserid[$key]) ;
			$arrreportuserweight = explode(',', $reportuserweight[$key]) ;
			foreach ($arrreportuser as $k=>$intreportuser)
			{
				$this->modelsManager->executeQuery($sql,array(
						'quota_id'    => $value,
						'quota_total' => $arrreportuserweight[$k],
						'quota_value' => $quotaval[$key],
						'report_user_id' => $intreportuser,
						'report_id'   => $reportId,
				)) ;
			}
		}
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

		$where = 'i.id >0  and  r.company_id = '.$this->companyId;
		$andwhere=UserManageRoleModel::factory()->getWhereByUserManageRole('u.department_id');
		$where = $andwhere?$where.' and '.$andwhere:$where;

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
		//根据考评状态
		if ($_REQUEST['reportstatus']!=null)
		{
			$filter['reportstatus'] = intval($_REQUEST['reportstatus']);
			$where .= " and  ru.state  = ".$filter['reportstatus'];
		}
		$dataList = new \stdClass();

		/*统计*/
		$countInfo = ReportModel::factory()->getModelsManager()->createBuilder()
											->columns('count(distinct(u.id)) as num')
											->addFrom('ScshuxCms\Dacang\Model\ReportModel','r')
											->leftJoin('ScshuxCms\Dacang\Model\ReportUserModel','r.id = ru.report_id','ru')
											->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','ru.user_id = u.id','u')
											->leftJoin('ScshuxCms\Dacang\Model\ReportItemModel','r.id = i.report_id','i')
											->groupBy('r.id,u.id')
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
		$columns='u.name as uname,u.department_id,r.name as reportname,r.id,r.remark,r.created,'.
		'ru.state as status,r.ispoint,r.ispub';
		$items = ReportModel::factory()->getModelsManager()->createBuilder()
										->columns($columns)
										->addFrom('ScshuxCms\Dacang\Model\ReportModel','r')
										->leftJoin('ScshuxCms\Dacang\Model\ReportUserModel','r.id = ru.report_id','ru')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','ru.user_id = u.id','u')
										->leftJoin('ScshuxCms\Dacang\Model\ReportItemModel','r.id = i.report_id','i')
										->groupBy('r.id,u.id')
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
				$item['totalpoint'] = ReportItemModel::factory()->getTotalPoint($item['id']);
			}
		}
		$this->view->setVar('reportstatus',Constants::reportStatus()) ;
		$this->view->setVar('departlist',  DepartmentModel::TreeDepartList($this->companyId));
		$this->view->setVar('departone',  DepartmentModel::departListOne($this->companyId)) ;
		$dataList->items = $items;
		
		return $dataList;

	}

	
	
	

	/**
	 *
	 * @desc 获取模版列表
	 * 模版只跟公司相关
	 * @date 2017年4月1日
	 */
	protected  function _getTplDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$filter = array();

		$where = ' company_id = '.$this->companyId;

		//根据模版名称
		if(isset($_REQUEST['name'])){
			$filter['name'] = trim($_REQUEST['name']);
			$where .= " and  name  like '%{$filter['name']}%'";
		}


		$dataList = new \stdClass();

		/*统计*/
		$countInfo = $this->modelsManager->createBuilder()
											->columns('count(*) as num')
											->addFrom('ScshuxCms\Dacang\Model\ReportTplModel')
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
										->addFrom('ScshuxCms\Dacang\Model\ReportTplModel')
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
	 * @desc 获取模版对应的指标数据
	 * @date 2017年4月1日
	 */
	protected  function _getItemDataList()
	{
		$id = $this->request->get('id') ;
		if(!$id)
		{
			$this->sendErrorResult('参数错误') ;
		}

		$where = ' i.report_id = '.$id;

		$dataList = new \stdClass();
		$offset = ($page-1)*$pagesize;
		
		$colemns = 'i.id,i.quota_id,i.quota_total,i.quota_value,group_concat(i.report_user_id) as report_user_id,';
		$colemns.= 'group_concat(i.quota_total) as quota_total,q.type,group_concat(u.name) as report_user_name,q.name as quota_name,q.type as quota_type,';
		$colemns.= 'q.point_desc';
		
		$items = $this->modelsManager->createBuilder()
										->columns($colemns)
										->addFrom('ScshuxCms\Dacang\Model\ReportTplItemModel','i')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.report_user_id = u.id','u')
										->leftJoin('ScshuxCms\Dacang\Model\QuotaModel','i.quota_id = q.id','q')
										->groupBy('i.quota_id')
										->where($where)
										->orderBy('i.id desc')
										->limit($pagesize,$offset)
										->getQuery()
										->execute() ;
		$dataList->items = $items;
		return $dataList;

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
		if(!$id)
		{
			Utils::showMsg('请选择报表', $backurl) ;
		}

		$where = ' i.report_id = '.$id;

		$dataList = new \stdClass();
		$offset = ($page-1)*$pagesize;
		
		//查询字段
		$columns = 'i.id,i.quota_id,i.quota_total,i.quota_value,i.quota_value,group_concat(distinct(u.id)) as reportuserids,i.report_user_id,';
		$columns.= 'q.type,group_concat(distinct(u.name)) as report_user_name,group_concat(i.quota_total) as reportuserweight,q.name as quota_name,';
		$columns.= 'q.type as quota_type,group_concat(i.report_time) as reporttimes';
		
		$items = $this->modelsManager->createBuilder()
										->columns($columns)
										->addFrom('ScshuxCms\Dacang\Model\ReportItemModel','i')
										->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.report_user_id = u.id','u')
										->leftJoin('ScshuxCms\Dacang\Model\QuotaModel','i.quota_id = q.id','q')
										->groupBy('i.quota_id')
										->where($where)
										->orderBy('q.id asc')
										->limit($pagesize,$offset)
										->getQuery()
										->execute() ;

		$dataList->items = $items;
		return $dataList;

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

		if($items)
		{
			$return =  $items[0]->name ;
		}
		return $return ;
	}

	/**
	 * 删除报表数据
	 * @param  $ids
	 */
	protected  function  _remove($ids)
	{
		if($ids){
			//删除报表对应指标
			$reportitems = ReportItemModel::factory()->find('report_id in('.$ids.')') ;
			foreach ($reportitems as $item)
			{
				$item->delete();
			}

			//删除报表对应用户
			$reportusers = ReportUserModel::factory()->find('report_id in('.$ids.')') ;
			foreach ($reportusers as $item)
			{
				$item->delete();
			}
		}
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

		$dataList = new \stdClass() ;
		//没有传递page参数的时候 表示是第一次进入 必须要有参数reportId and quotaId  有传page参数的时候 是调用的phalcon自带的分页 可以从session里面获取这两个参数
		$session = FactoryDefault::getDefault()->get('session');
		if(!$_REQUEST['page'])
		{
			if (!$reportId || !$quotaId)
				return  $dataList ;
					
				$session->set('showpointlistparam',$reportId.'_'.$quotaId);
		}
		else
		{
			$param=explode('_', $session->get('showpointlistparam'));
			$reportId = $param[0];
			$quotaId = $param[1];
		}
		
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$offset = ($page-1)*$pagesize;
		
		
		//获取数据列表
		$dataList = ReportModel::factory()->showPoint($reportId,$quotaId,$this->companyId,$page,$pagesize);
		return $dataList ;
	}

	/**
	 * 删除模版数据
	 * @param  $ids
	 */
	protected  function  _removetpl($ids)
	{
		if($ids){
			$where=is_numeric($ids)?'id='.$ids:'id in ('.$ids.')';				//删除tpl模版
			$andwhere=is_numeric($ids)?'report_id='.$ids:'report_id in ('.$ids.')';	//删除模版指标
			
			ReportTplModel::factory()->deleteBySql($where);
			ReportTplItemModel::factory()->deleteBySql($andwhere);
		}
	}


	
	
	/**
	 * @desc	获取当前用户 可以查看的 所有报表
	 * @param			
	 * @return			
	 */
	protected function _getAllReportid()
	{
		$where = 'i.id >0  and r.company_id = '.$this->companyId;
		$andwhere=UserManageRoleModel::factory()->getWhereByUserManageRole('u.department_id');
		$where = $andwhere?$where.' and '.$andwhere:$where;
		
		$items = ReportModel::factory()->getModelsManager()->createBuilder()
				->columns("r.id")
				->addFrom('ScshuxCms\Dacang\Model\ReportModel','r')
				->leftJoin('ScshuxCms\Dacang\Model\ReportUserModel','r.id = ru.report_id','ru')
				->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','ru.user_id = u.id','u')
				->leftJoin('ScshuxCms\Dacang\Model\ReportItemModel','r.id = i.report_id','i')
				->groupBy('r.id,u.id')
				->where($where)
				->getQuery()
				->execute()
				->toArray();
		$data=array();
		if ($items)
		{
			foreach ($items as $item)
			{
				$data[]=$item['id'];
			}
		}
		return $data;
	}

}
