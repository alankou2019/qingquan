<?php
/**
 * 积分考评  手机端
* @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
* @author kaiping.jiang <kaiping.jiang@scshux.com>
*/
namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\ReportItemModel;
use ScshuxCms\Core\Helper;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Dacang\Model\ReportModel;
use ScshuxCms\Dacang\Model\ReportUserModel;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Dacang\Model\QuotaModel;
use ScshuxCms\Dacang\Model\CompanyDepartModel;
use ScshuxCms\Dacang\Model\ReportStoresModel;
use ScshuxCms\Core\Helper\Dding;
use ScshuxCms\Dacang\Model\ExtraReportItemModel;
use ScshuxCms\Dacang\Model\ExtraReportDescModel;
use ScshuxCms\Dacang\Model\ExtraStoresReportItemModel;
use ScshuxCms\Dacang\Model\QuotaCommentModel;
use ScshuxCms\User\Model\UserViewRoleModel;
use ScshuxCms\Dacang\Helper\Render;
use ScshuxCms\Dacang\Model\PointReportModel;
use ScshuxCms\Dacang\Model\QuotaApplyModel;
use ScshuxCms\Dacang\Model\PointReportItemDetailModel;
use ScshuxCms\Dacang\Model\PointReportItemModel;
use ScshuxCms\Dacang\Model\PointReportUserModel;
use ScshuxCms\Dacang\Model\PointCheckModel;
use ScshuxCms\Dacang\Model\CheckGroupUserModel;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Dacang\Model\CheckGroupModel;
use ScshuxCms\Dacang\Model\PointStoresReportItemModel;
use ScshuxCms\Salary\Model\CompanyModuleAuthModel;

class  BsPointController  extends FrontendBaseController
{

// 	public $userId = '55' ;              	//当前登录钉钉客户端的用户id
// 	public $companyId = '5' ;				//当前登录钉钉客户端的用户所属公司id
	public $userId='';
	public $companyId='';

	public function initialize()
	{
		$mainview =  $this->getView()->getMainView();
		$mainview = str_replace('/main', '/bs', $mainview);
		$this->getView()->setMainView($mainview);
		if($_SERVER['REQUEST_METHOD']=='GET')
		{
			if(strpos($_SERVER["REQUEST_URI"], 'newindex/'))
			{
				$haskey = str_replace('/bspoint/newindex/', '', $_SERVER["REQUEST_URI"]);
				//增加判断  hashkey后面有agentid的情况
				@list($haskey,$agentid)=explode("?", $haskey,2);
				if($haskey)
				{
					$this->session->set('company_haskey', $haskey);
				}
			}
			if(strpos($_SERVER["REQUEST_URI"], 'index/'))
			{
				$haskey = str_replace('/bspoint/index/', '', $_SERVER["REQUEST_URI"]);
				//增加判断  hashkey后面有agentid的情况
				@list($haskey,$agentid)=explode("?", $haskey,2);
				if($haskey)
				{
					$this->session->set('company_haskey', $haskey);
				}
			}

		}

		$this->getuserinfo() ;

	}


	/**
	 *
	 * @desc	新版首页	展示个人信息 公司信息  统计待评分  以评
	 * @date	2017年6月2日
	 */
	public function newindexAction()
	{
		$userinfo = CompanyUserModel::getDetailUser($this->userId) ;
		$nopointnum = PointReportItemModel::factory()->getNeedPointListNum($this->userId);
		$pointingnum = PointReportItemModel::factory()->getReportIngListNum($this->userId);

		$this->view->setVar('nopointnum', $nopointnum) ;
		$this->view->setVar('pointingnum', $pointingnum) ;
		$this->view->setVar('userinfo', $userinfo) ;
		$this->view->setVar('controller_name', $this->getDI()->get('router')->getControllerName());
		$this->view->setVar('hasSalaryMobile', $this->hasSalaryMobile());
	}

