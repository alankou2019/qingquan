<?php
/**
 * 用户管理
 */

namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use ScshuxCms\User\Model\UserModel;
use ScshuxCms\User\Model\UserManageRoleModel;
use ScshuxCms\Dacang\Model\DepartmentModel;
use ScshuxCms\User\Model\UserViewRoleModel;
use ScshuxCms\Dacang\Model\CompanyUserModel;

class UserController extends FrontendBaseController
{

    /**
     *
     * @desc 用户列表
     * @date 2017年4月1日
     */
    public function indexAction()
    {
        $act    = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
        $isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
        if ($act == 'remove') {
            $this->_remove($_REQUEST['id']);
        }

        $dataList = $this->_getDataList();

        $this->view->setVar('dataList', $dataList);
        $this->view->setVar('full_page', 1);
        if ($isAjax) {
            $this->view->setMainView(false);
            $this->view->start();
            $this->view->setVar('full_page', 0);
            $this->view->render('user', 'index');
            $this->view->finish();
            $dataList->content = $this->view->getContent();
            $this->sendSuccessResult($dataList);
        }

    }

    /**
     *
     * @desc 用户编辑
     * @date 2017年4月1日
     */
    public function editAction()
    {
        $itemId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        if ($itemId > 0) {
            $item = UserModel::factory()->findFirst('user_id=' . $itemId);
            if (empty($item)) {
                Utils::showMsg('修改的记录不存在!');
            }
            $this->view->setVar('item', $item);
        }
    }




    /**
     *
     * @desc 用户保存
     * @date 2017年4月1日
     */
    public function saveAction()
    {
        $backUrl = $this->getHelper()->createUrl(['p' => 'user/index']);
        if ($this->request->isPost()) {
            $postData = $_POST;

            if (empty($postData['user_name'])) {
                Utils::showMsg('请添加用户名称!', $backUrl);
            }

            $phone = trim($postData['phone']);
            //封装数据
            $data = [
                'user_name' => trim($postData['user_name']),
                'true_name' => trim($postData['true_name']),
                'phone'     => $phone,
            ];

            if ($postData['new_pass']) {
                $data['password'] = md5(trim($postData['new_pass']));
            }

            //判断是修改还是添加
            $id = intval($postData['user_id']);
            if (empty($id)) {
                //验证手机号是否存在
                $isHas = UserModel::factory()->loadUserByUserName($postData['user_name']);
                if ($isHas) {
                    Utils::showMsg('用户名已经存在 ，请从新输入!', $backUrl);
                }

                $data['company_id'] = $this->companyId;
                $data['created']    = Helper::factory()->getTime()->gmtime();
                $data['reg_ip']     = Utils::getIP();

                $result = UserModel::factory()->save($data);

            } else {
                $item = UserModel::factory()->findFirst('user_id=' . $id);
                if (empty($item)) {
                    Utils::showMsg('修改的记录不存在!', $backUrl);
                }
                $result = $item->save($data);
            }
            if ($result) {
                Utils::showMsg('操作成功!', $backUrl);
            } else {
                Utils::showMsg('操作失败!', $backUrl);
            }

        } else {
            Utils::showMsg('不支持的请求方式!', $backUrl);
        }
    }


    /**
     * @desc    用户管理权限
     * @param
     * @return
     */
    public function userManageRoleAction()
    {
        $this->layout = '';
        $gourl        = Helper::factory()->createUrl(['p' => 'user/index']);
        $userId       = $this->request->get('user_id');
        if (!$userId) {
            Utils::showMsg('error', $gourl);
        }
        $userInfo = UserModel::findFirst($userId);
        if (!$userInfo) {
            Utils::showMsg('用户不存在', $gourl);
        }
        if ($userInfo->company_id != $this->companyId) {
            Utils::showMsg('error!', $gourl);
        }

        //用户拥有的权限
        $userRole = [];
        $userRole = UserManageRoleModel::factory()->getUserManageRole($userId);
        //当前公司的所有部门列表
        $departList = DepartmentModel::TreeDepartList($this->companyId);
        if ($departList) {
            foreach ($departList as $depart) {
                $depart->isChecked = 0;
                if (in_array($depart->id, $userRole)) {
                    $depart->isChecked = 1;
                }
            }
        }

        $departList = Utils::formatTree($departList);
        $this->view->setVar('departList', $departList);
        $this->view->setVar('user_id', $userId);
    }


