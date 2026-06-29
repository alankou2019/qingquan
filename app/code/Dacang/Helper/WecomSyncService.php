<?php
namespace ScshuxCms\Dacang\Helper;

use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\CompanyDepartModel;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Dacang\Model\PlatformDepartmentIdentityModel;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;
use ScshuxCms\Dacang\Model\PlatformUserIdentityModel;

class WecomSyncService
{
    private $integration;
    private $client;

    public function __construct(PlatformIntegrationModel $integration)
    {
        $this->integration = $integration;
        $this->client = new WecomClient($integration);
    }

    public function syncAll()
    {
        $companyId = intval($this->integration->company_id);
        $departments = $this->client->getDepartments();
        $departmentMap = array();

        foreach ($departments as $department) {
            $externalId = (string)$department['id'];
            $identity = PlatformDepartmentIdentityModel::findFirst(
                "company_id={$companyId} and platform='wecom' and external_department_id='" . addslashes($externalId) . "'"
            );
            $model = $identity ? CompanyDepartModel::findFirst(intval($identity->department_id)) : null;
            if (!$model) {
                $model = new CompanyDepartModel();
                $model->company_id = $companyId;
            }
            $model->name = $department['name'];
            $model->dingding_id = intval($department['id']);
            $model->dingding_parent_id = isset($department['parentid']) ? intval($department['parentid']) : 0;
            if (!$model->save()) {
                throw new \RuntimeException('保存部门失败: ' . $department['name']);
            }
            if (!$identity) {
                $identity = new PlatformDepartmentIdentityModel();
                $identity->company_id = $companyId;
                $identity->department_id = $model->id;
                $identity->platform = 'wecom';
                $identity->external_department_id = $externalId;
                $identity->created_at = time();
            }
            $identity->updated_at = time();
            if (!$identity->save()) {
                throw new \RuntimeException('保存部门身份映射失败: ' . $department['name']);
            }
            $departmentMap[$externalId] = intval($department['id']);
        }

        $seen = array();
        $created = 0;
        $updated = 0;
        foreach ($departments as $department) {
            $users = $this->client->getDepartmentUsers($department['id'], false);
            foreach ($users as $userData) {
                $externalUserId = (string)$userData['userid'];
                if (isset($seen[$externalUserId])) {
                    continue;
                }
                $seen[$externalUserId] = true;
                $identity = PlatformUserIdentityModel::getByExternalId($companyId, 'wecom', $externalUserId);
                $user = $identity ? CompanyUserModel::findFirst(intval($identity->company_user_id)) : null;
                $isNew = !$user;
                if (!$user) {
                    $user = new CompanyUserModel();
                    $user->company_id = $companyId;
                    $user->created = time();
                    $user->right = 1;
                    $user->addreport = 0;
                }
                $departmentIds = isset($userData['department']) ? $userData['department'] : array($department['id']);
                $user->department_id = intval(reset($departmentIds));
                $user->name = $userData['name'];
                $user->active = 1;
                $user->avatar = isset($userData['avatar']) ? $userData['avatar'] : '';
                $user->email = isset($userData['email']) ? $userData['email'] : '';
                $user->jobnumber = !empty($userData['mobile']) ? $userData['mobile'] : '';
                $leaderFlags = isset($userData['is_leader_in_dept']) ? $userData['is_leader_in_dept'] : array();
                $user->is_leader = $leaderFlags && intval(reset($leaderFlags)) === 1 ? 1 : 0;
                $user->extattr = json_encode($userData, JSON_UNESCAPED_UNICODE);
                if (!$user->save()) {
                    throw new \RuntimeException('保存员工失败: ' . $userData['name']);
                }
                if (!$identity) {
                    $identity = new PlatformUserIdentityModel();
                    $identity->company_id = $companyId;
                    $identity->company_user_id = $user->id;
                    $identity->platform = 'wecom';
                    $identity->external_user_id = $externalUserId;
                    $identity->created_at = time();
                }
                $identity->status = 1;
                $identity->updated_at = time();
                if (!$identity->save()) {
                    throw new \RuntimeException('保存员工身份映射失败: ' . $userData['name']);
                }
                $isNew ? $created++ : $updated++;
            }
        }

        $disabled = 0;
        $identities = PlatformUserIdentityModel::find("company_id={$companyId} and platform='wecom' and status=1");
        foreach ($identities as $identity) {
            if (!isset($seen[$identity->external_user_id])) {
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
            'departments' => count($departments),
            'created_users' => $created,
            'updated_users' => $updated,
            'disabled_users' => $disabled,
        );
    }
}