	/**
	 *
	 * @desc	查询评分列表
	 * 获取需要我评分的  和正在对我进行评分的
	 * @date	2017年5月3日
	 */
	public  function indexAction()
	{
		$userId = $this->userId ;
		$request = $this->request ;
		$type = $request->get('type') ? intval($request->get('type')) : '1' ;  //默认显示待评分
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = 20 ;

		//获取需要当前登录用户评分的用户列表
		$needdatalist = PointReportItemModel::factory()->getNeedPointList($userId, $page, $pagesize);
		//获取当前正在对我进行评分的考核表
		$reportinglist = PointReportItemModel::factory()->getReportIngList($userId, $page, $pagesize);
		//获取需要审核的考评表
		$checkingList = PointCheckModel::getHavaCheckReport($userId,$page,$pagesize);

		$this->view->setVar('type', $type) ;
		$this->view->setVar('needdatalist', $needdatalist);
		$this->view->setVar('reportinglist', $reportinglist);
		$this->view->setVar('checkingList', $checkingList);
		$this->view->setVar('is_check_user', CheckGroupUserModel::isCheck($userId));
	}


	/**
	 * @desc	ajax局部刷新
	 * @param
	 * @return
	 */
	public function ajaxRequestAction()
	{
		$isAjax=$this->request->isAjax();
		$isPost=$this->request->isPost();
		if (!$isAjax || !$isPost)
		{
			$this->sendErrorResult('request method error');
		}
		$userId = $this->userId;
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = 20 ;

		$requestUrl=$this->request->get('request_url');
		$data=array();
		$controller='bspoint';
		switch ($requestUrl)
		{
			case 'needpointlist':
				$datalist=PointReportItemModel::factory()->getNeedPointList($userId, $page, $pagesize) ;
				$data=Render::renderNeedIndex($datalist,$controller);
				break;
			case 'reportinglist':
				$datalist=PointReportItemModel::factory()->getReportIngList($userId, $page, $pagesize) ;
				$data=Render::renderIngIndex($datalist,$controller);
				break;
			case '':
				//获取需要审核的考评表
				$datalist = PointCheckModel::getHavaCheckReport($userId,$page,$pagesize);
				$data=Render::renderCheckIndex($datalist,$controller);
				break;
		}

		$this->sendSuccessResult($data);
	}



	/**
	 *
	 * @desc	评分详情页面
	 * @date	2017年5月3日
	 */
	public function pointDetailAction()
	{
		$gourl = $this->getHelper()->createUrl(array('p'=>'bspoint/index'));

		$request = $this->request ;
		$id   = $request->get('id') ;      	//报表id
		$uid  = $request->get('uid') ;    	//被审核人id

		if(!$id || !$uid || !is_numeric($id) || !is_numeric($uid))
		{
			Utils::showFrontMsg('参数错误', $gourl) ;
		}
		$id = intval($id) ;
		$uid = intval($uid) ;

		$userinfo = CompanyUserModel::getUserAddDepartByids($uid) ;
		if(!$userinfo)
		{
			$this->sendErrorResult('用户不存在') ;
		}

		$reportinfo = PointReportModel::findFirst($id);
		if(!$reportinfo)
		{
			Utils::showFrontMsg('考核信息不存在', $gourl) ;
		}
		$reportuser = PointReportUserModel::findFirst('report_id = '.$id.' and user_id = '.$uid) ;
		if(!$reportuser)
		{
			Utils::showFrontMsg('考核信息不存在！', $gourl);
		}

		if($reportuser->state == 1)
		{
			Utils::showFrontMsg('考核信息已经完成咯', $gourl) ;
		}
		$totalPoint = PointReportItemModel::factory()->getTotalPoint($id);

		//获取需要评分的具体指标
		$details = PointReportItemModel::factory()->getNeedPointDetail($id,$uid,$this->userId);
		$this->view->setVar('userinfo', $userinfo) ;  						//被考核人的信息
		$this->view->setVar('details', $details) ; 							//考核的具体指标信息
		$this->view->setVar('reportinfo', $reportinfo);						//考核报表信息
		$this->view->setVar('totalpoint',$totalPoint);						//总分
	}