    /**
     * @desc    用户管理权限保存
     * @param
     * @return
     */
    public function userManageRoleSaveAction()
    {
        $gourl  = Helper::factory()->createUrl(['p' => 'user/index']);
        $userId = intval($this->request->get('user_id'));
        if (!$userId) {
            Utils::showMsg('error', $gourl);
        }

        //先删除 在添加
        UserManageRoleModel::factory()->deleteBySql('user_id=' . $userId);
        $roleArr = $this->request->get('role');
        if (!empty($roleArr)) {
            $nowtime = Helper::factory()->getTime()->gmtime();
            foreach ($roleArr as $role) {
                $dataArr = [
                    'user_id'    => $userId,
                    'depart_id'  => $role,
                    'created_at' => $nowtime,
                ];
                $res     = UserManageRoleModel::factory()->save($dataArr);
                UserManageRoleModel::delFactory();
            }
        }
        Utils::showMsg('操作成功', $gourl);
    }


    /**
     * @desc    用户查看权限
     * @param
     * @return
     */
    public function userViewRoleAction()
    {
        $this->layout = '';
        $gourl        = Helper::factory()->createUrl(['p' => 'firm/staff']);
        $userId       = $this->request->get('user_id');
        if (!$userId) {
            Utils::showMsg('error', $gourl);
        }
        $userInfo = CompanyUserModel::findFirst($userId);
        if (!$userInfo) {
            Utils::showMsg('用户不存在', $gourl);
        }
        if ($userInfo->company_id != $this->companyId) {
            Utils::showMsg('error!', $gourl);
        }

        //用户拥有的权限
        $userRole = [];
        $userRole = UserViewRoleModel::factory()->getUserViewRole($userId);
        //当前公司的所有部门列表
        $departList = DepartmentModel::TreeDepartList($this->companyId);
        if ($departList) {
            foreach ($departList as $depart) {
                $depart->isChecked = 0;
                if (in_array($depart->id, $userRole))
                    $depart->isChecked = 1;
            }
        }
        $departList = Utils::formatTree($departList);
        $this->view->setVar('departList', $departList);
        $this->view->setVar('user_id', $userId);
    }


    /**
     * @desc    用户管理权限保存
     * @param
     * @return
     */
    public function userViewRoleSaveAction()
    {
        $gourl  = Helper::factory()->createUrl(['p' => 'user/index']);
        $userId = intval($this->request->get('user_id'));
        if (!$userId) {
            Utils::showMsg('error', $gourl);
        }

        //先删除 在添加
        UserViewRoleModel::factory()->deleteBySql('user_id=' . $userId);
        $roleArr = $this->request->get('role');
        if (!empty($roleArr)) {
            $nowtime = Helper::factory()->getTime()->gmtime();
            foreach ($roleArr as $role) {
                $dataArr = [
                    'user_id'    => $userId,
                    'depart_id'  => $role,
                    'created_at' => $nowtime,
                ];
                $res     = UserViewRoleModel::factory()->save($dataArr);
                UserViewRoleModel::delFactory();
            }
        }
        Utils::showMsg('操作成功', $gourl);
    }


    /**
     *
     * @desc 获取用户列表
     * @date 2017年4月1日
     */
    protected function _getDataList()
    {
        /*条件*/
        $page     = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $page     = $page < 1 ? 1 : $page;
        $pagesize = isset($_REQUEST['pagesize']) ? intval($_REQUEST['pagesize']) : 15;
        $filter   = [];

        $where = ' company_id = ' . $this->companyId;

        if (isset($_REQUEST['name'])) {
            $filter['name'] = trim($_REQUEST['name']);
            $where          .= " and  user_name  like '%{$filter['name']}%'";
        }


        $dataList = new \stdClass();

        /*统计*/
        $countInfo = UserModel::query()
            ->where($where)
            ->columns('count(*) as num')
            ->execute();

        $dataList->count       = $countInfo[0]->num;
        $dataList->currentPage = $page;
        $dataList->pageSize    = $pagesize;
        $dataList->pageCount   = ceil($dataList->count / $dataList->pageSize);
        $dataList->filter      = $filter;
        /*加载数据*/
        $offset = ($page - 1) * $pagesize;
        $items  = UserModel::query()
            ->where($where)
            ->orderBy('user_id desc')
            ->limit($pagesize, $offset)
            ->execute()
            ->toArray();

        $dataList->items = $items;
        return $dataList;
    }

    /**
     * 删除数据
     * @param  $ids
     */
    protected function _remove($ids)
    {
        if ($ids) {
            $items = UserModel::factory()->find('user_id in(' . $ids . ')');
            foreach ($items as $item) {
                if ($item->is_admin == 1) {
                    continue;
                } else {
                    $item->delete();
                }
            }
        }
    }

}