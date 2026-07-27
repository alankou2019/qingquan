<?php
namespace ScshuxCms\Dacang\Helper;

use ScshuxCms\Dacang\Model\CompanyDepartModel;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Dacang\Model\PlatformDepartmentIdentityModel;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;
use ScshuxCms\Dacang\Model\PlatformUserIdentityModel;

class FeishuSyncService
{
    private $integration;
    private $client;

    public function __construct(PlatformIntegrationModel $integration)
    {
        $this->integration = $integration;
        $this->client = new FeishuClient($integration);
    }

    public function syncAll()
    {
        $companyId = intval($this->integration->company_id);
        $company = CompanyModel::findFirst($companyId);
        if (!$company) {
            throw new \RuntimeException('公司不存在');
        }

        $apiDepartments = $this->client->getAllDepartments();
        $departments = array(array(
            'open_department_id' => '0',
            'parent_department_id' => '',
            'name' => $company->name,
        ));
        foreach ($apiDepartments as $department) {
            $departments[] = $department;
        }

        $nextLocalCode = $this->getNextLocalDepartmentCode($companyId);
        $departmentCodeMap = array();
        $parentMap = array();
        $departmentModelMap = array();

        foreach ($departments as $department) {
            $externalId = $this->getDepartmentExternalId($department);
            if ($externalId === '') {
                continue;
            }
            $identity = PlatformDepartmentIdentityModel::findFirst(
                "company_id={$companyId} and platform='feishu' and external_department_id='" .
                addslashes($externalId) . "'"
            );
            $model = $identity ? CompanyDepartModel::findFirst(intval($identity->department_id)) : null;
            if (!$model) {
                $model = new CompanyDepartModel();
                $model->company_id = $companyId;
                $model->dingding_id = $nextLocalCode++;
                $model->dingding_parent_id = 0;
            }
            if (intval($model->dingding_id) <= 0) {
                $model->dingding_id = $nextLocalCode++;
            }
            $model->name = !empty($department['name']) ? $department['name'] : '未命名部门';
            if (!$model->save()) {
                throw new \RuntimeException('保存部门失败: ' . $model->name);
            }

            if (!$identity) {
                $identity = new PlatformDepartmentIdentityModel();
                $identity->company_id = $companyId;
                $identity->platform = 'feishu';
                $identity->external_department_id = $externalId;
                $identity->created_at = time();
            }
            $identity->department_id = $model->id;
            $identity->updated_at = time();
            if (!$identity->save()) {
                throw new \RuntimeException('保存飞书部门身份映射失败: ' . $model->name);
            }

            $departmentCodeMap[$externalId] = intval($model->dingding_id);
            $departmentModelMap[$externalId] = $model;
            $parentMap[$externalId] = isset($department['parent_department_id'])
                ? (string)$department['parent_department_id'] : '';
        }

        foreach ($departmentModelMap as $externalId => $model) {
            $parentExternalId = isset($parentMap[$externalId]) ? $parentMap[$externalId] : '';
            $model->dingding_parent_id = $parentExternalId !== '' && isset($departmentCodeMap[$parentExternalId])
                ? $departmentCodeMap[$parentExternalId] : 0;
            if (!$model->save()) {
                throw new \RuntimeException('更新部门层级失败: ' . $model->name);
            }
        }

        $seen = array();
        $created = 0;
        $updated = 0;
        foreach ($departmentCodeMap as $externalDepartmentId => $localDepartmentCode) {
            $users = $this->client->getDepartmentUsers($externalDepartmentId);
            foreach ($users as $userData) {
                $externalUserId = $this->getUserExternalId($userData);
                if ($externalUserId === '' || isset($seen[$externalUserId])) {
                    continue;
                }
                $seen[$externalUserId] = true;
                $identity = PlatformUserIdentityModel::getByExternalId(
                    $companyId,
                    'feishu',
                    $externalUserId
                );
                $user = $identity ? CompanyUserModel::findFirst(intval($identity->company_user_id)) : null;
                $isNew = !$user;
                if (!$user) {
                    $user = new CompanyUserModel();
                    $user->company_id = $companyId;
                    $user->created = time();
                    $user->right = 1;
                    $user->addreport = 0;
                    $user->is_leader = 0;
                }

                $user->department_id = $this->resolveUserDepartmentCode(
                    $userData,
                    $departmentCodeMap,
                    $localDepartmentCode
                );
                $user->name = !empty($userData['name']) ? $userData['name'] : $externalUserId;
                $user->active = $this->isUserActive($userData) ? 1 : 0;
                $user->avatar = $this->getAvatar($userData);
                $user->email = !empty($userData['email']) ? $userData['email'] : '';
                $user->jobnumber = !empty($userData['mobile'])
                    ? $userData['mobile']
                    : (!empty($userData['job_number']) ? $userData['job_number'] : '');
                $user->extattr = json_encode($userData, JSON_UNESCAPED_UNICODE);
                if (!$user->save()) {
                    throw new \RuntimeException('保存员工失败: ' . $user->name);
                }

                if (!$identity) {
                    $identity = new PlatformUserIdentityModel();
                    $identity->company_id = $companyId;
                    $identity->platform = 'feishu';
                    $identity->external_user_id = $externalUserId;
                    $identity->created_at = time();
                }
                $identity->company_user_id = $user->id;
                $identity->status = $user->active ? 1 : 0;
                $identity->updated_at = time();
                if (!$identity->save()) {
                    throw new \RuntimeException('保存飞书员工身份映射失败: ' . $user->name);
                }
                $isNew ? $created++ : $updated++;
            }
        }

        $activeIdentities = PlatformUserIdentityModel::find(
            "company_id={$companyId} and platform='feishu' and status=1"
        );
        if (!$seen && count($activeIdentities) > 0) {
            throw new \RuntimeException('飞书未返回任何员工，为防止误停用，已中止本次同步');
        }

        $disabled = 0;
        foreach ($activeIdentities as $identity) {
            if (!isset($seen[(string)$identity->external_user_id])) {
                $identity->status = 0;
                $identity->updated_at = time();
                $identity->save();
                $user = CompanyUserModel::findFirst(intval($identity->company_user_id));
                if ($user) {
                    $user->active = 0;
                    $user->save();
                }
                $disabled++;
            }
        }

        $this->integration->last_sync_at = time();
        $this->integration->last_error = '';
        $this->integration->updated_at = time();
        $this->integration->save();

        return array(
            'departments' => count($apiDepartments),
            'created_users' => $created,
            'updated_users' => $updated,
            'disabled_users' => $disabled,
        );
    }

