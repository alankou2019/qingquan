<?php
/**
 * Salary data view permissions.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class SalaryViewRoleModel extends BaseModel
{
	protected static $_instance = null;

	public function initialize()
	{
		$this->setSource($this->getTableName("salary_view_role"));
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new SalaryViewRoleModel();
		}
		return self::$_instance;
	}

	public static function delFactory()
	{
		self::$_instance = null;
	}

	public function getUserScope($companyId, $userId, $scopeType = '')
	{
		$return = array();
		$where = 'company_id=' . intval($companyId) . ' and user_id=' . intval($userId);
		if ($scopeType !== '') {
			$where .= ' and scope_type="' . addslashes($scopeType) . '"';
		}
		$items = self::factory()->find($where);
		foreach ($items as $item) {
			if ($scopeType !== '') {
				$return[] = intval($item->target_id);
			} else {
				if (!isset($return[$item->scope_type])) {
					$return[$item->scope_type] = array();
				}
				$return[$item->scope_type][] = intval($item->target_id);
			}
		}
		return $return;
	}

	public function getRoleCountMap($companyId)
	{
		$return = array();
		$sql = 'select user_id,count(*) as num from ' . $this->getSource() .
			' where company_id=' . intval($companyId) . ' group by user_id';
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $item) {
			$return[intval($item['user_id'])] = intval($item['num']);
		}
		return $return;
	}

	public function getUserCanExport($companyId, $userId)
	{
		$item = self::factory()->findFirst('company_id=' . intval($companyId) . ' and user_id=' . intval($userId) . ' and can_export=1');
		return $item ? 1 : 0;
	}

	public function saveUserScopes($companyId, $userId, $departmentIds, $employeeIds, $canExport = 0)
	{
		$companyId = intval($companyId);
		$userId = intval($userId);
		if ($companyId <= 0 || $userId <= 0) {
			return false;
		}

		$this->deleteBySql('company_id=' . $companyId . ' and user_id=' . $userId);
		$now = time();
		$this->saveOneScopeList($companyId, $userId, 'department', $departmentIds, $canExport, $now);
		$this->saveOneScopeList($companyId, $userId, 'employee', $employeeIds, $canExport, $now);
		return true;
	}

	protected function saveOneScopeList($companyId, $userId, $scopeType, $targetIds, $canExport, $now)
	{
		if (empty($targetIds) || !is_array($targetIds)) {
			return true;
		}
		$saved = array();
		foreach ($targetIds as $targetId) {
			$targetId = intval($targetId);
			if ($targetId <= 0 || isset($saved[$targetId])) {
				continue;
			}
			$saved[$targetId] = 1;
			$item = new SalaryViewRoleModel();
			$item->saveData(array(
				'company_id' => $companyId,
				'user_id' => $userId,
				'scope_type' => $scopeType,
				'target_id' => $targetId,
				'can_view_detail' => 1,
				'can_export' => intval($canExport) ? 1 : 0,
				'created_at' => $now,
				'updated_at' => $now,
			));
		}
		return true;
	}
}
