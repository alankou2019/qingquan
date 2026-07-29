<?php
/**
 * Unified organization and employee master data for all business modules.
 */
namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Helper\FeishuSyncService;
use ScshuxCms\Dacang\Helper\WecomSyncService;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Dacang\Model\DepartmentModel;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;
use ScshuxCms\Dacang\Model\PlatformUserIdentityModel;
use ScshuxCms\Salary\Model\SalaryEmployeeDepartmentModel;
use ScshuxCms\Salary\Model\SalaryOperationLogModel;
use ScshuxCms\Salary\Model\SalaryPayrollAuditModel;
use ScshuxCms\Salary\Model\SalaryViewRoleModel;

class PersonnelController extends FrontendBaseController
{
	public function indexAction()
	{
		$company = CompanyModel::findFirst($this->companyId);
		$platform = $company && !empty($company->app_platform)
			? trim($company->app_platform)
			: 'dingding';
		$platformOptions = $this->getPlatformOptions();

		$wecom = $this->getPlatformItem('wecom');
		$feishu = $this->getPlatformItem('feishu');
		$syncItems = array(
			'dingding' => array(
				'name' => '钉钉',
				'status' => '成熟可用',
				'desc' => '复用原KPI考核系统已经稳定运行的钉钉部门和员工同步能力。',
				'url' => $this->getHelper()->createUrl(array(
					'p' => 'department/async',
					'from' => 'personnel',
				)),
				'action' => '同步钉钉',
				'sync_url' => '',
			),
			'wecom' => $wecom,
			'feishu' => $feishu,
			'manual' => array(
				'name' => 'Excel导入',
				'status' => '成熟可用',
				'desc' => '使用统一Excel模板导入部门和员工，导入后供KPI、积分和薪酬共同使用。',
				'url' => '',
				'action' => '',
				'sync_url' => '',
				'upload_url' => $this->getHelper()->createUrl(array(
					'p' => 'department/uploadexcel',
					'from' => 'personnel',
				)),
				'template_url' => $this->getHelper()->createUrl(array(
					'p' => 'department/exportexceltpl',
				)),
			),
		);

		$user = Helper::factory()->getSession()->get('_user');
		$this->view->setVar('company', $company);
		$this->view->setVar('platform', $platform);
		$this->view->setVar(
			'platformName',
			isset($platformOptions[$platform]) ? $platformOptions[$platform] : $platform
		);
		$this->view->setVar('syncItems', $syncItems);
		$this->view->setVar('userItems', $this->getCompanyUsers());
		$this->view->setVar('departmentItems', $this->getPersonnelDepartments());
		$this->view->setVar('canManagePersonnel', !empty($user->is_admin) ? 1 : 0);
		$this->getView()->setViewsDir(APPROOT . '/design/frontend/default/Salary');
		$this->view->pick('salary/employeesync');
	}

	public function saveAction()
	{
		$this->requirePersonnelManager();
		if (!$this->request->isPost()) {
			$this->sendErrorResult('不支持的请求方式');
		}

		$employeeId = intval($this->request->getPost('employee_id'));
		$userInfo = CompanyUserModel::findFirst(
			'company_id=' . intval($this->companyId) . ' and id=' . $employeeId
		);
		if (!$userInfo) {
			$this->sendErrorResult('员工不存在或不属于当前企业');
		}

		$name = trim($this->request->getPost('name'));
		$mobile = trim($this->request->getPost('mobile'));
		$positionName = trim($this->request->getPost('position_name'));
		$departmentId = intval($this->request->getPost('department_id'));
		if ($name == '') {
			$this->sendErrorResult('员工姓名不能为空');
		}
		if (function_exists('mb_strlen') && mb_strlen($name, 'UTF-8') > 80) {
			$this->sendErrorResult('员工姓名不能超过80个字符');
		}
		if ($mobile != '' && !preg_match('/^\d{6,20}$/', $mobile)) {
			$this->sendErrorResult('手机号应为6至20位数字');
		}
		if (function_exists('mb_strlen') && mb_strlen($positionName, 'UTF-8') > 100) {
			$this->sendErrorResult('岗位不能超过100个字符');
		}

		$departmentMap = $this->getPersonnelDepartmentMap();
		if ($departmentId > 0 && !isset($departmentMap[$departmentId])) {
			$this->sendErrorResult('所选部门不存在或不属于当前企业');
		}

		$userTable = CompanyUserModel::factory()->getSource();
		$departmentModel = SalaryEmployeeDepartmentModel::factory();
		$mobileColumn = $departmentModel->getEmployeeMobileColumn($userTable);
		$positionColumn = $departmentModel->getEmployeePositionColumn($userTable);
		if ($mobileColumn != '' && $mobile != '') {
			$duplicate = $this->getDI()->get('db')->query(
				'select id from `' . $userTable . '` where company_id=' .
				intval($this->companyId) . ' and id<>' . $employeeId .
				' and `' . $mobileColumn . '`="' . addslashes($mobile) . '" limit 1'
			)->fetch();
			if ($duplicate) {
				$this->sendErrorResult('该手机号已被其他员工使用');
			}
		}

		$saveData = array(
			'name' => $name,
			'department_id' => $departmentId,
		);
		if ($mobileColumn != '') {
			$saveData[$mobileColumn] = $mobile;
		}
		if ($positionColumn != '') {
			$saveData[$positionColumn] = $positionName;
		}
		if (!$userInfo->save($saveData)) {
			$this->sendErrorResult('员工信息保存失败，请稍后重试');
		}

		$departmentName = $departmentId > 0 && isset($departmentMap[$departmentId])
			? $departmentMap[$departmentId]['name']
			: '-';
		$this->addPersonnelLog(
			'personnel_employee_save',
			'company_user',
			$employeeId,
			'编辑统一人员信息：' . $name
		);
		$this->sendSuccessResult(array(
			'message' => '员工信息已保存，KPI、积分和薪酬模块同步生效',
			'employee' => array(
				'id' => $employeeId,
				'name' => $name,
				'mobile' => $mobile,
				'department_id' => $departmentId,
				'department_name' => $departmentName,
				'position_name' => $positionName,
			),
		));
	}

