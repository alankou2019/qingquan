<?php
/**
 * Salary management entry for company backend.
 */
namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Dacang\Model\DepartmentModel;
use ScshuxCms\Salary\Model\CompanyModuleAuthModel;
use ScshuxCms\Salary\Model\SalaryViewRoleModel;

class SalaryController extends FrontendBaseController
{
	public function indexAction()
	{
		$this->checkModule();
		$this->view->setVar('features', $this->getSalaryFeatures());
	}

	public function payrollAction()
	{
		$this->showFeature('payroll', '工资核算');
	}

	public function payslipAction()
	{
		$this->showFeature('payslip', '工资条发放');
	}

	public function commissionAction()
	{
		$this->showFeature('commission', '提成核算');
	}

	public function performanceAction()
	{
		$this->showFeature('performance_salary', '绩效工资核算');
	}

	public function employeesyncAction()
	{
		$this->checkModule();
		$company = CompanyModel::findFirst($this->companyId);
		$platform = empty($company->app_platform) ? 'dingding' : $company->app_platform;
		$platformOptions = $this->getPlatformOptions();
		$syncItems = array(
			'dingding' => array(
				'name' => '钉钉',
				'status' => '已接通旧系统同步',
				'desc' => '继续使用原有考核系统的钉钉组织和员工同步能力。',
				'url' => $this->getHelper()->createUrl(array('p' => 'department/async')),
				'action' => '进入同步',
			),
			'wecom' => array(
				'name' => '企业微信',
				'status' => '预留',
				'desc' => '企业微信接口尚未接入，当前可先用Excel或后台维护员工信息。',
				'url' => $this->getHelper()->createUrl(array('p' => 'department/index')),
				'action' => '去部门管理',
			),
			'feishu' => array(
				'name' => '飞书',
				'status' => '预留',
				'desc' => '飞书接口尚未接入，当前先保留企业平台选项和后续接入口。',
				'url' => $this->getHelper()->createUrl(array('p' => 'department/index')),
				'action' => '去部门管理',
			),
			'manual' => array(
				'name' => '手工/Excel',
				'status' => '可用',
				'desc' => '适合暂未接入第三方通讯平台的企业，员工资料通过后台导入或维护。',
				'url' => $this->getHelper()->createUrl(array('p' => 'department/index')),
				'action' => '去部门管理',
			),
		);

		$this->view->setVar('company', $company);
		$this->view->setVar('platform', $platform);
		$this->view->setVar('platformName', isset($platformOptions[$platform]) ? $platformOptions[$platform] : $platform);
		$this->view->setVar('syncItems', $syncItems);
	}

	public function authAction()
	{
		$this->checkModule();
		$userItems = $this->getCompanyUsers();
		$roleCountMap = SalaryViewRoleModel::factory()->getRoleCountMap($this->companyId);
		foreach ($userItems as $key => $item) {
			$userItems[$key]['role_count'] = isset($roleCountMap[intval($item['id'])]) ? $roleCountMap[intval($item['id'])] : 0;
		}
		$this->view->setVar('userItems', $userItems);
	}

	public function autheditAction()
	{
		$this->checkModule();
		$userId = intval($this->request->get('user_id'));
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/auth'));
		$userInfo = CompanyUserModel::findFirst($userId);
		if (!$userInfo || intval($userInfo->company_id) != intval($this->companyId)) {
			Utils::showMsg('员工不存在', $backUrl);
		}

		$departmentRoles = SalaryViewRoleModel::factory()->getUserScope($this->companyId, $userId, 'department');
		$employeeRoles = SalaryViewRoleModel::factory()->getUserScope($this->companyId, $userId, 'employee');
		$departList = DepartmentModel::TreeDepartList($this->companyId);
		if ($departList) {
			foreach ($departList as $depart) {
				$depart->isChecked = in_array(intval($depart->id), $departmentRoles) ? 1 : 0;
			}
			$departList = Utils::formatTree($departList);
		}
		$userItems = $this->getCompanyUsers();
		foreach ($userItems as $key => $item) {
			$userItems[$key]['isChecked'] = in_array(intval($item['id']), $employeeRoles) ? 1 : 0;
		}
		$canExport = SalaryViewRoleModel::factory()->getUserCanExport($this->companyId, $userId);

		$this->view->setVar('userInfo', $userInfo);
		$this->view->setVar('departList', $departList);
		$this->view->setVar('userItems', $userItems);
		$this->view->setVar('canExport', $canExport);
	}

