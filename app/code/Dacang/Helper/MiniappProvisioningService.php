<?php
namespace ScshuxCms\Dacang\Helper;

use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Salary\Model\CompanyModuleAuthModel;
use ScshuxCms\User\Model\UserModel;

class MiniappProvisioningService
{
	const INITIAL_PASSWORD = 'dc123456';

	public function provision($company, $payload, $operatorId = 0)
	{
		$companyId = intval($company->id);
		$adminName = trim($payload['admin_name']);
		$adminMobile = trim($payload['admin_mobile']);
		$companyCode = trim($company->company_code);
		if ($companyCode === '') {
			$companyCode = CompanyIdentity::generateCompanyCode($company->name, $companyId);
		}

		$user = $this->upsertAdminUser($companyId, $adminName, $adminMobile);
		$employee = $this->upsertAdminEmployee($companyId, $adminName, $adminMobile, $companyCode);

		$company->app_platform = 'miniapp';
		$company->company_code = $companyCode;
		$company->miniapp_admin_name = $adminName;
		$company->miniapp_admin_mobile = $adminMobile;
		$company->miniapp_admin_user_id = intval($user->user_id);
		$company->miniapp_admin_employee_id = intval($employee->id);
		$company->registration_source = isset($payload['registration_source']) ? $payload['registration_source'] : 'admin';
		$company->pointstatus = in_array('points', $payload['modules']) ? 1 : 0;
		if (!$company->save()) {
			throw new \RuntimeException('保存小程序企业资料失败');
		}

		$this->saveModules($companyId, $payload['modules'], $operatorId);
		return array(
			'company_code' => $companyCode,
			'admin_employee_no' => $employee->jobnumber,
			'initial_password' => self::INITIAL_PASSWORD
		);
	}

	protected function upsertAdminUser($companyId, $adminName, $adminMobile)
	{
		$user = UserModel::factory()->findFirst('company_id=' . intval($companyId) . ' and is_admin=1');
		if (!$user) {
			$user = new UserModel();
			$user->company_id = intval($companyId);
			$user->created = time();
			$user->reg_ip = Helper::factory()->getIp();
			$user->is_admin = 1;
			$user->password = md5(self::INITIAL_PASSWORD);
		}
		$user->user_name = $adminMobile;
		$user->phone = $adminMobile;
		$user->true_name = $adminName;
		if (!$user->save()) {
			throw new \RuntimeException('保存小程序总管理员账号失败');
		}
		return $user;
	}

	protected function upsertAdminEmployee($companyId, $adminName, $adminMobile, $companyCode)
	{
		$employeeNo = CompanyIdentity::adminEmployeeNo($companyCode);
		$employee = CompanyUserModel::factory()->findFirst('company_id=' . intval($companyId) . ' and jobnumber="' . addslashes($employeeNo) . '"');
		if (!$employee) {
			$employee = new CompanyUserModel();
			$employee->company_id = intval($companyId);
			$employee->created = time();
			$employee->department_id = 0;
			$employee->right = 3;
			$employee->addreport = 1;
		}
		$employee->name = $adminName;
		$employee->jobnumber = $employeeNo;
		$employee->active = 1;
		$employee->extattr = json_encode(array(
			'mobile' => $adminMobile,
			'employee_no' => $employeeNo,
			'source' => 'miniapp_admin'
		), JSON_UNESCAPED_UNICODE);
		if (!$employee->save()) {
			throw new \RuntimeException('保存总管理员员工档案失败');
		}
		return $employee;
	}

	protected function saveModules($companyId, $modules, $operatorId)
	{
		$enabled = array_fill_keys($modules, 1);
		$salaryEnabled = isset($enabled['salary']);
		$auth = array(
			'salary' => array(
				'_module' => $salaryEnabled ? 1 : 0,
				'payroll' => isset($enabled['salary']) ? 1 : 0,
				'payslip' => isset($enabled['salary']) ? 1 : 0,
				'commission' => isset($enabled['commission']) ? 1 : 0,
				'performance_salary' => 0
			),
			'points' => array('_module' => isset($enabled['points']) ? 1 : 0),
			'kpi' => array('_module' => isset($enabled['kpi']) ? 1 : 0),
			'commission' => array('_module' => isset($enabled['commission']) ? 1 : 0),
			'annual' => array('_module' => isset($enabled['annual']) ? 1 : 0),
			'promotion' => array('_module' => isset($enabled['promotion']) ? 1 : 0),
			'training' => array('_module' => isset($enabled['training']) ? 1 : 0)
		);
		return CompanyModuleAuthModel::saveCompanyAuth($companyId, $auth, $operatorId);
	}
}