<?php
/**
 *
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */

namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Dacang\Model\DepartmentModel;
use ScshuxCms\Core\Tree;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Dacang\Helper\DingdingOapi;
use ScshuxCms\Dacang\Model\CompanyDepartModel;
use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\CompanyUserModel;

class DepartmentController extends FrontendBaseController
{

    protected static $_callbankarr = [];

    /**
     *
     * @desc 用户列表
     * @date 2017年4月1日
     */
    public function indexAction()
    {
        return $this->response->redirect(
            Helper::factory()->createUrl(['p' => 'personnel/index'])
        );

        $act    = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
        $user = Helper::factory()->getSession()->get('_user');
        $this->view->setVar('canManagePersonnel', !empty($user->is_admin) ? 1 : 0);
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
            $this->view->render('department', 'index');
            $this->view->finish();
            $dataList->content = $this->view->getContent();
            $this->sendSuccessResult($dataList);
        }

    }

    /**
     * 同步数据
     */
    public function asyncAction()
    {
        $source = isset($_REQUEST['from']) ? trim($_REQUEST['from']) : '';
        $fromPersonnel = $source == 'personnel';
        $fromSalary = $source == 'salary';
        if ($fromPersonnel) {
            $backUrl = $this->getHelper()->createUrl(['p' => 'personnel/index']);
        } else if ($fromSalary) {
            $backUrl = $this->getHelper()->createUrl(['p' => 'salary/employeesync']);
        } else {
            $backUrl = $this->getHelper()->createUrl(['p' => 'firm/staff']);
        }
        if (!$this->isPersonnelManager()) {
            Utils::showMsg('只有企业管理员可以同步通讯录', $backUrl);
        }
        $type    = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'department';
        if ($type == 'department') {
            $nextParams = ['p' => 'department/async', 'type' => 'user'];
            if ($fromPersonnel || $fromSalary) {
                $nextParams['from'] = $source;
            }
            $backUrl = $this->getHelper()->createUrl($nextParams);
            DingdingOapi::factory()->asyncDepartment($this->companyId);
            Utils::showMsg('同步部门数据成功!', $backUrl);

        } else if ($type == 'user') {
            DingdingOapi::factory()->asyncSimplelist($this->companyId);
        }
        Utils::showMsg('同步数据成功!', $backUrl);
    }

    public function UploadExcelAction()
    {
        $source = isset($_REQUEST['from']) ? trim($_REQUEST['from']) : '';
        if ($source == 'personnel') {
            $gourl = Helper::factory()->createUrl(['p' => 'personnel/index']);
        } else if ($source == 'salary') {
            $gourl = Helper::factory()->createUrl(['p' => 'salary/employeesync']);
        } else {
            $gourl = Helper::factory()->createUrl(['p' => 'department/index']);
        }
        if (!$this->isPersonnelManager()) {
            Utils::showMsg('只有企业管理员可以导入人员信息', $gourl);
        }
        $ispost = $this->request->isPost();
        if ($ispost) {
            if (
                !isset($_FILES['exceltpl']) ||
                empty($_FILES['exceltpl']['name']) ||
                intval($_FILES['exceltpl']['error']) !== UPLOAD_ERR_OK
            ) {
                Utils::showMsg('请上传文件', $gourl);
            }
            $extname = strtolower(pathinfo($_FILES['exceltpl']['name'], PATHINFO_EXTENSION));
            if (!in_array($extname, ['xls', 'xlsx'])) {
                Utils::showMsg('请上传Excel文件（xls或xlsx）', $gourl);
            }

            $file = Utils::uploadFile('exceltpl', 'excel');
            if (!$file) {
                Utils::showMsg('文件上传失败，请使用系统模板并重新上传', $gourl);
            }

            //构建字段 => 位置 数组
            $array = [
                'departname1' => 'A',
                'departname2' => 'B',
                'departname3' => 'C',
                'departname4' => 'D',
                'departname5' => 'E',
                'username'    => 'F',
                'mobile'      => 'G',
            ];


            //调用phpexcel类   读取excel 文件
            try {
                $data = Utils::readExcel(WEBROOT . $file, $array);
            } catch (\Throwable $exception) {
                error_log('Personnel Excel import failed: ' . $exception->getMessage());
                Utils::showMsg('Excel读取失败，请确认使用系统模板且文件未损坏', $gourl);
            }
            if (!$data) {
                Utils::showMsg('读取excel错误', $gourl);
            }

            //根据获取的data数据  添加到mysql
            if ($this->createUser($data)) {
                Utils::showMsg('保存成功', $gourl);
            } else {
                Utils::showMsg('保存失败', $gourl);
            }
        } else {
            Utils::showMsg('error', $gourl);
        }
    }


    public function exportExcelTplAction()
    {
        ob_clean();
        $filename = WEBROOT . '/data/usertpl.xls';
        $name     = '用户模版';
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            header("Content-type:application/vnd.ms-excel");
            header("Content-Disposition:filename=" . $name . '.xls');

            echo $content;
        }
        exit();

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
        $pagesize = isset($_REQUEST['pagesize']) ? intval($_REQUEST['pagesize']) : 150;
        $filter   = [];

        $where = ' company_id = ' . $this->companyId;

        $dataList = new \stdClass();

        /*统计*/
        $countInfo = DepartmentModel::query()
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
        $items  = DepartmentModel::query()
            ->where($where)
            ->orderBy('id desc')
            ->limit($pagesize, $offset)
            ->execute()
            ->toArray();


        $treeObj         = new Tree($items, 'dingding_id', 'name', 'dingding_parent_id');
        $items           = $treeObj->unlimitedForLevel('    ', 1);
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


    protected function createUser($data)
    {

        $return             = false;
        self::$_callbankarr = [];

        if (!$data || !is_array($data)) {
            return $return;
        }


        $departArr = $userArr = [];
        foreach ($data as $value) {


            //部门名称
            $departname1 = $value['departname1'];
            $departname2 = $value['departname2'];
            $departname3 = $value['departname3'];
            $departname4 = $value['departname4'];
            $departname5 = $value['departname5'];

            if (empty($value['departname1']) || empty($value['username']) || empty($value['mobile'])) {
                continue;
            }

            if ($departname5) {
                $departArr[$departname1][$departname2][$departname3][$departname4][$departname5]['user-list'][] = [
                    'username' => $value['username'],
                    'mobile'   => $value['mobile'],
                ];
                continue;
            }

            if ($departname4) {
                $departArr[$departname1][$departname2][$departname3][$departname4]['user-list'][] = [
                    'username' => $value['username'],
                    'mobile'   => $value['mobile'],
                ];
                continue;
            }

            if ($departname3) {
                $departArr[$departname1][$departname2][$departname3]['user-list'][] = [
                    'username' => $value['username'],
                    'mobile'   => $value['mobile'],
                ];
                continue;
            }

            if ($departname2) {
                $departArr[$departname1][$departname2]['user-list'][] = [
                    'username' => $value['username'],
                    'mobile'   => $value['mobile'],
                ];
                continue;
            }

            if ($departname1) {
                $departArr[$departname1]['user-list'][] = [
                    'username' => $value['username'],
                    'mobile'   => $value['mobile'],
                ];
            }
        }

        // Excel import is incremental. Existing departments and people must
        // remain available when the latest roster is imported again.
        $rootDepartment = (new CompanyDepartModel())->findFirst(
            'company_id=' . intval($this->companyId) . ' and dingding_id=1'
        );
        if (empty($rootDepartment)) {
            $rootDepartment = new CompanyDepartModel();
			$rootDepartment->saveData([
                'name'        => $this->companyName,
                'company_id'  => $this->companyId,
                'dingding_id' => 1,
            ]);
        }

        $departId = 0;
        foreach ($departArr as $departName1 => $depart1) {

            if ($departName1 != 'user-list') {
                $departId = $this->saveDepart('', $departName1);
                if ($depart1['user-list']) {
                    $this->saveUser($depart1['user-list'], $departId);
                }

                foreach ($depart1 as $departName2 => $depart2) {
                    if ($departName2 != 'user-list') {
                        $departId = $this->saveDepart($departName1, $departName2);
                        if ($depart2['user-list']) {
                            $this->saveUser($depart2['user-list'], $departId);
                        }
                        foreach ($depart2 as $departName3 => $depart3) {
                            if ($departName3 != 'user-list') {
                                $departId = $this->saveDepart($departName2, $departName3);
                                if ($depart3['user-list']) {
                                    $this->saveUser($depart3['user-list'], $departId);
                                }
                                foreach ($depart3 as $departName4 => $depart4) {
                                    if ($departName4 != 'user-list') {
                                        $departId = $this->saveDepart($departName3, $departName4);
                                        if ($depart4['user-list']) {
                                            $this->saveUser($depart4['user-list'], $departId);
                                        }
                                        foreach ($depart4 as $departName5 => $depart5) {
                                            if ($departName5 != 'user-list') {
                                                $departId = $this->saveDepart($departName4, $departName5);
                                                if ($depart5['user-list']) {
                                                    $this->saveUser($depart5['user-list'], $departId);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        return true;
    }

    private function saveUser($userArr, $departID)
    {
        if (empty($userArr) || empty($departID)) {
            return;
        }

        $companyUserModel = new CompanyUserModel();
        $userTable = $companyUserModel->getSource();
        $columns = $this->getTableColumns($userTable);
        $mobileColumn = $this->getCompanyUserMobileColumn($userTable);

        foreach ($userArr as $user) {
            $data = [
                'company_id'    => $this->companyId,
                'department_id' => $departID,
                'name'          => $user['username'],
            ];
            if ($mobileColumn != '') {
                $data[$mobileColumn] = $user['mobile'];
            }
            $optional = [
                'created'   => Helper::factory()->getTime()->gmtime(),
                'right'     => 1,
                'is_admin'  => 0,
                'is_boss'   => 0,
                'active'    => 0,
                'is_leader' => 0,
                'addreport' => 0,
                'passwd'    => md5(123456),
            ];
            foreach ($optional as $column => $value) {
                if (isset($columns[$column])) {
                    $data[$column] = $value;
                }
            }

            //不存在则添加
            $where = "company_id=" . intval($this->companyId);
            if ($mobileColumn != '') {
                $where .= " and `" . $mobileColumn . "`='" . addslashes($user['mobile']) . "'";
            } else {
                $where .= " and name='" . addslashes($user['username']) . "' and department_id=" . intval($departID);
            }
            $isExists = $companyUserModel->findFirst($where);
            if (!$isExists) {
                $companyUserModel = new CompanyUserModel();
				$companyUserModel->saveData($data);

                if (isset($columns['dingding_user_id'])) {
					$companyUserModel->saveData([
                        'dingding_user_id' => $companyUserModel->id,
                    ]);
                }
            } else {
                // Re-importing the roster must refresh the employee's current
                // name and department instead of leaving a stale department id.
				$isExists->saveData([
                    'name'          => $user['username'],
                    'department_id' => $departID,
                ]);
            }
        }
    }

    private function getCompanyUserMobileColumn($userTable)
    {
        foreach (['jobnumber', 'mobile', 'phone'] as $column) {
            if ($this->tableHasColumn($userTable, $column)) {
                return $column;
            }
        }
        return '';
    }

    private function getTableColumns($tableName)
    {
        $items = $this->getDI()->get('db')->query('SHOW COLUMNS FROM `' . $tableName . '`')->fetchAll();
        $columns = [];
        foreach ($items as $item) {
            $columns[$item['Field']] = 1;
        }
        return $columns;
    }

    protected function isPersonnelManager()
    {
        $user = Helper::factory()->getSession()->get('_user');
        return !empty($user->is_admin);
    }

    private function tableHasColumn($tableName, $column)
    {
        $item = $this->getDI()->get('db')->query("SHOW COLUMNS FROM `" . $tableName . "` LIKE '" . addslashes($column) . "'")->fetch();
        return $item ? true : false;
    }

    private function saveDepart($parentDepartName, $departName)
    {

        $departInfo = (new CompanyDepartModel())->findFirst("company_id=" . $this->companyId . " and name='{$departName}'");
        if (empty($departInfo)) {
            $data = [
                'name'       => $departName,
                'company_id' => $this->companyId,
            ];

            $companyDepartModel = new CompanyDepartModel();
			$companyDepartModel->saveData($data);


            $parentDepartInfo = (new CompanyDepartModel())->findFirst("company_id=" . $this->companyId . " and name='{$parentDepartName}'");
			$companyDepartModel->saveData([
                'dingding_id'        => $companyDepartModel->id,
                'dingding_parent_id' => isset($parentDepartInfo->id) ? $parentDepartInfo->id : 1,
            ]);
            return $companyDepartModel->id;
        } else {
            return $departInfo->id;
        }
    }


}