	public function authsaveAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/auth'));
		$userId = intval($this->request->get('user_id'));
		$userInfo = CompanyUserModel::findFirst($userId);
		if (!$userInfo || intval($userInfo->company_id) != intval($this->companyId)) {
			Utils::showMsg('员工不存在', $backUrl);
		}

		$departmentRoles = $this->request->get('role_department');
		$employeeRoles = $this->request->get('role_employee');
		if (empty($departmentRoles) || !is_array($departmentRoles)) {
			$departmentRoles = array();
		}
		if (empty($employeeRoles) || !is_array($employeeRoles)) {
			$employeeRoles = array();
		}
		$canExport = intval($this->request->get('can_export'));
		SalaryViewRoleModel::factory()->saveUserScopes($this->companyId, $userId, $departmentRoles, $employeeRoles, $canExport);
		Utils::showMsg('操作成功', $backUrl);
	}

	protected function showFeature($featureCode, $featureName)
	{
		$this->checkFeature($featureCode);
		$this->view->setVar('featureCode', $featureCode);
		$this->view->setVar('featureName', $featureName);
		$this->view->pick('salary/feature');
	}

	protected function checkModule()
	{
		$authMap = CompanyModuleAuthModel::getCompanyAuthMap($this->companyId);
		if (!CompanyModuleAuthModel::isEnabled($authMap, 'salary')) {
			Utils::showMsg('薪酬管理模块未开通，请联系营运后台开通。', $this->getHelper()->createUrl(array('p' => 'index/index')));
		}
		return $authMap;
	}

	protected function checkFeature($featureCode)
	{
		$authMap = $this->checkModule();
		if (!CompanyModuleAuthModel::isEnabled($authMap, 'salary', $featureCode)) {
			Utils::showMsg('当前薪酬子功能未开通，请联系营运后台开通。', $this->getHelper()->createUrl(array('p' => 'salary/index')));
		}
		return true;
	}

	protected function getSalaryFeatures()
	{
		$authMap = $this->checkModule();
		$items = array(
			array('code' => 'payroll', 'name' => '工资核算', 'url' => 'salary/payroll', 'desc' => '预留员工工资核算、导入和复核入口。'),
			array('code' => 'payslip', 'name' => '工资条发放', 'url' => 'salary/payslip', 'desc' => '预留工资条生成、发放和员工确认入口。'),
			array('code' => 'commission', 'name' => '提成核算', 'url' => 'salary/commission', 'desc' => '预留销售提成规则、核算和明细查看入口。'),
			array('code' => 'performance_salary', 'name' => '绩效工资核算', 'url' => 'salary/performance', 'desc' => '预留绩效结果联动工资核算入口。'),
		);
		foreach ($items as $key => $item) {
			$items[$key]['enabled'] = CompanyModuleAuthModel::isEnabled($authMap, 'salary', $item['code']) ? 1 : 0;
		}
		return $items;
	}

	protected function getPlatformOptions()
	{
		return array(
			'dingding' => '钉钉',
			'wecom' => '企业微信',
			'feishu' => '飞书',
			'manual' => '手工/Excel',
		);
	}

	protected function getCompanyUsers()
	{
		$return = array();
		$items = $this->modelsManager->createBuilder()
			->columns('u.id,u.name,u.department_id,u.is_admin,u.is_leader,d.name as departmentname')
			->addFrom('ScshuxCms\Dacang\Model\CompanyUserModel', 'u')
			->leftJoin('ScshuxCms\Dacang\Model\DepartmentModel', 'u.department_id=d.dingding_id and u.company_id=d.company_id', 'd')
			->where('u.company_id=' . intval($this->companyId))
			->orderBy('u.id asc')
			->getQuery()
			->execute()
			->toArray();
		foreach ($items as $item) {
			$return[] = $item;
		}
		return $return;
	}
}
