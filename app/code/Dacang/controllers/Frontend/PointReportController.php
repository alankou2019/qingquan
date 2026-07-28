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
use ScshuxCms\Dacang\Model\PointReportModel;
use ScshuxCms\Dacang\Model\ReportTplModel;
use ScshuxCms\Dacang\Model\ReportTplItemModel;
use ScshuxCms\Dacang\Model\PointReportUserModel;
use ScshuxCms\Dacang\Model\PointReportItemModel;
use ScshuxCms\Dacang\Model\QuotaModel;
use ScshuxCms\Dacang\Model\ReportStoresModel;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Core\Helper\Dding;
use ScshuxCms\Dacang\Helper\FeishuNotifier;
use ScshuxCms\Dacang\Model\ExtraPointReportModel;
use ScshuxCms\Dacang\Model\ExtraPointReportItemModel;
use ScshuxCms\Dacang\Model\ExtraReportDescModel;
use ScshuxCms\Dacang\Model\ExtraStoresPointReportItemModel;
use ScshuxCms\Dacang\Model\ExtraReportTplModel;
use ScshuxCms\User\Model\UserManageRoleModel;
use ScshuxCms\Dacang\Model\QuotaCommentModel;
use ScshuxCms\Dacang\Model\StoreQuotaCommentModel;
use ScshuxCms\Dacang\Model\PointReportTplModel;
use ScshuxCms\Dacang\Model\PointReportItemDetailModel;
use ScshuxCms\Dacang\Model\PointReportTplItemModel;
use ScshuxCms\Dacang\Model\CheckGroupModel;
use ScshuxCms\Dacang\Model\PointStoresReportItemDetailModel;
use ScshuxCms\Dacang\Model\PointStoresReportItemModel;
use ScshuxCms\Dacang\Model\QuotaApplyModel;
use ScshuxCms\Dacang\Model\PointCheckModel;
class PointReportController extends FrontendBaseController
{
	/**
	 *
	 * @desc 新建积分考评表
	 * @date 2017年4月21日
	 */
	public function newAction()
	{
		$backurl = $this->getHelper()->createUrl(array('p'=>'firm/staff'));  //默认跳回到人员列表
		$request = $this->request ;
		$userid  = $request->get('userid') ;
	
		if(!$userid)
		{
			Utils::showMsg('请选择需要生成积分考评表的人员', $backurl) ;
		}
	
		//获取允许的每人可以建立的报表数目
		$companyinfo = CompanyModel::findFirst($this->companyId) ;
		$reportlimit = $companyinfo->reportlimit ;
	
		$hasreportnum = PointReportModel::factory()->getHasReport($userid) ;
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
	 * @desc 积分考评保存指标
	 * @date 2017年4月21日
	 */
	public function saveAction()
	{
	
		$request = $this->request ;
		$name    = $request->get('name') ; //报表名称
		$userid  = $request->get('userid') ;
		$istpl   = $request->get('istpl') ;
		$quotaids= $request->get('quotaids') ;
		$quotaval= $request->get('quotavalue') ;
		$reportuserid = $request->get('reportuserid') ;
		//必要验证
		$this->checkvalue();
		//判断是新增还是修改
		$id = $request->get('id') ;
		if($id)
		{
			$res = PointReportModel::factory()->savaReport($request);
			if(!$res)
			{
				$this->sendErrorResult('保存失败 ，请稍后再试') ;
			}
		}
		else
		{
			//添加考评表
			$res = PointReportModel::factory()->addreport($request,$this->companyId) ;
		}
	
		if($res)
		{
			$backstr = ''; 	  //预定义返回的信息
				
			//判断是否需要添加模版
			if($istpl == 1)
			{
				//判断可保存的模版数 是否已经达到限制
				$islimit = PointReportTplModel::factory()->checkTplNum($this->companyId);
				//如果还未达到限制的模版条数  则添加模版
				if(!$islimit)
				{
					$reportId = PointReportTplModel::factory()->addreporttpl($request,$this->companyId) ;
					if($reportId)
					{
						//添加报表模版每项指标
						$this->savePointReportTplItem($quotaids, $quotaval, $reportuserid, $reportId) ;
					}
				}
				else
				{
					$backstr = '模版已达到数量限制，请联系管理员';
				}
			}
			//保存报表每项指标
			$this->savePointReportItem($quotaids,$quotaval,$userid,$reportuserid,$res) ;
			
			//保存报表用户对应表
			$this->addPointReportuser($userid,$res);
				
			//确定报表id
			if (!$id)
			{
				$id = PointReportModel::factory()->id ;
			}
				
				
			$this->sendSuccessResult('积分考评表建立成功'.$backstr) ;
		}
		else
		{
			$this->sendErrorResult('新建报表失败，请稍后再试') ;
		}
	}
	


	
	/**
	 *
	 * desc 积分考评表列表
	 * @date 2017年4月21日
	 */
	public function listAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
		if($act == 'remove'){
			$this->_pointremove($_REQUEST['id']);
		}
	
		$dataList = $this->_getPointDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('pointreport','list');
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
		$backurl = $this->getHelper()->createUrl(array('p'=>'pointreport/list'));  //默认跳回到人员列表
		$request = $this->request ;
		$id  = $request->get('id') ;
	
		if(!$id)
		{
			Utils::showMsg('请选择报表', $backurl) ;
		}
	
		$id = intval($id) ;
		$reportinfo = PointReportModel::findFirst($id) ;
		if(!$reportinfo)
		{
			Utils::showMsg('报表数据不存在', $backurl) ;
		}
		
		if($reportinfo->ispoint == 1)
		{
			Utils::showMsg('此考核表正在进行考核，不能进行修改', $backurl) ;
		}
		
		$reportuser = PointReportUserModel::findFirst('report_id = '.$reportinfo->id) ;
		if(!$reportuser)
		{
			Utils::showMsg('错误，不能进行修改', $backurl) ;
		}
		
		$quotaApply=QuotaApplyModel::getApplyQuota($id);
		$this->view->setVar('item', $reportinfo) ;
		$this->view->setVar('userstr', $this->_getUserStr($id)) ;
		$this->view->setVar('reportquotalist', $this->_getReportItemDataList()) ;
		$this->view->setVar('quotatype', Constants::getQuotaType()) ;
		$this->view->setVar('userid', $reportuser->user_id) ;
		$this->view->setVar('quotaapply',$quotaApply);
		$this->view->setVar('quotaapplynum', count($quotaApply));
	}
	
	
	