	public function deleteAction()
	{
		$this->requirePersonnelManager();
		if (!$this->request->isPost()) {
			$this->sendErrorResult('不支持的请求方式');
		}

		$employeeId = intval($this->request->getPost('employee_id'));
		$userInfo = CompanyUserModel::findFirst(
			'company_id=' . intval($this->companyId) . ' and id=' . $employeeId
		);
		if (!$userInfo) {
			$this->sendErrorResult('员工不存在或已经删除');
		}
		if (intval($userInfo->is_admin) == 1) {
			$this->sendErrorResult('企业管理员不能删除');
		}
		if ($employeeId == $this->getOperatorId()) {
			$this->sendErrorResult('不能删除当前登录员工');
		}
		if (intval($userInfo->addreport) == 1) {
			$this->sendErrorResult('该员工正在使用绩效考核，请先移出考核范围');
		}

		$employeeName = $userInfo->name;
		$db = $this->getDI()->get('db');
		$db->begin();
		try {
			$viewRoleTable = SalaryViewRoleModel::factory()->getSource();
			if ($this->tableExists($viewRoleTable)) {
				SalaryViewRoleModel::factory()->deleteBySql(
					'company_id=' . intval($this->companyId) .
					' and (user_id=' . $employeeId .
					' or (scope_type="employee" and target_id=' . $employeeId . '))'
				);
			}
			$auditRoleTable = SalaryPayrollAuditModel::factory()->getRoleTable();
			if ($this->tableExists($auditRoleTable)) {
				$db->execute(
					'delete from `' . $auditRoleTable . '` where company_id=' .
					intval($this->companyId) . ' and reviewer_id=' . $employeeId
				);
			}
			$identityTable = PlatformUserIdentityModel::factory()->getSource();
			if ($this->tableExists($identityTable)) {
				$db->execute(
					'delete from `' . $identityTable . '` where company_id=' .
					intval($this->companyId) . ' and company_user_id=' . $employeeId
				);
			}
			if (!$db->execute(
				'delete from `' . CompanyUserModel::factory()->getSource() .
				'` where company_id=' . intval($this->companyId) .
				' and id=' . $employeeId
			)) {
				throw new \Exception('员工删除失败');
			}
			$db->commit();
		} catch (\Exception $exception) {
			$db->rollback();
			$this->sendErrorResult('员工删除失败，请稍后重试');
		}

		$this->addPersonnelLog(
			'personnel_employee_delete',
			'company_user',
			$employeeId,
			'删除统一人员：' . $employeeName . '；历史考核及薪酬记录保留'
		);
		$this->sendSuccessResult(array(
			'message' => '员工已从统一人员库删除，历史业务记录仍然保留',
			'employee_id' => $employeeId,
		));
	}