	/**
	 *
	 * @desc	审核指标页面  详情
	 * @date	2017年5月3日
	 */
	public function checkDetailAction()
	{
		$gourl = $this->getHelper()->createUrl(array('p'=>'bspoint/index'));

		$request = $this->request ;
		$id   = $request->get('id') ;      	//报表id

		if(!$id || !is_numeric($id))
		{
			Utils::showFrontMsg('参数错误', $gourl) ;
		}
		$id = intval($id) ;
		$reportinfo = PointReportModel::findFirst($id);
		if(!$reportinfo)
		{
			Utils::showFrontMsg('考核信息不存在', $gourl) ;
		}

		//获取被考评人 信息
		$item=PointReportItemModel::findFirst('report_id='.$id);
		$uid=$item->user_id;
		$userinfo = CompanyUserModel::getUserAddDepartByids($uid) ;

		//获取需要评分的具体指标
		$data=array(
				'user_id'=>$this->userId,
				'report_id'=>$id
		);
		$details = PointCheckModel::getHavaCheckPoint($data);
		$this->view->setVar('userinfo', $userinfo) ;  						//被考核人的信息
		$this->view->setVar('details', $details) ; 							//考核的具体指标信息
		$this->view->setVar('reportinfo', $reportinfo);						//考核报表信息
	}


	/**
	 *
	 * @desc	获取当前用户正在进行的打分的报表的详情
	 * 未公示的 不显示具体得分  以及总分
	 * @date	2017年5月3日
	 */
	public function reportIngDetailAction()
	{
		$gourl = $this->getHelper()->createUrl(array('p'=>'bspoint/index'));

		$request = $this->request ;
		$id   = $request->get('id') ;      	//报表id
		$uid  = $this->userId;		    	//被审核人id

		if(!$id || !is_numeric($id))
		{
			Utils::showFrontMsg('参数错误', $gourl) ;
		}

		$id = intval($id) ;
		$uid = intval($uid) ;

		$userinfo = CompanyUserModel::getUserAddDepartByids($uid) ;

		if(!$userinfo)
		{
			$this->sendErrorResult('用户不存在') ;
		}

		$reportinfo = PointReportModel::findFirst($id);
		if(!$reportinfo)
		{
			Utils::showFrontMsg('考核信息不存在', $gourl) ;
		}

		$reportuser = PointReportUserModel::findFirst('report_id = '.$id.' and user_id = '.$uid) ;
		if(!$reportuser)
		{
			Utils::showFrontMsg('考核信息不存在！', $gourl);
		}

		//根据报表id  获取当前用户已经进行打分的详情
		$details = PointReportItemModel::factory()->getIntPointDetail($id,$uid);
		$totalPoint = PointReportItemModel::factory()->getTotalPoint($id);

		$this->view->setVar('userinfo', $userinfo) ;  						//被考核人的信息
		$this->view->setVar('details', $details) ; 							//考核的具体指标信息
		$this->view->setVar('reportinfo', $reportinfo);						//考核报表信息
		$this->view->setVar('totalpoint', $totalPoint) ;
		$this->view->setVar('ispoint', $isPoint);
	}


