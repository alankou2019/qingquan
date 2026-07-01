<?php
/**
 * 钉钉接口
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
use ScshuxCms\User\Model\UserModel;
use ScshuxCms\Salary\Model\CompanyModuleAuthModel;
use ScshuxCms\Salary\Model\SalaryOperationLogModel;
use ScshuxCms\Salary\Model\PayrollSlipModel;
use ScshuxCms\Salary\Model\SalaryViewRoleModel;
use Phalcon\Di\FactoryDefault;

class  BsController extends FrontendBaseController
{

    // 	public $userId = '55' ;              	//当前登录钉钉客户端的用户id
    // 	public $companyId = '5' ;				//当前登录钉钉客户端的用户所属公司id
    public $userId    = '';
    public $companyId = '';

    public function initialize()
    {
        $mainview = $this->getView()->getMainView();
        $mainview = str_replace('/main', '/bs', $mainview);
        $this->getView()->setMainView($mainview);

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (strpos($_SERVER["REQUEST_URI"], 'newindex/')) {
                $haskey = str_replace('/bs/newindex/', '', $_SERVER["REQUEST_URI"]);
                //增加判断  hashkey后面有agentid的情况
                @list($haskey, $agentid) = explode("?", $haskey, 2);
                if ($haskey) {
                    $this->session->set('company_haskey', $haskey);
                }
            }

            if (strpos($_SERVER["REQUEST_URI"], '/index/')) {
                $haskey = str_replace('/bs/index/', '', $_SERVER["REQUEST_URI"]);
                @list($haskey, $agentid) = explode("?", $haskey, 2);
                if ($haskey) {
                    $this->session->set('company_haskey', $haskey);
                }
            }
        }

        $this->getuserinfo();

    }


    /**
     *
     * @desc    新版首页    展示个人信息 公司信息  统计待评分  以评
     * @date    2017年6月2日
     */
    public function newindexAction()
    {
        $userinfo    = CompanyUserModel::getDetailUser($this->userId);
        $nopointnum  = ReportItemModel::factory()->getNeedPointListNum($this->userId);
        $pointingnum = ReportItemModel::factory()->getReportIngListNum($this->userId);

        $this->view->setVar('nopointnum', $nopointnum);
        $this->view->setVar('pointingnum', $pointingnum);
        $this->view->setVar('userinfo', $userinfo);
        $this->view->setVar('pointmofule', UserModel::checkPointModule());
        $this->view->setVar('controller_name', $this->getDI()->get('router')->getControllerName());
        $this->view->setVar('bro', $this->checkBrowser());
        $this->view->setVar('hasSalaryMobile', $this->hasSalaryMobile());
    }

    public function salaryAction()
    {
        $this->checkSalaryMobile();
        $currentMonth = date('Y-m');
        $currentYear = date('Y');
        $monthSlips = PayrollSlipModel::getEmployeePublishedSlips($this->companyId, $this->userId, '', $currentMonth, 1);
        $yearSlips = PayrollSlipModel::getEmployeePublishedSlips($this->companyId, $this->userId, $currentYear, '', 60);
        $historySlips = PayrollSlipModel::getEmployeePublishedSlips($this->companyId, $this->userId, '', '', 120);
        $historyCount = 0;
        foreach ($historySlips as $slip) {
            if (strpos($slip['payroll_month'], $currentYear . '-') !== 0) {
                $historyCount++;
            }
        }

        $this->view->setVar('currentMonth', $currentMonth);
        $this->view->setVar('monthSlip', empty($monthSlips) ? false : $monthSlips[0]);
        $this->view->setVar('yearSlipCount', count($yearSlips));
        $this->view->setVar('historyCount', $historyCount);
        $this->view->setVar('canViewSubordinateSalary', $this->canViewSubordinateSalary());
    }

    public function salaryyearAction()
    {
        $this->checkSalaryMobile();
        $year = trim($this->request->get('year'));
        if (!preg_match('/^\d{4}$/', $year)) {
            $year = date('Y');
        }
        $slips = PayrollSlipModel::getEmployeePublishedSlips($this->companyId, $this->userId, $year, '', 60);
        $summary = $this->buildMobileSalarySummary($slips);
        $this->view->setVar('year', $year);
        $this->view->setVar('slips', $slips);
        $this->view->setVar('summary', $summary);
    }

    public function salaryhistoryAction()
    {
        $this->checkSalaryMobile();
        $currentYear = date('Y');
        $allSlips = PayrollSlipModel::getEmployeePublishedSlips($this->companyId, $this->userId, '', '', 120);
        $historySlips = array();
        foreach ($allSlips as $slip) {
            if (strpos($slip['payroll_month'], $currentYear . '-') !== 0) {
                $historySlips[] = $slip;
            }
        }
        $summary = $this->buildMobileSalarySummary($historySlips);
        $this->view->setVar('slips', $historySlips);
        $this->view->setVar('summary', $summary);
    }

    public function salarysubordinateAction()
    {
        $this->checkSalaryMobile();
        $scope = $this->getMobileSalaryScope();
        $year = trim($this->request->get('year'));
        if (!preg_match('/^\d{4}$/', $year)) {
            $year = date('Y');
        }
        $slips = array();
        if ($this->scopeHasSalaryRows($scope)) {
            $slips = PayrollSlipModel::getAuthorizedPublishedSlips($this->companyId, $scope, $this->userId, $year, '', 200);
        }
        $this->view->setVar('year', $year);
        $this->view->setVar('slips', $slips);
        $this->view->setVar('summary', $this->buildMobileSalarySummary($slips));
        $this->view->setVar('canViewSubordinateSalary', $this->scopeHasSalaryRows($scope));
    }

    public function salarysubordinatedetailAction()
    {
        $this->checkSalaryMobile();
        $slipId = intval($this->request->get('id'));
        $backUrl = $this->getHelper()->createUrl(array('p' => 'bs/salarysubordinate'));
        if ($slipId <= 0) {
            Utils::showFrontMsg('参数错误', $backUrl);
        }
        $scope = $this->getMobileSalaryScope();
        if (!$this->scopeHasSalaryRows($scope)) {
            Utils::showFrontMsg('没有下属薪酬查看权限', $this->getHelper()->createUrl(array('p' => 'bs/salary')));
        }
        $slip = PayrollSlipModel::getAuthorizedPublishedSlipDetail($this->companyId, $scope, $slipId, $this->userId);
        if (!$slip) {
            Utils::showFrontMsg('工资条不存在或无权查看', $backUrl);
        }
        $slip['published_time'] = empty($slip['published_at']) ? '-' : date('Y-m-d H:i', intval($slip['published_at']));
        $slip['viewed_time'] = empty($slip['viewed_at']) ? '-' : date('Y-m-d H:i', intval($slip['viewed_at']));
        $slip['confirmed_time'] = empty($slip['confirmed_at']) ? '-' : date('Y-m-d H:i', intval($slip['confirmed_at']));
        $this->addMobileSalaryLog('mobile_subordinate_salary_view', 'payroll_slip', $slipId, $slip['payroll_month'], '手机端查看下属薪酬明细');
        $this->view->setVar('slip', $slip);
    }

    public function salarydetailAction()
    {
        $this->checkSalaryMobile();
        $slipId = intval($this->request->get('id'));
        $backUrl = $this->getHelper()->createUrl(array('p' => 'bs/salary'));
        if ($slipId <= 0) {
            Utils::showFrontMsg('参数错误', $backUrl);
        }
        $slip = PayrollSlipModel::getEmployeePublishedSlipDetail($this->companyId, $this->userId, $slipId);
        if (!$slip) {
            Utils::showFrontMsg('工资条不存在', $backUrl);
        }
        $slip['published_time'] = empty($slip['published_at']) ? '-' : date('Y-m-d H:i', intval($slip['published_at']));
        $slip['viewed_time'] = empty($slip['viewed_at']) ? '-' : date('Y-m-d H:i', intval($slip['viewed_at']));
        $slip['confirmed_time'] = empty($slip['confirmed_at']) ? '-' : date('Y-m-d H:i', intval($slip['confirmed_at']));
        $this->addMobileSalaryLog('mobile_payslip_view', 'payroll_slip', $slipId, $slip['payroll_month'], '手机端查看本人薪酬明细');
        $this->view->setVar('slip', $slip);
    }

    public function salaryconfirmAction()
    {
        $this->checkSalaryMobile();
        $slipId = intval($this->request->get('id'));
        $backUrl = $this->getHelper()->createUrl(array('p' => 'bs/salarydetail', 'id' => $slipId));
        if (!$this->request->isPost()) {
            Utils::showFrontMsg('不支持的请求方式', $backUrl);
        }
        $slip = PayrollSlipModel::getEmployeePublishedSlipDetail($this->companyId, $this->userId, $slipId);
        if (!$slip) {
            Utils::showFrontMsg('工资条不存在', $backUrl);
        }
        $result = PayrollSlipModel::factory()->confirmEmployeeSlip($this->companyId, $this->userId, $slipId);
        if (!$result) {
            Utils::showFrontMsg(PayrollSlipModel::factory()->getLastError(), $backUrl);
        }
        $this->addMobileSalaryLog('mobile_payslip_confirm', 'payroll_slip', $slipId, $slip['payroll_month'], '手机端确认本人工资条');
        Utils::showFrontMsg('工资条已确认', $backUrl);
    }

    /**
     *
     * @desc    查询评分列表
     * @date    2017年5月3日
     */
    public function indexAction()
    {
        $userId   = $this->userId;
        $request  = $this->request;
        $type     = $request->get('type') ? intval($request->get('type')) : '1';  //默认显示待评分
        $page     = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $page     = $page < 1 ? 1 : $page;
        $pagesize = 20;

        //获取需要当前登录用户评分的用户列表
        $needdatalist = ReportItemModel::factory()->getNeedPointList($userId, $page, $pagesize);

        //获取当前登录用户已经评分的用户列表
        $hasdatalist = ReportItemModel::factory()->getHasPointList($userId, $page, $pagesize);

        //获取当前正在对我进行评分的考核表
        $reportinglist = ReportItemModel::factory()->getReportIngList($userId, $page, $pagesize);

        $this->view->setVar('type', $type);
        $this->view->setVar('needdatalist', $needdatalist);
        $this->view->setVar('hasdatalist', $hasdatalist);
        $this->view->setVar('reportinglist', $reportinglist);
    }


    /**
     * @desc    ajax局部刷新
     * @param
     * @return
     */
    public function ajaxRequestAction()
    {
        $isAjax = $this->request->isAjax();
        $isPost = $this->request->isPost();
        if (!$isAjax || !$isPost) {
            $this->sendErrorResult('request method error');
        }
        $userId   = $this->userId;
        $page     = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $page     = $page < 1 ? 1 : $page;
        $pagesize = 20;

        $requestUrl = $this->request->get('request_url');
        $data       = [];
        switch ($requestUrl) {
            case 'needpointlist':
                $datalist = ReportItemModel::factory()->getNeedPointList($userId, $page, $pagesize);
                $data     = Render::renderNeedIndex($datalist);
                break;
            case 'haspointlist':
                $datalist = ReportItemModel::factory()->getHasPointList($userId, $page, $pagesize);
                $data     = Render::renderHasIndex($datalist);
                break;
            case 'reportinglist':
                $datalist = ReportItemModel::factory()->getReportIngList($userId, $page, $pagesize);
                $data     = Render::renderIngIndex($datalist);
                break;
        }

        $this->sendSuccessResult($data);
    }


    /**
     *
     * @desc    评分详情页面
     * @date    2017年5月3日
     */
    public function pointDetailAction()
    {
        $gourl = $this->getHelper()->createUrl(['p' => 'bs/index']);

        $request = $this->request;
        $id      = $request->get('id');                                        //报表id
        $uid     = $request->get('uid');                                       //被审核人id
        $state   = $request->get('state');                                     //类型

        if (!$id || !$uid || !is_numeric($id) || !is_numeric($uid)) {
            Utils::showFrontMsg('参数错误', $gourl);
        }
        $id  = intval($id);
        $uid = intval($uid);

        $userinfo = CompanyUserModel::getUserAddDepartByids($uid);

        if (!$userinfo) {
            $this->sendErrorResult('用户不存在');
        }

        $reportinfo = ReportModel::findFirst($id);
        if (!$reportinfo) {
            Utils::showFrontMsg('考核信息不存在', $gourl);
        }

        $reportuser = ReportUserModel::findFirst('report_id = ' . $id . ' and user_id = ' . $uid);

        if (!$reportuser) {
            Utils::showFrontMsg('考核信息不存在！', $gourl);
        }
        //判断是查看当前用户未评分的详情  还是已经评分的详情
        if ($state) {
            $details = ReportItemModel::factory()->getHasPointDetail($id, $uid, $this->userId);
        } else {
            if ($reportuser->state == 1) {
                Utils::showFrontMsg('考核信息已经完成咯', $gourl);
            }
            //获取需要评分的具体指标
            $details = ReportItemModel::factory()->getNeedPointDetail($id, $uid, $this->userId);
        }

        // 		$this->view->setvar('totalpoint',ReportItemModel::factory()->getTotalPoint($id));	//报表总分
        $this->view->setVar('userinfo', $userinfo);                            //被考核人的信息
        $this->view->setVar('details', $details);                              //考核的具体指标信息
        $this->view->setVar('reportinfo', $reportinfo);                        //考核报表信息
        $this->view->setVar('state', $state ? 1 : 0);                         //是否为已评分详情

    }


    /**
     *
     * @desc    获取当前用户正在进行的打分的报表的详情
     * 未公示的 不显示具体得分  以及总分
     * @date    2017年5月3日
     */
    public function reportIngDetailAction()
    {
        $gourl = $this->getHelper()->createUrl(['p' => 'bs/index']);

        $request = $this->request;
        $id      = $request->get('id');          //报表id
        $uid     = $this->userId;                //被审核人id

        if (!$id || !is_numeric($id)) {
            Utils::showFrontMsg('参数错误', $gourl);
        }

        $id  = intval($id);
        $uid = intval($uid);

        $userinfo = CompanyUserModel::getUserAddDepartByids($uid);

        if (!$userinfo) {
            $this->sendErrorResult('用户不存在');
        }

        $reportinfo = ReportModel::findFirst($id);
        if (!$reportinfo) {
            Utils::showFrontMsg('考核信息不存在', $gourl);
        }

        //根据报表id  获取当前用户已经进行打分的详情
        $details = ReportItemModel::factory()->getIntPointDetail($id, $uid);

        //如果还未公示  不显示具体打分情况
        $totalPoint = ReportItemModel::factory()->getTotalPoint($id, $sid);
        $isPoint    = $reportinfo->ispub;
        $totalPoint = $isPoint ? $totalPoint : 0;

        $this->view->setVar('userinfo', $userinfo);                            //被考核人的信息
        $this->view->setVar('details', $details);                              //考核的具体指标信息
        $this->view->setVar('reportinfo', $reportinfo);                        //考核报表信息
        $this->view->setVar('state', $state);                                  //详情类型
        $this->view->setVar('totalpoint', $totalPoint);
        $this->view->setVar('extraavgpoint', ExtraReportItemModel::getAvgPoint($id));
        $this->view->setVar('extrareportdesc', ExtraReportDescModel::getDesc($id));
        $this->view->setVar('ispoint', $isPoint);
    }


    /**
     *
     * @desc    进行打分
     * @date    2017年5月3日
     */
    public function setPointAction()
    {
        $isajax = $this->request->isAjax();
        if ($isajax) {
            $request  = $this->request;
            $reportId = $request->get('reportId');        //报表id
            $uid      = $request->get('uid');             //被考核人id
            $quotaids = $request->get('quotaids');        //评分指标id
            $quotaval = $request->get('quotaval');        //具体评分值
            $extpoint = $request->get('extraquotaval');   //额外的加减分

            if (!$reportId || !$uid || !$quotaids || !$quotaval) {
                $this->sendErrorResult('参数错误');
            }

            //指标个数
            $quotanum = count(array_unique($quotaids));
            if (count($quotaval) < $quotanum) {
                $this->sendErrorResult('请给每个指标进行打分');
            }

            $reportId = intval($reportId);
            $uid      = intval($uid);

            $reportinfo = ReportModel::findFirst($reportId);
            if (!$reportinfo) {
                $this->sendErrorResult('考核表不存在');
            }

            $reportuser = ReportUserModel::findFirst('report_id = ' . $reportId . ' and user_id = ' . $uid);

            if (!$reportuser) {
                $this->sendErrorResult('用户的考核表不存在');
            }

            $nowtime = Helper::factory()->getTime()->gmtime();
            //循环每一个指标
            foreach ($quotaids as $key => $qid) {
                //获取指标
                $quotainfo = QuotaModel::findFirst($qid);
                if (!$quotainfo) {
                    $this->sendErrorResult('此指标不存在');
                    break;
                }

                $item = ReportItemModel::findFirst('report_id = ' . $reportId . ' and user_id = ' . $uid . ' and report_user_id = ' . $this->userId . ' and quota_id = ' . intval($qid));


                if (!$item) {
                    $this->sendErrorResult('此指标不存在！');
                    break;
                }

                //加减分不需要进行指标上限判断，但减分项只能填0或负数
                if ($quotainfo->type == 4) {
                    if ($this->isMinusQuota($quotainfo) && floatval($quotaval[$key]) > 0) {
                        $this->sendErrorResult('减分项只能填写0或负数');
                        break;
                    }
                    if ($this->isPlusQuota($quotainfo) && floatval($quotaval[$key]) < 0) {
                        $this->sendErrorResult('加分项只能填写0或正数');
                        break;
                    }
                } else {
                    //判断指标类型  来确定打分类型
                    $quotaMaxPoint = QuotaModel::factory()->getQuotaMaxPoint();
                    if (in_array($quotainfo->type, [1, 2, 5])) {
                        if (floatval($quotaval[$key]) < 0 || floatval($quotaval[$key]) > $quotaMaxPoint[$quotainfo->type]) {
                            $this->sendErrorResult('输入的评分不合理，请从新输入');
                            break;
                        }
                    } else {
                        //比重类型指标  判断最大值最小值
                        //通过指标id   报表id  确定此指标的权重
                        $reportitem = ReportItemModel::findFirst('report_id = ' . $reportId . ' and quota_id = ' . $qid);
                        if (!$reportitem) {
                            $this->sendErrorResult('考核指标不存在');
                        }
                        $quotavalue = 2 * $reportitem->quota_value;

                        if (floatval($quotaval[$key]) > $quotavalue || floatval($quotaval[$key]) < 0) {
                            $this->sendErrorResult('输入的评分不合理，请从新输入！！');
                            break;
                        }
                    }
                }


                //保存指标值
                $item->report_time  = $nowtime;
                $item->report_point = $quotaval[$key];


                $res = $item->save();
                if (!$res) {
                    $this->sendErrorResult('打分失败，请稍后再试');
                    break;
                }
            }

            //判断是否已经全部打分完成
            if (ReportItemModel::factory()->isOver($reportId, $uid)) {
                //修改report_user 状态
                ReportUserModel::factory()->setStatus($reportId, $uid);
            }
            $this->sendSuccessResult('打分成功');

        } else {
            $this->sendErrorResult('请求方式错误');
        }
    }

    /**
     * @desc 判断指标是否为减分项
     */
    protected function isMinusQuota($quota)
    {
        if (!$quota || !isset($quota->name)) {
            return false;
        }
        return strpos($quota->name, '减分') !== false || strpos($quota->name, '扣分') !== false;
    }

    /**
     * @desc 判断指标是否为加分项
     */
    protected function isPlusQuota($quota)
    {
        if (!$quota || !isset($quota->name)) {
            return false;
        }
        return strpos($quota->name, '加分') !== false || strpos($quota->name, '奖励') !== false;
    }


    /**
     * @desc    评分人重置本人已提交的评分
     */
    public function resetMyPointAction()
    {
        if (!$this->request->isAjax()) {
            $this->sendErrorResult('请求方式错误');
        }

        $reportId = intval($this->request->get('reportId'));
        $uid      = intval($this->request->get('uid'));
        if (!$reportId || !$uid) {
            $this->sendErrorResult('参数错误');
        }

        $reportinfo = ReportModel::findFirst($reportId);
        if (!$reportinfo) {
            $this->sendErrorResult('考核表不存在');
        }
        if (intval($reportinfo->ispoint) !== 1) {
            $this->sendErrorResult('考核表已结束或已归档，不能重置评分');
        }

        $reportuser = ReportUserModel::findFirst(
            'report_id = ' . $reportId . ' and user_id = ' . $uid
        );
        if (!$reportuser) {
            $this->sendErrorResult('用户的考核表不存在');
        }

        $items = ReportItemModel::find(
            'report_id = ' . $reportId .
            ' and user_id = ' . $uid .
            ' and report_user_id = ' . intval($this->userId)
        );
        if (!$items || count($items) === 0) {
            $this->sendErrorResult('没有可重置的本人评分');
        }

        $hasSubmitted = false;
        foreach ($items as $item) {
            if (intval($item->report_time) > 0) {
                $hasSubmitted = true;
            }
        }
        if (!$hasSubmitted) {
            $this->sendErrorResult('本人评分尚未提交，无需重置');
        }

        foreach ($items as $item) {
            $item->report_time  = 0;
            $item->report_point = 0;
            if (!$item->save()) {
                $this->sendErrorResult('重置失败，请稍后再试');
            }
        }

        $reportuser->state = 0;
        if (!$reportuser->save()) {
            $this->sendErrorResult('重置失败，请稍后再试');
        }

        // 已公示时撤回公示，避免继续展示重置前的旧结果。
        if (intval($reportinfo->ispub) === 1) {
            $reportinfo->ispub = 0;
            if (!$reportinfo->save()) {
                $this->sendErrorResult('评分已清空，但撤回公示失败，请联系管理员');
            }
        }

        $this->sendSuccessResult('评分已重置，请重新评分');
    }


    /**
     *
     * @desc    查询操作
     * @date    2017年5月5日
     */
    public function searchAction()
    {
        $uid      = intval($this->userId);
        $userinfo = CompanyUserModel::findFirst($uid);

        if ($userinfo) {
            //当前账户权限
            $right = $userinfo->right;

            //当权限为全公司的时候   则获取部门列表
            if ($right == 3) {
                $departlist = CompanyDepartModel::find('company_id = ' . $userinfo->company_id);
            } else {
                //查看自己当前部门
                $departlist = CompanyDepartModel::find('dingding_id = ' . $userinfo->department_id . ' and company_id = ' . $userinfo->company_id);
            }
            $departjson = [];
            //封装成前端需要的json格式
            if ($departlist) {
                foreach ($departlist as $key => $depart) {
                    $departjson[$key]['value'] = $depart->id;
                    $departjson[$key]['text']  = $depart->name;
                }
            }

            //获取额外的   管理员设置的 可以查看的权限
            $extRole = UserViewRoleModel::factory()->getExtRole($this->userId);
            if (!empty($extRole)) {
                //二维数组序列化成一维数组 然后在合并去重
                foreach ($departjson as $key => $val) {
                    $departjson[$key] = serialize($val);
                }
                foreach ($extRole as $key => $val) {
                    $extRole[$key] = serialize($val);
                }
                $mergeArr   = array_unique(array_merge($departjson, $extRole));
                $departjson = [];
                foreach ($mergeArr as $val) {
                    $departjson[] = unserialize($val);
                }
            }
            $this->view->setVar('departjson', json_encode($departjson));
            $this->view->setVar('year', date('Y', time()));
            $this->view->setVar('searchtime', date('Y-m-d', time()));
        }
    }


    /**
     *
     * @desc    获取已经归档成功的被考核人列表
     * @date    2017年5月5日
     */
    public function SearchListAction()
    {
        $act    = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
        $isAjax = $this->request->isAjax();

        $dataList = $this->_getDataList();
        $this->view->setVar('dataList', $dataList);
        $this->view->setVar('full_page', 1);
        if ($isAjax) {
            $this->view->setMainView(false);
            $this->view->start();
            $this->view->setVar('full_page', 0);
            $this->view->render('bs', 'ajaxsearchlist');
            $this->view->finish();
            $dataList->content = $this->view->getContent();
            $this->sendSuccessResult($dataList);
        }
    }


    /**
     *
     * @desc    考核表详情
     * @date    2017年5月8日
     */
    public function storesDetailAction()
    {
        $gourl = $this->getHelper()->createUrl(['p' => 'bs/searctlist']);

        $request = $this->request;
        $id      = $request->get('id');         //报表id
        $uid     = $request->get('uid');        //被审核人id
        $sid     = $request->get('sid');        //归档id

        if (!$id || !$uid || !$sid || !is_numeric($id) || !is_numeric($uid) || !is_numeric($sid)) {
            Utils::showFrontMsg('参数错误', $gourl);
        }
        $id  = intval($id);
        $uid = intval($uid);
        $sid = intval($sid);

        $userinfo = CompanyUserModel::getUserAddDepartByids($uid);

        if (!$userinfo) {
            $this->sendErrorResult('用户不存在');
        }

        $reportinfo = ReportModel::findFirst($id);
        if (!$reportinfo) {
            Utils::showFrontMsg('考核信息不存在', $gourl);
        }

        $storesinfo = ReportStoresModel::findFirst($sid);
        if (!$storesinfo) {
            Utils::showFrontMsg('归档信息不存在！', $gourl);
        }

        $storestime = $storesinfo->storestime;

        $details = ReportStoresModel::factory()->getHasStores($id, $storestime, $this->userId);

        $this->view->setVar('userinfo', $userinfo);                            //被考核人的信息
        $this->view->setVar('details', $details);                              //考核的具体指标信息
        $this->view->setVar('reportinfo', $reportinfo);                        //考核报表信息
        $this->view->setVar('totalpoint', ReportStoresModel::factory()->getTotalPoint($id, $sid));
        $this->view->setVar('extraavgpoint', ExtraStoresReportItemModel::getAvgPoint($id));
        $this->view->setVar('extrareportdesc', ExtraReportDescModel::getDesc($id));
    }


    protected function checkSalaryMobile()
    {
        if (!$this->hasSalaryMobile()) {
            Utils::showFrontMsg('企业尚未开通员工薪酬查询，请联系HR确认。', $this->getHelper()->createUrl(array('p' => 'bs/newindex')));
        }
        return true;
    }

    protected function hasSalaryMobile()
    {
        $authMap = CompanyModuleAuthModel::getCompanyAuthMap($this->companyId);
        return CompanyModuleAuthModel::isEnabled($authMap, 'salary') && CompanyModuleAuthModel::isEnabled($authMap, 'salary', 'payslip');
    }

    protected function canViewSubordinateSalary()
    {
        return $this->scopeHasSalaryRows($this->getMobileSalaryScope()) ? 1 : 0;
    }

    protected function getMobileSalaryScope()
    {
        $userInfo = CompanyUserModel::findFirst('company_id=' . intval($this->companyId) . ' and id=' . intval($this->userId));
        if ($userInfo && intval($userInfo->is_admin) == 1) {
            return array('all' => 1, 'employee_ids' => array(), 'department_names' => array());
        }
        $departmentIds = SalaryViewRoleModel::factory()->getUserScope($this->companyId, $this->userId, 'department');
        $employeeIds = SalaryViewRoleModel::factory()->getUserScope($this->companyId, $this->userId, 'employee');
        return array(
            'all' => 0,
            'employee_ids' => $employeeIds,
            'department_names' => $this->getMobileSalaryDepartmentNames($departmentIds),
        );
    }

    protected function getMobileSalaryDepartmentNames($departmentIds)
    {
        $names = array();
        if (empty($departmentIds)) {
            return $names;
        }
        $ids = array();
        foreach ($departmentIds as $departmentId) {
            $departmentId = intval($departmentId);
            if ($departmentId > 0) {
                $ids[] = $departmentId;
            }
        }
        if (empty($ids)) {
            return $names;
        }
        $items = CompanyDepartModel::find('company_id=' . intval($this->companyId) . ' and id in (' . implode(',', array_unique($ids)) . ')');
        foreach ($items as $item) {
            $names[] = $item->name;
        }
        return array_unique($names);
    }

    protected function scopeHasSalaryRows($scope)
    {
        if (!empty($scope['all'])) {
            return true;
        }
        return !empty($scope['employee_ids']) || !empty($scope['department_names']);
    }

    protected function buildMobileSalarySummary($slips)
    {
        $summary = array('count' => 0, 'earning_total' => '0.00', 'deduction_total' => '0.00', 'net_total' => '0.00');
        $earning = 0;
        $deduction = 0;
        $net = 0;
        foreach ($slips as $slip) {
            $summary['count']++;
            $earning += floatval($slip['earning_total']);
            $deduction += floatval($slip['deduction_total']);
            $net += floatval($slip['net_amount']);
        }
        $summary['earning_total'] = sprintf('%.2f', round($earning, 2));
        $summary['deduction_total'] = sprintf('%.2f', round($deduction, 2));
        $summary['net_total'] = sprintf('%.2f', round($net, 2));
        return $summary;
    }

    protected function addMobileSalaryLog($actionCode, $objectType = '', $objectId = 0, $payrollMonth = '', $summary = '')
    {
        SalaryOperationLogModel::factory()->addLog($this->companyId, $this->userId, $actionCode, $objectType, $objectId, $payrollMonth, $summary);
    }

    /**
     * 获取当前登录顶顶用户信息
     */
    public function getuserinfo()
    {
        //记录当前的请求地址
        $cache = Helper::factory()->getCache();
        $cache->save('callbackurl', $_SERVER['REQUEST_URI']);

        //测试环境设置用户信息
        $appenv = Helper::factory()->getConfig('application_env');
        if ($appenv == 'dev') {
            $this->userId    = 68;
            $this->companyId = 5;
            return;
        }


        $user_id              = $this->session->get('user_id');
        $company_id           = $this->session->get('company_id');
        $dingding_user_id     = $this->session->get('dingding_user_id');
        $companyhaskey        = $this->session->get('company_haskey');
        $currentcompanyhaskey = $this->session->get('current_company_haskey');
        //debug
        if ($user_id == '17632') {
            $this->session->set('user_id', '55');
            $this->session->set('dingding_user_id', '062321564423354698');
            $user_id          = $this->session->get('user_id');
            $dingding_user_id = $this->session->get('dingding_user_id');
        }
        if ($user_id > 0 && $company_id > 0 && ($companyhaskey == $currentcompanyhaskey)) {
            //判断公司是否过期
            $companyinfo = CompanyModel::findFirst($company_id);
            if (!$companyinfo || $companyinfo->status == 0) {
                Utils::showFrontMsg('公司不存在或未激活');
            }
            if (($companyinfo->expire_time != -1) && ($companyinfo->expire_time < time())) {
                Utils::showFrontMsg('使用时间已经到期，请联系管理员');
            }

            //判断当前登录用户所属几个公司  如果用户属于两个公司  并且恰好两个公司都在使用此微应用  那么则应该判断当前是哪个公司
            $this->userId    = $user_id;
            $this->companyId = $company_id;
        } else {
            $this->redirectLogin();
        }
    }

    private function redirectLogin()
    {

        $browser = $this->checkBrowser();
        if ($browser == 'dding') {
            $this->redirect('dding/login');
        } else {
            $this->redirect('wp/loginpage');
        }
    }


    private function checkBrowser()
    {
        $userAgent = $this->request->getUserAgent();
        if (strpos($userAgent, 'DingTalk') !== false) {
            return 'dding';
        } else {
            return 'other';
        }


    }


    /**
     * @desc    评价指标
     * @param
     * @return
     */
    public function commentQuotaAction()
    {
        $gourl   = $this->request->getHTTPReferer();
        $quotaId = intval($this->request->get('quota_id'));
        $content = $this->request->get('content');
        $rId     = intval($this->request->get('rid'));
        $id      = intval($this->request->get('id'));
        if (!$quotaId || !$rId) {
            Utils::showFrontMsg('参数错误', $gourl);
        }

        if (!$content) {
            Utils::showFrontMsg('请填写点评内容', $gourl);
        }

        $quotaInfo = QuotaModel::factory()->findFirst($quotaId);
        if (!$quotaInfo) {
            Utils::showFrontMsg('无效的指标', $gourl);
        }


        $data = [
            'qid'     => $quotaId,
            'rid'     => $rId,
            'user_id' => $this->userId,
            'content' => trim($content),
        ];
        if ($id) {
            $quotaComment = QuotaCommentModel::findFirst($id);
            if (!$quotaComment) {
                $this->sendErrorResult('此点评不存在');
            }

            $res = $quotaComment->save($data);
        } else {
            //验证当前是否已经进行点评过
            $isExists = QuotaCommentModel::factory()->isExists($this->userId, $quotaId, $rId);
            if ($isExists) {
                $this->sendErrorResult('您只能点评一次');
            }

            $data['created_at'] = Helper::factory()->getTime()->gmtime();
            $res                = QuotaCommentModel::factory()->save($data);
        }

        if ($res) {
            $this->sendSuccessResult('success');
        }
        $this->sendErrorResult('error');
    }


    /**
     * @desc    获取评论
     * @param
     * @return
     */
    public function getQuotaCommentAction()
    {
        $request  = $this->request;
        $quotaId  = intval($request->get('quota_id'));
        $rId      = intval($request->get('rid'));
        $page     = ($request->get('page') && $request->get('page') > 0) ? intval($request->get('page')) : 1;
        $pageSize = ($request->get('pagesize') && $request->get('pagesize') > 0 && $request->get('pagesize') < 11) ? intval($request->get('pagesize')) : 10;
        $where    = 'qid=' . $quotaId . ' and rid=' . $rId;

        $dataList = QuotaCommentModel::factory()->getComment($where, $page, $pageSize);
        if (is_string($dataList)) {
            $this->sendErrorResult($dataList);
        }

        $this->sendSuccessResult(QuotaCommentModel::factory()->renderComment($dataList));

    }


    /**
     * @desc    获取用户列表
     * @param
     * @return
     */
    protected function _getDataList()
    {
        $datalist          = new \stdClass();
        $datalist->datanum = 0;

        $page     = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $page     = $page < 1 ? 1 : $page;
        $pagesize = isset($_REQUEST['pagesize']) ? intval($_REQUEST['pagesize']) : 10;

        $request  = $this->request;
        $departId = $request->get('departId');                      //部门
        $name     = $request->get('name');                          //姓名
        $stime    = $request->get('stime');                         //开始时间
        $etime    = $request->get('etime');                         //结束时间
        $sobj     = $request->get('searchuser');                    //查询对象

        $filter               = [];
        $filter['searchuser'] = $sobj;

        $where = ' 1=1 ';
        if ($name) {
            $name           = trim($name);
            $where          .= ' and u.name like "%' . $name . '%"';
            $filter['name'] = $name;
        }

        //由于暂时不知道  搜索页面 怎么获取部门的id   故暂时以名称查询   以后在修改为id查询
        if ($departId) {
            $departId           = trim($departId);
            $where              .= ' and d.name ="' . $departId . '"';
            $filter['departId'] = $departId;
        }

        if ($stime) {
            $where           .= ' and r.created > ' . strtotime($stime . "00:00:00");
            $filter['stime'] = $stime;
        }

        if ($etime) {
            $where           .= ' and r.created < ' . strtotime($etime . " 23:59:59");
            $filter['etime'] = $etime;
        }

        $datalist->pagesize = $pagesize;
        $datalist->filter   = json_encode($filter);

        //判断查询入口
        if ($sobj == 'self') {
            $where .= ' and s.user_id = ' . $this->userId;
        } else {
            //获取当前登录用户额外的查看权限
            $extRole = UserViewRoleModel::factory()->getUserViewRole($this->userId);
            $userIds = '';
            if ($extRole) {
                $userIds = CompanyUserModel::factory()->getOneDepartUsers(implode(',', array_unique($extRole)));
            }
            //判断当前登录用户的查看权限
            $userinfo = CompanyUserModel::findFirst($this->userId);
            $right    = $userinfo->right;
            //如果为3  则查看全部  不做限制
            if ($right == 1) {
                //只能查看自己的
                $where .= $userIds ? ' and s.user_id in(' . $userIds . ',' . $this->userId . ')' : ' and s.user_id = ' . $this->userId;
            }
            if ($right == 2) {
                //查看同一部门
                $departuser = CompanyUserModel::factory()->getOneDepartUsers($userinfo->department_id);
                if ($departuser) {
                    $where .= ' and s.user_id in (' . $departuser . ')';
                    $where .= $userIds ? ' and s.user_id in(' . $userIds . ',' . $departuser . ')' : ' and s.user_id in (' . $departuser . ')';
                } else {
                    $where .= $userIds ? ' and s.user_id in(' . $userIds . ',' . $this->userId . ')' : ' and s.user_id = ' . $this->userId;
                }
            }
        }
        file_put_contents('searchlist_sql.log', $where . PHP_EOL, FILE_APPEND);
        $items           = ReportStoresModel::factory()->getHasStoresList($where, $page, $pagesize);
        $datalist->items = $items;
        $datalist->page  = $page;
        if (count($items) > 0) {
            $datalist->datanum = count($items);
        }

        return $datalist;
    }
}
