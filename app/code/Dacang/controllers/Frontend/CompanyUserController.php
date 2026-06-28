<?php

namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Core\Helper\Utils;

class CompanyUserController extends FrontendBaseController
{

    public function changePasswordAction()
    {
        $itemId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        if ($itemId > 0) {
            $item = CompanyUserModel::factory()->findFirst('id=' . $itemId);
            if (empty($item)) {
                Utils::showMsg('修改的记录不存在!');
            }
            $this->view->setVar('item', $item);
        }
    }


    public function savePasswordAction()
    {
        $backUrl = $this->getHelper()->createUrl(['p' => 'firm/staff']);
        if ($this->request->isPost()) {


            $data    = [];
            $newPass = $_POST['new_pass'];
            $userId  = $_POST['id'];
            if (empty($userId)) {
                Utils::showMsg('操作成功!', $backUrl);
            }
            if ($newPass) {
                $data['password'] = md5(trim($newPass));
            }


            $userModel = new CompanyUserModel();
            $userInfo  = $userModel->findFirst($userId);
            if (empty($userInfo)) {
                Utils::showMsg('操作成功!', $backUrl);
            }


            if ($data) {
                $userModel->save($data);
            }
            Utils::showMsg('操作成功!', $backUrl);

        } else {
            Utils::showMsg('不支持的请求方式!', $backUrl);
        }

    }


}