	/**
	 *
	 * @desc 报表详情
	 * @date 2017年4月24日
	 */
	public function detailAction()
	{
		$backurl = $this->getHelper()->createUrl(array('p'=>'pointreport/list'));  //默认跳回到人员列表

		$request = $this->request ;
		$id  = $request->get('id') ;

		if(!$id)
		{
			Utils::showMsg('请选择报表', $backurl) ;
		}

		$id = intval($id) ;
		$reporttem = PointReportModel::findFirst($id) ;
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
			$this->view->render('pointreport','reporttpl');
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
			$this->view->render('pointreport','simplereporttpl');
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
		$reporttpl = PointReportTplModel::findFirst($id) ;
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
		$this->view->render('pointreport','reporttplitem');
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
		if($act == 'remove'){
			$this->_detailremove($_REQUEST['id']);
		}
		$dataList = $this->_getShowPointList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		$this->view->setVar('statusArr', Constants::getPointCommentStatus());
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('pointreport','showpoint');
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
				//积分考评不存在   不执行归档
				$reportinfo = PointReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				//未公示的  不能进行归档
				if ($reportinfo->ispub!=1)
				{
					continue;
				}
				//积分考评没完成  不执行归档
				$reportuser = PointReportUserModel::findFirst('report_id = '.$id) ;
				if (!$reportuser)
				{
					continue ;
				}
				
				if ($reportuser->state != 1)
				{
					continue ;
				}

				//记录归档的时间
				$nowtime = Helper::factory()->getTime()->gmtime() ;
				//保存此次归档的具体指标
				$saveQuota = PointStoresReportItemModel::factory()->saveStores($id,$nowtime);
				if (!$saveQuota)
				{
					continue;
				}
				//查询具体的打分信息  并且进行归档
				$saveStores = PointStoresReportItemDetailModel::factory()->saveStores($id,$nowtime) ;
				if (!$saveStores)
				{
					continue ;
				}
				
				//清空具体打分信息
				$clearpoint = PointReportItemDetailModel::factory()->clearPoint($id);
				if(!$clearpoint)
				{
					continue ;
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
				$reportinfo = PointReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				//未公示的  不能进行归档
				if ($reportinfo->ispub!=1)
				{
					continue;
				}
				$reportuser = PointReportUserModel::findFirst('report_id	= '.$id) ;
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
				//保存此次归档的具体指标
				$saveQuota = PointStoresReportItemModel::factory()->saveStores($id,$nowtime);
				if (!$saveQuota)
				{
					continue;
				}
				//查询具体的打分信息  并且进行归档
				$saveStores = PointStoresReportItemDetailModel::factory()->saveStores($id,$nowtime) ;
				if (!$saveStores)
				{
					continue ;
				}
	
				//清空具体打分信息
				$clearpoint = PointReportItemDetailModel::factory()->clearPoint($id) ;
				if(!$clearpoint)
				{
					continue ;
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
					
				$reportinfo = PointReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				if($reportinfo->ispoint == 1)
				{
					continue ;
				}
					
				$isFeishu = FeishuNotifier::isConfigured($this->companyId);
				$touser = '';
				if(!$isFeishu)
				{
					//调用钉钉接口 发送消息给用户
					$touser = PointReportItemModel::factory()->getDdUserid($id);
					if(!$touser)
					{
						continue ;
					}
				}
				
				//获取被考核人的姓名
				$reportusername = PointReportItemModel::factory()->getReportUserName($id);
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
					if($isFeishu)
					{
						FeishuNotifier::sendReportStart(
							$this->companyId,$id,'积分考核评分提醒',$tipstr,true
						);
					}
					else
					{
						//不判断消息成功与否
						$res = Dding::factory()->sendMsg($this->companyId,$touser,$tipstr,2);
					}
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
				$reportinfo = PointReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				if($reportinfo->ispoint == 1)
				{
					continue ;
				}
					
				$isFeishu = FeishuNotifier::isConfigured($this->companyId);
				$touser = '';
				if(!$isFeishu)
				{
					//调用钉钉接口 发送消息给用户
					$touser = PointReportItemModel::factory()->getDdUserid($id) ;
					if(!$touser)
					{
						continue ;
					}
				}
	
				//获取被考核人的姓名
				$reportusername = PointReportItemModel::factory()->getReportUserName($id);
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
					if($isFeishu)
					{
						FeishuNotifier::sendReportStart(
							$this->companyId,$id,'积分考核评分提醒',$tipstr,true
						);
					}
					else
					{
						//不判断消息成功与否
						$res = Dding::factory()->sendMsg($this->companyId,$touser,$tipstr);
					}
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
				
				$reportinfo = PointReportModel::findFirst($id) ;
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
				$res = PointReportItemDetailModel::factory()->comeback($id);
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
				
				$reportinfo = PointReportModel::findFirst($id) ;
				if(!$reportinfo)
				{
					continue ;
				}
				//只能重置已经开始、但尚未归档的考评表
				if($reportinfo->ispoint != 1)
				{
					continue ;
				}
				$reportuser = PointReportUserModel::findFirst('report_id = '.$id) ;
				if (!$reportuser)
				{
					continue ;
				}
				
				PointReportItemDetailModel::factory()->comeback($id);
				
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
				$reportinfo = PointReportModel::findFirst($id) ;
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
				$reportUser = PointReportUserModel::findFirst('report_id='.$id);
				if(!$reportUser)
				{
					continue ;
				}

				$isFeishu = FeishuNotifier::isConfigured($this->companyId);
				$dduserid = '';
				if(!$isFeishu)
				{
					//获取被考核人的钉钉userid
					$dduseridobj = CompanyUserModel::findFirst($reportUser->user_id) ;
					$dduserid = $dduseridobj->dingding_user_id ;
				}
				
				//测试环境不发送消息
				$appenv=Helper::factory()->getConfig('application_env');
				if (empty($appenv) || $appenv!='dev')
				{
					if($isFeishu)
					{
						FeishuNotifier::sendReportPublished(
							$this->companyId,$reportUser->user_id,$reportinfo->name,true
						);
					}
					else
					{
						//调用钉钉接口 通知被考核人
						$res = Dding::factory()->sendMsg($this->companyId,$dduserid,'亲，您的'.$reportinfo->name.'已经完成咯  ，快去看看吧') ;
					}
				}
				
				//修改是否已经公示字段
				$reportinfo->ispub = 1 ;
				$reportinfo->save() ;
				//修改考评完成状态
				$reportUser->state=1;
				$reportUser->save();
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
	
				$reportinfo = PointReportModel::findFirst($id) ;
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
				$reportUser = PointReportUserModel::findFirst('report_id='.$id);
				if(!$reportUser)
				{
					continue ;
				}

				$isFeishu = FeishuNotifier::isConfigured($this->companyId);
				$dduserid = '';
				if(!$isFeishu)
				{
					//获取被考核人的钉钉userid
					$dduseridobj = CompanyUserModel::findFirst($reportUser->user_id) ;
					$dduserid = $dduseridobj->dingding_user_id ;
				}
	
				//测试环境不发送消息
				$appenv=Helper::factory()->getConfig('application_env');
				if (empty($appenv) || $appenv!='dev')
				{
					if($isFeishu)
					{
						FeishuNotifier::sendReportPublished(
							$this->companyId,$reportUser->user_id,$reportinfo->name,true
						);
					}
					else
					{
						//调用钉钉接口 通知被考核人
						$res = Dding::factory()->sendMsg($this->companyId,$dduserid,'亲，您的'.$reportinfo->name.'已经完成咯  ，快去看看吧') ;
					}
				}
	
	
				//修改是否已经公示字段
				$reportinfo->ispub = 1 ;
				$reportinfo->save() ;
				//修改考评完成状态
				$reportUser->state=1;
				$reportUser->save();
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
	 * @desc	修改积分考评    指标的具体评分的状态
	 * @param			
	 * @return			
	 */
	public function upstatAction()
	{
		$isAjax=$this->request->isAjax();
		$isPost=$this->request->isPost();
		if ($isAjax && $isPost)
		{
			$id=$this->request->get('id');
			$option=$this->request->get('option');
			if (!$id)
			{
				$this->sendErrorResult('error');
			}
			
			$detailInfo=PointReportItemDetailModel::findFirst(intval($id));
			if (!$detailInfo)
			{
				$this->sendErrorResult('error');
			}
			$detailInfo->status=intval($option);
			$detailInfo->save();
			$this->sendSuccessResult('success');
			
		}
		else 
		{
			$this->sendErrorResult('error');
		}
	}
	
	
	/**
	 * @desc	审核指标
	 * @param			
	 * @return			
	 */
	public function setQuotaAction()
	{
		$isAjax=$this->request->isAjax();
		$isPost=$this->request->isPost();
		if ($isAjax && $isPost)
		{
			$quotaId=$this->request->get('quotaId');	//指标id
			$option =$this->request->get('option');		//处理状态
			$status =QuotaApplyModel::getApplyStatus($option);
			if (!in_array($status, array(1,2)))
			{
				$this->sendErrorResult('error');
			}
			$applyInfo=QuotaApplyModel::findFirst($quotaId);
			if (!$applyInfo)
			{
				$this->sendErrorResult('error');
			}
			
			switch ($status)
			{
				case 1:
					//审核通过   则将指标申请表中的指标  添加到指标表里面  并且添加到考评表
					$res=QuotaApplyModel::passQuota($applyInfo);
					break;
				case 2:
					//审核不通过  删除
					$res=$applyInfo->delete();
					break;
			}
			if ($res)
			{
				$this->sendSuccessResult($res);
			}
			$this->sendErrorResult('error');
		}
		else 
		{
			$this->sendErrorResult('error');
		}
	}
	
	
	/**
	 * @desc	查看积分记录审核进度
	 * @param			
	 * @return			
	 */
	public function checkSpeedAction()
	{
		$id=$this->request->get('id');
		if (!$id)
		{
			exit('error');
		}
		//获取此考评记录  对应的考评人的部门id
		$departId=PointReportItemDetailModel::getDepartById($id);
		if (!$departId)
		{
			exit('error');
		}
		//获取需要审核该部门所有审核人
		$groupUser=CheckGroupModel::getGroupUserInfoByDepartId($departId['department_id']);
		if (!$groupUser)
		{
			//如果未设置考评人  则表示为已经全部审核通过
			exit('error');
		}
		//获取已经参与考评的审核人
		$hasCheckUser=PointCheckModel::getHasCheckUser($id);
		foreach ($groupUser as $key=>$user)
		{
			//审核人员的审核状态
			$user['checkstatus']='n';
			if (in_array($user['user_id'], $hasCheckUser))
			{
				$user['checkstatus']='y';
			}
			$groupUser[$key]=$user;
		}
		$this->view->setVar('groupUser', $groupUser);
		$this->view->setVar('count', count($groupUser));
		
	}
	

	/**
	 *
	 * @desc 根据报表id 获取用户
	 * @date 2017年4月24日
	 */
	protected function _getUserStr($reportId)
	{
		$return = '';
		if(!$reportId)
		{
			return  $return;
		}

		$reportId = intval($reportId) ;

		$items = $this->modelsManager->createBuilder()
										->columns('group_concat(u.name) as name')
										->addFrom('ScshuxCms\Dacang\Model\PointReportUserModel','ru')
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
	 * 删除积分考评表数据
	 * @param  $ids
	 */
	protected function _pointremove($ids)
	{
		$ids = array_unique(array_filter(array_map('intval', explode(',', (string)$ids))));
		if (!$ids) {
			return;
		}

		$idList = implode(',', $ids);
		$reports = PointReportModel::find('id in(' . $idList . ') and company_id=' . intval($this->companyId));
		$reportIds = array();
		$reportsToDelete = array();
		foreach ($reports as $report) {
			$reportIds[] = intval($report->id);
			$reportsToDelete[] = $report;
		}
		if (!$reportIds) {
			return;
		}

		$where = 'report_id in(' . implode(',', $reportIds) . ')';
		PointReportItemModel::deleteAll($where);
		PointReportItemDetailModel::factory()->deleteBySql($where);
		PointReportUserModel::factory()->deleteBySql($where);

		foreach ($reportsToDelete as $report) {
			$report->delete();
		}
	}

	/**
	 *
	 * @desc	获取积分考评表  指标具体评分情况
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
		if(!$this->request->get('is_ajax'))
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
		$dataList = PointReportItemDetailModel::showPoint($reportId,$quotaId,$page,$pagesize);
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
			
			PointReportTplModel::factory()->deleteBySql($where);
			PointReportTplItemModel::factory()->deleteBySql($andwhere);
		}
	}
	
	
	/**
	 * @desc	删除积分记录
	 * @param			
	 * @return			
	 */
	protected function _detailremove($ids)
	{
		if($ids){
			PointReportItemDetailModel::delItem($ids);
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
		
		$items = PointReportModel::factory()->getModelsManager()->createBuilder()
				->columns("r.id")
				->addFrom('ScshuxCms\Dacang\Model\PointReportModel','r')
				->leftJoin('ScshuxCms\Dacang\Model\PointReportUserModel','r.id = ru.report_id','ru')
				->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','ru.user_id = u.id','u')
				->leftJoin('ScshuxCms\Dacang\Model\PointReportItemModel','r.id = i.report_id','i')
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
		
		$point = 0.0 ;
		foreach ($quotaval as $val)
		{
			$point += floatval($val) ;
		}
		
		//判断各项指标的权重值总和是否等于所设置值
		if($point != $sum)
		{
			$this->sendErrorResult('各项指标权重相加必须等于权重总额，请从新设置指标权重') ;
		}
	}
	
	
	
	
	/**
	 *
	 * @desc 保存积分考评表每项指标
	 * @date 2017年4月21日
	 */
	protected function savePointReportItem($quotaids,$quotaval,$userid,$reportuserid,$reportId)
	{
		$reportId = intval($reportId) ;
		//根据 reportId  判断是新增  还是 修改
		$istrue = PointReportItemModel::findFirst('report_id = '.$reportId) ;
	
		//记录已经存在  表示更新
		if($istrue)
		{
			//删除原来的数据
			$delwhere = 'report_id = '.$reportId;
			PointReportItemModel::deleteAll($delwhere) ;
		}
		$sql = "insert into \ScshuxCms\Dacang\Model\PointReportItemModel (quota_id,quota_value,user_id,report_user_id,report_id,created) ";
		$sql.= 'values (:quota_id:,:quota_value:,:user_id:,:report_user_id:,:report_id:,:created:)';
		$nowtime=Helper::factory()->getTime()->gmtime();
		foreach ($quotaids as $key=>$value)
		{
			//因为一个指标可以选择多个评分人   所以这里循环添加
			$arrreportuser = explode(',', $reportuserid[$key]) ;
			foreach ($arrreportuser as $k=>$intreportuser)
			{
				$this->modelsManager->executeQuery($sql,array(
						'quota_id'    => $value,
						'quota_value' => $quotaval[$key],
						'user_id'     => $userid,
						'report_user_id' => $intreportuser,
						'report_id'   => $reportId,
						'created'     => $nowtime
				)) ;
			}
		}
	
	}
	
	
	/**
	 *
	 * @desc 保存积分考评表-用户对应表
	 * @date 2017年4月24日
	 */
	protected function addPointReportuser($userid,$reportId)
	{
		$userid = intval($userid);
		$reportId = intval($reportId) ;
	
		//判断是添加还是修改  只有当新添加的时候  才生成记录
		$reportuser = PointReportUserModel::findFirst('user_id = '.$userid.' and report_id = '.$reportId) ;
		if(!$reportuser)
		{
			$sql = "insert into \ScshuxCms\Dacang\Model\PointReportUserModel (user_id,report_id,created) ";
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
	 * @desc 积分考核模版每项指标
	 * @date 2017年4月21日
	 */
	protected function savePointReportTplItem($quotaids, $quotaval, $reportuserid, $reportId)
	{
		$sql = "insert into \ScshuxCms\Dacang\Model\PointReportTplItemModel (quota_id,quota_value,report_user_id,report_id) ";
		$sql.= 'values (:quota_id:,:quota_value:,:report_user_id:,:report_id:)';
	
		foreach ($quotaids as $key=>$value)
		{
			//因为一个指标可以选择多个评分人   所以这里循环添加
			$arrreportuser = explode(',', $reportuserid[$key]) ;
			foreach ($arrreportuser as $k=>$intreportuser)
			{
				$this->modelsManager->executeQuery($sql,array(
						'quota_id'    => $value,
						'quota_value' => $quotaval[$key],
						'report_user_id' => $intreportuser,
						'report_id'   => $reportId,
				)) ;
			}
		}
	}
	
	
	
	/**
	 *
	 * @desc 获取积分考评表列表
	 * @date 2017年4月1日
	 */
	protected  function _getPointDataList()
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
		$countInfo = PointReportModel::factory()->getModelsManager()->createBuilder()
		->columns('count(distinct(u.id)) as num')
		->addFrom('ScshuxCms\Dacang\Model\PointReportModel','r')
		->leftJoin('ScshuxCms\Dacang\Model\PointReportUserModel','r.id = ru.report_id','ru')
		->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','ru.user_id = u.id','u')
		->leftJoin('ScshuxCms\Dacang\Model\PointReportItemModel','r.id = i.report_id','i')
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
		$items = PointReportModel::factory()->getModelsManager()->createBuilder()
		->columns($columns)
		->addFrom('ScshuxCms\Dacang\Model\PointReportModel','r')
		->leftJoin('ScshuxCms\Dacang\Model\PointReportUserModel','r.id = ru.report_id','ru')
		->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','ru.user_id = u.id','u')
		->leftJoin('ScshuxCms\Dacang\Model\PointReportItemModel','r.id = i.report_id','i')
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
				$item['totalpoint'] = PointReportItemModel::factory()->getTotalPoint($item['id']);
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
		->addFrom('ScshuxCms\Dacang\Model\PointReportTplModel')
		->where($where)
		->getQuery()
		->execute();
	
	
	
		$dataList->count       = empty($countInfo)?0:$countInfo[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		$dataList->filter      = $filter ;
		/*加载数据*/
	
	
	
		$offset = ($page-1)*$pagesize;
		$items = $this->modelsManager->createBuilder()
		->addFrom('ScshuxCms\Dacang\Model\PointReportTplModel')
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
	
		$colemns = 'i.id,i.quota_id,group_concat(i.report_user_id) as report_user_id,i.quota_value,';
		$colemns.= 'q.type,group_concat(u.name) as report_user_name,q.name as quota_name,q.type as quota_type,';
		$colemns.= 'q.point_desc';
	
		$items = $this->modelsManager->createBuilder()
		->columns($colemns)
		->addFrom('ScshuxCms\Dacang\Model\PointReportTplItemModel','i')
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
		$backurl = $this->getHelper()->createUrl(array('p'=>'pointreport/list'));  //默认跳回到人员列表
	
		$id = $this->request->get('id') ;
		if(!$id)
		{
			Utils::showMsg('请选择报表', $backurl) ;
		}
	
		$where = ' i.report_id = '.$id;
	
		$dataList = new \stdClass();
		$offset = ($page-1)*$pagesize;
	
		//查询字段
		$columns = 'i.id,i.report_id,i.quota_id,i.quota_value,group_concat(distinct(u.id)) as reportuserids,i.report_user_id,';
		$columns.= 'q.type,group_concat(distinct(u.name)) as report_user_name,q.name as quota_name,';
		$columns.= 'q.type as quota_type';
	
		$items = $this->modelsManager->createBuilder()->columns($columns)
		->addFrom('ScshuxCms\Dacang\Model\PointReportItemModel','i')
		->leftJoin('ScshuxCms\Dacang\Model\CompanyUserModel','i.report_user_id = u.id','u')
		->leftJoin('ScshuxCms\Dacang\Model\QuotaModel','i.quota_id = q.id','q')
		->groupBy('i.quota_id')
		->where($where)
		->orderBy('q.id asc')
		->limit($pagesize,$offset)
		->getQuery()
		->execute();
		
		$dataList->items = $items;
		return $dataList;
	}

	
	/**
	 * @desc	设置回调url
	 * @param			
	 * @return			
	 */
	protected function _setCallbackUrl()
	{
		$cache=Helper::factory()->getCache();
		$cache->save('callbackUrl','http://'.$_SERVER['HTTP_HOST'].'/bspoint/index');
	}

}