	/**
	 *
	 * @desc	查询操作
	 * @date	2017年5月5日
	 */
	public function searchAction()
	{
		$uid = intval($this->userId) ;
		$userinfo = CompanyUserModel::findFirst($uid) ;

		if($userinfo)
		{
			//当前账户权限
			$right = $userinfo->right ;

			//当权限为全公司的时候   则获取部门列表
			if($right == 3)
			{
				$departlist = CompanyDepartModel::find('company_id = '.$userinfo->company_id) ;
			}
			else
			{
				//查看自己当前部门
				$departlist = CompanyDepartModel::find('dingding_id = '.$userinfo->department_id.' and company_id = '.$userinfo->company_id) ;
			}
			$departjson = array();
			//封装成前端需要的json格式
			if($departlist)
			{
				foreach ($departlist as $key=>$depart)
				{
					$departjson[$key]['value'] = $depart->id ;
					$departjson[$key]['text'] = $depart->name ;
				}
			}

			//获取额外的   管理员设置的 可以查看的权限
			$extRole = UserViewRoleModel::factory()->getExtRole($this->userId);
			if (!empty($extRole))
			{
				//二维数组序列化成一维数组 然后在合并去重
				foreach ($departjson as $key=>$val)
				{
					$departjson[$key]=serialize($val);
				}
				foreach ($extRole as $key=>$val)
				{
					$extRole[$key]=serialize($val);
				}
				$mergeArr=array_unique(array_merge($departjson,$extRole));
				$departjson=array();
				foreach ($mergeArr as $val)
				{
					$departjson[]=unserialize($val);
				}
			}
			$this->view->setVar('departjson', json_encode($departjson)) ;
			$this->view->setVar('year', date('Y',time())) ;
			$this->view->setVar('searchtime', date('Y-m-d',time())) ;
		}
	}



	/**
	 *
	 * @desc	获取已经归档成功的被考核人列表
	 * @date	2017年5月5日
	 */
	public function SearchListAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = $this->request->isAjax() ;