	public function syncAction()
	{
		$this->requirePersonnelManager();
		if (!$this->request->isPost() || !$this->request->isAjax()) {
			$this->sendErrorResult('请求方式错误');
		}

		$platform = trim($this->request->getPost('platform'));
		if (!in_array($platform, array('wecom', 'feishu'))) {
			$this->sendErrorResult('不支持的通讯平台');
		}
		$integration = PlatformIntegrationModel::getByCompany(
			$this->companyId,
			$platform,
			true
		);
		if (!$integration) {
			$this->sendErrorResult('平台尚未配置或未启用，请联系运营后台完成配置');
		}

		try {
			$result = $platform == 'wecom'
				? (new WecomSyncService($integration))->syncAll()
				: (new FeishuSyncService($integration))->syncAll();
		} catch (\Exception $exception) {
			$this->sendErrorResult($exception->getMessage());
		}

		$platformName = $platform == 'wecom' ? '企业微信' : '飞书';
		$this->addPersonnelLog(
			'personnel_platform_sync',
			'platform_integration',
			intval($integration->id),
			'同步' . $platformName . '组织和员工'
		);
		$result['message'] = $platformName . '通讯录同步完成';
		$this->sendSuccessResult($result);
	}

	protected function getPlatformItem($platform)
	{
		$platformName = $platform == 'wecom' ? '企业微信' : '飞书';
		$integration = PlatformIntegrationModel::getByCompany(
			$this->companyId,
			$platform,
			false
		);
		$enabled = $integration && intval($integration->enabled) == 1;
		return array(
			'name' => $platformName,
			'status' => $enabled ? '已配置并启用' : ($integration ? '已配置未启用' : '未配置'),
			'desc' => $enabled
				? '凭据由运营后台维护，企业管理员可在这里同步组织和员工。'
				: '请先由运营后台完成凭据配置和连接测试，再开放通讯录同步。',
			'url' => '',
			'action' => $enabled ? '同步' . $platformName : '',
			'sync_url' => $enabled
				? $this->getHelper()->createUrl(array('p' => 'personnel/sync'))
				: '',
		);
	}

	protected function getPlatformOptions()
	{
		return array(
			'dingding' => '钉钉',
			'wecom' => '企业微信',
			'feishu' => '飞书',
			'manual' => 'Excel导入',
		);
	}

	protected function getCompanyUsers()
	{
		$userTable = CompanyUserModel::factory()->getSource();
		$departmentModel = SalaryEmployeeDepartmentModel::factory();
		$departmentSql = $departmentModel->getDepartmentSql($this->companyId, 'u');
		$mobileColumn = $departmentModel->getEmployeeMobileColumn($userTable);
		$positionColumn = $departmentModel->getEmployeePositionColumn($userTable);
		$mobileSelect = $mobileColumn ? 'u.`' . $mobileColumn . '`' : '""';
		$positionSelect = $positionColumn ? 'u.`' . $positionColumn . '`' : '""';
		$sql = 'select u.id,u.name,u.department_id,u.is_admin,u.is_leader,' .
			$mobileSelect . ' as mobile,' . $positionSelect . ' as position_name,' .
			$departmentSql['select'] . ' as departmentname ' .
			'from `' . $userTable . '` u ' .
			$departmentSql['join'] .
			'where u.company_id=' . intval($this->companyId) .
			' order by departmentname asc,u.id asc';
		return $this->getDI()->get('db')->query($sql)->fetchAll();
	}

	protected function getPersonnelDepartments()
	{
		return array_values($this->getPersonnelDepartmentMap());
	}

	protected function getPersonnelDepartmentMap()
	{
		$return = array();
		$platform = SalaryEmployeeDepartmentModel::factory()->getCompanyPlatform(
			$this->companyId
		);
		$items = DepartmentModel::find(array(
			'conditions' => 'company_id=' . intval($this->companyId),
			'order' => 'id asc',
		));
		foreach ($items as $item) {
			$value = $platform == 'dingding'
				? intval($item->dingding_id)
				: intval($item->id);
			if ($value <= 0) {
				$value = intval($item->id);
			}
			$return[$value] = array(
				'value' => $value,
				'name' => $item->name,
			);
		}
		return $return;
	}

	protected function requirePersonnelManager()
	{
		$user = Helper::factory()->getSession()->get('_user');
		if (empty($user->is_admin)) {
			$this->sendErrorResult('只有企业管理员可以维护统一人员信息');
		}
		return true;
	}

	protected function getOperatorId()
	{
		$user = Helper::factory()->getSession()->get('_user');
		return empty($user->user_id) ? 0 : intval($user->user_id);
	}

	protected function addPersonnelLog(
		$actionCode,
		$objectType,
		$objectId,
		$summary
	) {
		return SalaryOperationLogModel::factory()->addLog(
			$this->companyId,
			$this->getOperatorId(),
			$actionCode,
			$objectType,
			$objectId,
			'',
			$summary
		);
	}

	protected function tableExists($tableName)
	{
		$item = $this->getDI()->get('db')->query(
			'select count(*) as num from information_schema.tables' .
			' where table_schema=database() and table_name="' .
			addslashes($tableName) . '"'
		)->fetch();
		return $item && intval($item['num']) > 0;
	}
}
