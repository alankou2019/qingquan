<?php
/**
 * Employee department resolver for salary pages.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class SalaryEmployeeDepartmentModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("company_user");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new SalaryEmployeeDepartmentModel();
		}
		return self::$_instance;
	}

	public function getCompanyEmployees($companyId, $mobileAlias = 'mobile')
	{
		$userTable = $this->getTableName('company_user');
		$mobileColumn = $this->getEmployeeMobileColumn($userTable);
		$mobileSelect = $mobileColumn ? 'u.`' . $mobileColumn . '` as ' . $mobileAlias : '"" as ' . $mobileAlias;
		$positionColumn = $this->getEmployeePositionColumn($userTable);
		$positionSelect = $positionColumn ? ',u.`' . $positionColumn . '` as position_name' : ',"" as position_name';
		$departmentSql = $this->getDepartmentSql($companyId, 'u');
		$sql = 'select u.id,u.name,' . $mobileSelect . ',u.department_id,' . $departmentSql['select'] . ' as department_name' . $positionSelect . ' ' .
			'from `' . $userTable . '` u ' .
			$departmentSql['join'] .
			'where u.company_id=' . intval($companyId) . ' order by u.id asc';
		return $this->getDB()->query($sql)->fetchAll();
	}

	public function getDepartmentSql($companyId, $userAlias = 'u')
	{
		$platform = $this->getCompanyPlatform($companyId);
		$departTable = $this->getTableName('company_department');
		if ($platform == 'dingding') {
			return array(
				'select' => 'd.name',
				'join' => 'left join `' . $departTable . '` d on ' . $userAlias . '.department_id=d.dingding_id and ' . $userAlias . '.company_id=d.company_id ',
			);
		}
		if (($platform == 'wecom' || $platform == 'feishu') && $this->hasTable($this->getTableName('platform_department_identity')) && $this->hasTable($this->getTableName('platform_user_identity'))) {
			return array(
				'select' => 'coalesce(dp.name,dm.name,"")',
				'join' => 'left join `' . $this->getTableName('platform_user_identity') . '` pui on pui.company_user_id=' . $userAlias . '.id and pui.company_id=' . $userAlias . '.company_id and pui.platform="' . addslashes($platform) . '" and pui.status=1 ' .
					'left join `' . $this->getTableName('platform_department_identity') . '` pdi on pdi.company_id=' . $userAlias . '.company_id and pdi.platform=pui.platform and pdi.external_department_id=' . $userAlias . '.department_id ' .
					'left join `' . $departTable . '` dp on pdi.department_id=dp.id and dp.company_id=' . $userAlias . '.company_id ' .
					'left join `' . $departTable . '` dm on ' . $userAlias . '.department_id=dm.id and dm.company_id=' . $userAlias . '.company_id ',
			);
		}
		return array(
			'select' => 'd.name',
			'join' => 'left join `' . $departTable . '` d on ' . $userAlias . '.department_id=d.id and ' . $userAlias . '.company_id=d.company_id ',
		);
	}

	public function getCompanyPlatform($companyId)
	{
		$companyTable = $this->getTableName('company');
		if (!$this->hasColumn($companyTable, 'app_platform')) {
			return 'dingding';
		}
		$row = $this->getDB()->query('select app_platform from `' . $companyTable . '` where id=' . intval($companyId))->fetch();
		$platform = $row && !empty($row['app_platform']) ? trim($row['app_platform']) : 'dingding';
		return in_array($platform, array('dingding', 'wecom', 'feishu', 'manual')) ? $platform : 'manual';
	}

	public function getEmployeeMobileColumn($userTable)
	{
		foreach (array('jobnumber', 'mobile', 'phone') as $column) {
			if ($this->hasColumn($userTable, $column)) {
				return $column;
			}
		}
		return '';
	}

	public function getEmployeePositionColumn($userTable)
	{
		foreach (array('position_name', 'position', 'job_title', 'title', 'post') as $column) {
			if ($this->hasColumn($userTable, $column)) {
				return $column;
			}
		}
		return '';
	}

	protected function hasColumn($table, $column)
	{
		$item = $this->getDB()->query("SHOW COLUMNS FROM `" . $table . "` LIKE '" . addslashes($column) . "'")->fetch();
		return $item ? true : false;
	}

	protected function hasTable($table)
	{
		$item = $this->getDB()->query("SHOW TABLES LIKE '" . addslashes($table) . "'")->fetch();
		return $item ? true : false;
	}
}