		$dataList = $this->_getDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('bs','ajaxsearchlist');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}


	/**
	 *
	 * @desc	考核表详情
	 * @date	2017年5月8日
	 */
	public function storesDetailAction()
	{
		$gourl = $this->getHelper()->createUrl(array('p'=>'bs/searctlist'));

		$request = $this->request ;
		$id   = $request->get('id') ;      	//报表id
		$uid  = $request->get('uid') ;    	//被审核人id
		$sid  = $request->get('sid') ;    	//归档id

		if(!$id || !$uid || !$sid || !is_numeric($id) || !is_numeric($uid) || !is_numeric($sid))
		{
			Utils::showFrontMsg('参数错误', $gourl) ;
		}
		$id = intval($id) ;
		$uid = intval($uid) ;
		$sid = intval($sid) ;

		$userinfo = CompanyUserModel::getUserAddDepartByids($uid) ;

		if(!$userinfo)
		{
			$this->sendErrorResult('用户不存在') ;
		}

		$reportinfo = PointReportModel::findFirst($id);
		if(!$reportinfo)
		{
			Utils::showFrontMsg('考核信息不存在', $gourl) ;
		}

		$reportuser = PointReportUserModel::findFirst('report_id = '.$id.' and user_id = '.$uid) ;

		if(!$reportuser)
		{
			Utils::showFrontMsg('考核信息不存在！', $gourl);
		}

		$storesinfo = PointStoresReportItemModel::findFirst($sid) ;
		if(!$storesinfo)
		{
			Utils::showFrontMsg('归档信息不存在！！', $gourl);
		}

		$storestime = $storesinfo->storestime ;

		$details = PointStoresReportItemModel::factory()->getHasStores($id,$storestime,$this->userId) ;

		$this->view->setVar('userinfo', $userinfo) ;  						//被考核人的信息
		$this->view->setVar('details', $details) ; 							//考核的具体指标信息
		$this->view->setVar('reportinfo', $reportinfo);						//考核报表信息
		$this->view->setVar('totalpoint', PointStoresReportItemModel::factory()->getTotalPoint($id,$sid)) ;
	}





	/**
	 * 获取当前登录顶顶用户信息
	 */
	public function getuserinfo()
	{
		//记录当前的请求地址
		$cache=Helper::factory()->getCache();
		$cache->save('callbackurl',$_SERVER['REQUEST_URI']);

		//测试环境设置用户信息
		$appenv=Helper::factory()->getConfig('application_env');
		if ($appenv=='dev')
		{
				$this->userId = 18169;
				$this->companyId = 5;
				return;
		}

		$user_id = $this->session->get('user_id');
		$company_id = $this->session->get('company_id');
		$dingding_user_id = $this->session->get('dingding_user_id');
		//debug
		if ($user_id=='17632')
		{
			$this->session->set('user_id','55');
			$this->session->set('dingding_user_id','062321564423354698');
			$user_id = $this->session->get('user_id');
			$dingding_user_id = $this->session->get('dingding_user_id');
		}


		if($user_id>0 && $company_id>0 )
		{
			//判断公司是否过期
			$companyinfo = CompanyModel::findFirst($company_id);
			if(!$companyinfo || $companyinfo->status == 0)
			{
				Utils::showFrontMsg('公司不存在或未激活') ;
			}
			if(($companyinfo->expire_time != -1)  && ($companyinfo->expire_time < time()))
			{
				Utils::showFrontMsg('使用时间已经到期，请联系管理员') ;
			}

			//判断当前登录用户所属几个公司  如果用户属于两个公司  并且恰好两个公司都在使用此微应用  那么则应该判断当前是哪个公司
			$this->userId = $user_id;
			$this->companyId = $company_id;
		}
		else
		{

			$this->redirect('dding/login') ;
		}
	}

	protected function hasSalaryMobile()
	{
		$authMap = CompanyModuleAuthModel::getCompanyAuthMap($this->companyId);
		return CompanyModuleAuthModel::isEnabled($authMap, 'salary') && CompanyModuleAuthModel::isEnabled($authMap, 'salary', 'payslip');
	}



	/**
	 * @desc	添加指标申请
	 * @param
	 * @return
	 */
	public function quotaApplyAction()
	{
		$isAjax=$this->request->isAjax();
		$isPost=$this->request->isPost();
		if ($isAjax && $isPost)
		{
			//接收参数
			$data=array();
			$data['name']=$this->request->get('name');
			$data['user_id']=$this->userId;
			$data['point_desc']=$this->request->get('point_desc');
			$data['type']=$this->request->get('type');
			$data['company_id']=$this->companyId;
			$data['report_id'] =$this->request->get('report_id');
			if (!in_array($data['type'], array(3,4)))
			{
				$data['type']=3;
			}

			//判断考评表是否已经在执行考评
			$pointReportInfo=PointReportModel::findFirst(intval($data['report_id']));
			if (!$pointReportInfo || $pointReportInfo->ispoint==1)
			{
				$this->sendErrorResult('考评表已经在执行，不能添加');
			}

			//获取当前用户所属部门    如果获取不到部门id  则默认为1
			$departId=CompanyDepartModel::getDepartId($this->userId);
			if (intval($departId)!=$departId)
			{
				$this->sendErrorResult($departId);	//如果返回值不是整数 则说明获取部门id失败
			}
			$data['depart_id']=$departId;

			//补充完整参数
			$data['status']=0;
			$data['created_at']=Helper::factory()->getTime()->gmtime();
			$res=QuotaApplyModel::factory()->save($data);
			if ($res)
			{
				$this->sendSuccessResult('Success');
			}
			$this->sendErrorResult('保存失败，请稍后再试');
		}
		else
		{
			$this->sendErrorResult('error');
		}
	}





	/**
	 * @desc	积分考评 进行打分
	 * @param
	 * @return
	 */
	public function savePointAction()
	{
		$isAjax=$this->request->isAjax();
		$isPost=$this->request->isPost();
		if ($isAjax && $isPost)
		{
			$reportId=intval($this->request->get('report_id'));
			$quotaId =intval($this->request->get('quota_id'));
			$point   =intval($this->request->get('point'));
			$reason  =trim($this->request->get('reason'));
			if (!$point)
			{
				$this->sendErrorResult('请打分');
			}
			if (!$reason)
			{
				$this->sendErrorResult('请填写您的打分理由');
			}

			//权重指标需要验证分数范围
			$quotaInfo=QuotaModel::findFirst($quotaId);
			if (!$quotaInfo)
			{
				$this->sendErrorResult('错误的指标');
			}
			if ($quotaInfo->type==3)
			{
				$reportInfo=PointReportItemModel::findFirst('report_id='.$reportId.' and quota_id='.$quotaId);
				if ($point>$reportInfo->quota_value)
				{
					$this->sendErrorResult('分数过高');
				}
			}

			//判断考评表是否符合评分的条件
			$pointReportInfo=PointReportModel::findFirst($reportId);
			if (!$pointReportInfo)
			{
				$this->sendErrorResult('error');
			}
			if ($pointReportInfo->ispoint!=1)
			{
				$this->sendErrorResult('赞不能进行考评');
			}

			//保存评分详情
			$detailData=array(
					'report_id'=>$reportId,
					'quota_id' =>$quotaId,
					'user_id'  =>$this->userId,
					'point'    =>$point,
					'reason'   =>$reason,
					'status'   =>0,
					'created_at'=>Helper::factory()->getTime()->gmtime(),
			);
			$res=PointReportItemDetailModel::factory()->save($detailData);
			if ($res)
			{
				$this->sendSuccessResult('Success');
			}
			$this->sendErrorResult('打分失败，请稍候再试');
		}
		else
		{
			$this->sendErrorResult('error');
		}
	}




	/**
	 * @desc	获取评分历史
	 * @param
	 * @return
	 */
	public function getItemDetailAction()
	{
		$isAjax=$this->request->isAjax();
		$isPost=$this->request->isPost();
		if ($isAjax && $isPost)
		{
			$reportId=intval($this->request->get('report_id'));
			$quotaId =intval($this->request->get('quota_id'));
			$status  =intval($this->request->get('status'));
			$page=$this->request->get('page')?intval($this->request->get('page')):1;
			$pageSize=10;
			if (!$reportId || !$quotaId)
			{
				$this->sendErrorResult('error');
			}

			// $where='quota_id='.$quotaId.' and report_id='.$reportId.' and user_id='.$this->userId;
			//
			$where='quota_id='.$quotaId.' and report_id='.$reportId;
			if ($status)
			{
				$where.=' and status=1';
			}
			$dataList=PointReportItemDetailModel::getDatalist($where,$page,$pageSize);
			$this->sendSuccessResult(PointReportItemDetailModel::renderDetail($dataList));
		}
		else
		{
			$this->sendErrorResult('error');
		}
	}




	/**
	 * @desc	积分记录审核
	 * @param
	 * @return
	 */
	public function pointCheckAction()
	{
		$isAjax=$this->request->isAjax();
		$isPost=$this->request->isPost();
		if ($isAjax && $isPost)
		{
			$detailId=$this->request->get('id');
			$option  =$this->request->get('option');
			if (!$detailId || !$option)
			{
				$this->sendErrorResult('error');
			}
			$status=PointCheckModel::getMapStatus($option);
			if (!in_array($status, array_keys(PointCheckModel::getStatus())))
			{
				$this->sendErrorResult('error');
			}
			//判断当前用户是否有权限操作
			$isCheck=CheckGroupUserModel::isCheckUser($this->userId,$detailId);
			if (!$isCheck)
			{
				$this->sendErrorResult(CheckGroupUserModel::getError());
			}

			//添加审核记录
			$data=array(
					'user_id'=>$this->userId,
					'status' =>$status,
					'item_detail_id'=>$detailId
			);
			$res=PointCheckModel::saveData($data);
			if ($res) {
				$this->sendSuccessResult('Success');
			}
			$this->sendErrorResult('失败，请稍后再试');
		}
		else
		{
			$this->sendErrorResult('error');
		}
	}



	/**
	 * @desc	删除点评记录
	 * @param
	 * @return
	 */
	public function delItemDetailAction()
	{
		$isAjax=$this->request->isAjax();
		$isPost=$this->request->isPost();
		if ($isAjax && $isPost)
		{
			$detailId=$this->request->get('id');
			if (!$detailId)
			{
				$this->sendErrorResult('error');
			}
			//判断当前用户是否有权限操作
			$isCheck=CheckGroupUserModel::isCheckUser($this->userId,$detailId);
			if (!$isCheck)
			{
				$this->sendErrorResult(CheckGroupUserModel::getError());
			}

			//删除记录
			$res=PointReportItemDetailModel::delItem($id);
			if ($res)
			{

				$this->sendSuccessResult('Success');
			}
			$this->sendErrorResult('删除失败,请稍后再试');
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
			$this->sendErrorResult('error');
		}
		//获取此考评记录  对应的考评人的部门id
		$departId=PointReportItemDetailModel::getDepartById($id);
		if (!$departId)
		{
			$this->sendErrorResult('error');
		}
		//获取需要审核该部门所有审核人
		$groupUser=CheckGroupModel::getGroupUserInfoByDepartId($departId['department_id']);
		if (!$groupUser)
		{
			//如果未设置考评人  则表示为已经全部审核通过
			$this->sendErrorResult('error');
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
		$this->sendSuccessResult(Render::renderCheckSpeed($groupUser));

	}

	/**
	 * @desc	获取用户列表
	 * @param
	 * @return
	 */
	protected function _getDataList()
	{
		$datalist = new \stdClass();
		$datalist->datanum = 0 ;

		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):10;

		$request = $this->request ;
		$departId = $request->get('departId') ;     			//部门
		$name  = $request->get('name') ;						//姓名
		$stime = $request->get('stime') ;						//开始时间
		$etime = $request->get('etime') ;						//结束时间
		$sobj  = $request->get('searchuser') ;					//查询对象

		$filter = array() ;
		$filter['searchuser'] = $sobj ;

		$where = ' 1=1 ';
		if($name)
		{
			$name = trim($name) ;
			$where .= ' and u.name like "%'.$name.'%"';
			$filter['name'] = $name ;
		}

		//由于暂时不知道  搜索页面 怎么获取部门的id   故暂时以名称查询   以后在修改为id查询
		if($departId)
		{
			$departId = trim($departId) ;
			$where .= ' and d.name ="'.$departId.'"';
			$filter['departId'] = $departId ;
		}

		if($stime)
		{
			$where .= ' and r.created > '.strtotime($stime."00:00:00");
			$filter['stime'] = $stime ;
		}

		if($etime)
		{
			$where .= ' and r.created < '.strtotime($etime." 23:59:59") ;
			$filter['etime'] = $etime ;
		}

		$datalist->pagesize = $pagesize ;
		$datalist->filter = json_encode($filter);

		//判断查询入口
		if($sobj == 'self')
		{
			$where .= ' and s.user_id = '.$this->userId;
		}
		else
		{
			//获取当前登录用户额外的查看权限
			$extRole=UserViewRoleModel::factory()->getUserViewRole($this->userId);
			$userIds='';
			if ($extRole)
			{
				$userIds=CompanyUserModel::factory()->getOneDepartUsers(implode(',', array_unique($extRole)));
			}
			//判断当前登录用户的查看权限
			$userinfo = CompanyUserModel::findFirst($this->userId);
			$right = $userinfo->right ;
			//如果为3  则查看全部  不做限制
			if($right == 1)
			{
				//只能查看自己的
				$where .= $userIds?' and s.user_id in('.$userIds.','.$this->userId.')':' and s.user_id = '.$this->userId;
			}
			if ($right == 2)
			{
				//查看同一部门
				$departuser = CompanyUserModel::factory()->getOneDepartUsers($userinfo->department_id);
				if($departuser)
				{
					$where .= ' and s.user_id in ('.$departuser.')' ;
					$where .= $userIds?' and s.user_id in('.$userIds.','.$departuser.')':' and s.user_id in ('.$departuser.')';
				}
				else
				{
					$where .= $userIds?' and s.user_id in('.$userIds.','.$this->userId.')':' and s.user_id = '.$this->userId;
				}
			}
		}
		$items = ReportStoresModel::factory()->getPointHasStoresList($where,$page,$pagesize) ;
		$datalist->items = $items ;
		$datalist->page = $page ;
		if(count($items) > 0)
		{
			$datalist->datanum = count($items);
		}

		return $datalist ;
	}
}