    private function getNextLocalDepartmentCode($companyId)
    {
        $max = 0;
        foreach (CompanyDepartModel::find('company_id=' . intval($companyId)) as $department) {
            $max = max($max, intval($department->dingding_id));
        }
        return $max + 1;
    }

    private function getDepartmentExternalId($department)
    {
        if (array_key_exists('open_department_id', $department)) {
            return (string)$department['open_department_id'];
        }
        return isset($department['department_id']) ? (string)$department['department_id'] : '';
    }

    private function getUserExternalId($userData)
    {
        if (!empty($userData['open_id'])) {
            return (string)$userData['open_id'];
        }
        return !empty($userData['user_id']) ? (string)$userData['user_id'] : '';
    }

    private function resolveUserDepartmentCode($userData, $departmentCodeMap, $fallback)
    {
        $departmentIds = !empty($userData['department_ids']) && is_array($userData['department_ids'])
            ? $userData['department_ids'] : array();
        foreach ($departmentIds as $departmentId) {
            $departmentId = (string)$departmentId;
            if (isset($departmentCodeMap[$departmentId])) {
                return intval($departmentCodeMap[$departmentId]);
            }
        }
        return intval($fallback);
    }

    private function getAvatar($userData)
    {
        if (empty($userData['avatar'])) {
            return '';
        }
        if (is_string($userData['avatar'])) {
            return $userData['avatar'];
        }
        foreach (array('avatar_240', 'avatar_72', 'avatar_origin') as $key) {
            if (!empty($userData['avatar'][$key])) {
                return $userData['avatar'][$key];
            }
        }
        return '';
    }

    private function isUserActive($userData)
    {
        if (empty($userData['status']) || !is_array($userData['status'])) {
            return true;
        }
        if (!empty($userData['status']['is_exited'])) {
            return false;
        }
        if (array_key_exists('is_activated', $userData['status'])) {
            return (bool)$userData['status']['is_activated'];
        }
        return true;
    }
}
