<?php
/**
 * webpage
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */

namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Core\Helper;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Dacang\Model\CompanyUserModel;

class  WpController extends FrontendBaseController
{

    public function initialize()
    {
        $do = $this->request->get('do_action');
        if ($do != 'logout') {
            $userId = $this->session->get('user_id');
            if ($userId) {
                $this->redirect('bs/newindex');
            }
        }

    }


    public $userId    = '';                //当前登录钉钉客户端的用户id
    public $companyId = '';                //当前登录钉钉客户端的用户所属公司id

    public function loginPageAction()
    {

    }

    public function changePassWordPageAction()
    {
        $mobile = $_GET['mobile'];
        $this->view->setVar('mobile', $mobile);
    }

    public function loginAction()
    {
        $goUrl = $this->getHelper()->createUrl(['p' => 'wp/loginpage']);


        //设置顶顶的回调地址
        $mobile = $_POST['mobile'];
        $passwd = $_POST['pass_word'];
        if (empty($mobile)) {
            Utils::showFrontMsg('请输入手机号', $goUrl);
        }
        if (empty($passwd)) {
            Utils::showFrontMsg('请输入密码', $goUrl);
        }

        if (!Utils::isMobile($mobile)) {
            Utils::showFrontMsg('请输入正确的手机号', $goUrl);
        }

        //判断手机号是否存在
        $userInfo = (new CompanyUserModel())->findfirst('jobnumber=' . $mobile);
        if (!$userInfo) {
            Utils::showFrontMsg('用户不存在', $goUrl);
        }


        if ($passwd == 123456) {
            $goUrl = $this->getHelper()->createUrl(['p' => 'wp/changepasswordpage?mobile=' . $mobile]);
            Utils::showFrontMsg('请重置登录密码', $goUrl);
        }


        $encryPass = md5($passwd);
        if ($encryPass != $userInfo->passwd) {
            Utils::showFrontMsg('密码错误', $goUrl);
        }


        //判断公司是否已经过期
        $companyId   = $userInfo->company_id;
        $companyinfo = CompanyModel::findFirst($companyId);
        if (!$companyinfo) {
            Utils::showFrontMsg('公司信息不存在', $goUrl);
        }

        if (($companyinfo->expire_time < time()) && ($companyinfo->expire_time != -1)) {
            Utils::showFrontMsg('试用时间已经到期，请联系管理员', $goUrl);
        }

        $data       = [
            'id'         => $userInfo->id,
            'company_id' => $userInfo->company_id,
        ];
        $session_id = $_COOKIE['__scsxsid'];
        Helper::factory()->getCache()->save($session_id, json_encode($data), '31536000');


        $this->session->set('user_id', $userInfo->id);
        $this->session->set('company_id', $userInfo->company_id);


        $goUrl = $this->getHelper()->createUrl(['p' => 'bs/newindex']);
        Utils::showFrontMsg('登录成功', $goUrl);

    }


    public function changePasswordAction()
    {

        $goUrl = $this->getHelper()->createUrl(['p' => 'wp/changepasswordpage']);

        $mobile    = $_POST['mobile'];
        $newPasswd = $_POST['pass_word'];
        if (empty($newPasswd)) {
            Utils::showFrontMsg('请输入密码', $goUrl);
        }

        if (empty($mobile)) {
            Utils::showFrontMsg('请输入手机号码', $goUrl);

        }

        $userInfo = (new CompanyUserModel())->findfirst('jobnumber=' . $mobile);
        if (!$userInfo) {
            Utils::showFrontMsg('用户不存在', $goUrl);
        }


        $newPasswd = md5($newPasswd);
		$userInfo->saveData([
            'passwd' => $newPasswd,
        ]);

        $goUrl = $this->getHelper()->createUrl(['p' => 'wp/loginpage']);
        Utils::showFrontMsg('更改成功', $goUrl);
    }

    public function logoutAction()
    {
        $this->session->remove('user_id');
        $this->session->remove('company_id');
        setcookie("__scsxsid", "");


        $goUrl = $this->getHelper()->createUrl(['p' => 'bs/newindex']);
        Utils::showFrontMsg('退出成功', $goUrl);


    }
}