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
use ScshuxCms\Salary\Model\EmployeeSalaryStructureModel;
use ScshuxCms\Salary\Model\PayrollArchiveModel;
use ScshuxCms\Salary\Model\PayrollEmployeeRowModel;
use ScshuxCms\Salary\Model\PayrollPeriodModel;
use ScshuxCms\Salary\Model\PayrollSlipModel;
use ScshuxCms\Salary\Model\SalaryPayrollImportModel;
use ScshuxCms\Salary\Model\SalaryPayrollAuditModel;
use ScshuxCms\Salary\Model\SalaryProjectModel;
use ScshuxCms\Salary\Model\SalaryProjectTemplateModel;
use ScshuxCms\Salary\Model\SalaryViewRoleModel;

class SalaryController extends FrontendBaseController
{
	public function indexAction()
	{
		$this->checkModule();
		$this->view->setVar('features', $this->getSalaryFeatures());
	}

	public function projectAction()
	{
		$this->checkModule();
		$templates = SalaryProjectTemplateModel::factory()->getActiveTemplates();
		$templateProjectMap = SalaryProjectModel::factory()->getCompanyTemplateProjectMap($this->companyId);
		$directions = SalaryProjectModel::getDirectionLabels();
		$sourceTypes = SalaryProjectModel::getSourceTypeLabels();
		foreach ($templates as $key => $template) {
			$templates[$key]['is_selected'] = isset($templateProjectMap[intval($template['id'])]) ? 1 : 0;
			$templates[$key]['direction_label'] = SalaryProjectModel::label($directions, $template['direction']);
			$templates[$key]['source_type_label'] = SalaryProjectModel::label($sourceTypes, $template['source_type']);
		}

		$editId = intval($this->request->get('id'));
		$editItem = false;
		if ($editId > 0) {
			$editItem = SalaryProjectModel::factory()->findFirst('id=' . $editId . ' and company_id=' . intval($this->companyId) . ' and deleted_at=0');
		}

		$this->view->setVar('templates', $templates);
		$this->view->setVar('projects', SalaryProjectModel::factory()->getCompanyProjects($this->companyId));
		$this->view->setVar('editItem', $editItem);
		$this->view->setVar('sourceTypes', $sourceTypes);
		$this->view->setVar('directions', $directions);
		$this->view->setVar('calculationModes', SalaryProjectModel::getCalculationModeLabels());
		$this->view->setVar('statusLabels', SalaryProjectModel::getStatusLabels());
		$initialTable = EmployeeSalaryStructureModel::factory()->getInitialSalaryTable($this->companyId);
		$this->view->setVar('initialProjects', $initialTable['projects']);
		$this->view->setVar('initialEmployees', $initialTable['employees']);
	}

	public function projectsavetemplatesAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$templateIds = isset($_POST['template_ids']) ? $_POST['template_ids'] : $this->request->get('template_ids');
		SalaryProjectModel::factory()->saveTemplateSelection($this->companyId, $templateIds);
		Utils::showMsg('通用工资项目已保存', $backUrl);
	}

	public function projectsaveAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$result = SalaryProjectModel::factory()->saveCustomProject($this->companyId, $_POST);
		if (!$result) {
			Utils::showMsg(SalaryProjectModel::factory()->getLastError(), $backUrl);
		}
		Utils::showMsg('自定义工资项目已保存', $backUrl);
	}

	public function projectdeleteAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$projectId = intval($this->request->get('id'));
		$result = SalaryProjectModel::factory()->deleteCompanyProject($this->companyId, $projectId);
		if (!$result) {
			Utils::showMsg(SalaryProjectModel::factory()->getLastError(), $backUrl);
		}
		Utils::showMsg('工资项目已停用', $backUrl);
	}

	public function saveinitialsalaryAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$result = EmployeeSalaryStructureModel::factory()->saveInitialSalaryTable($this->companyId, $_POST, $this->getOperatorId());
		if (!$result) {
			Utils::showMsg(EmployeeSalaryStructureModel::factory()->getLastError(), $backUrl);
		}
		Utils::showMsg('初始工资表已保存', $backUrl);
	}

	public function initialtemplateAction()
	{
		$this->checkModule();
		$projects = SalaryProjectModel::factory()->getCompanyProjects($this->companyId);
		$headers = SalaryPayrollImportModel::getDefaultTemplateHeaders($projects);
		$this->outputSalaryTemplate($headers);
	}

	public function uploadinitialsalaryAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		if (empty($_FILES['initial_file']['name'])) {
			Utils::showMsg('请先选择初始工资表Excel文件', $backUrl);
		}
		$extname = strtolower(pathinfo($_FILES['initial_file']['name'], PATHINFO_EXTENSION));
		if (!in_array($extname, array('xls', 'xlsx'))) {
			Utils::showMsg('请上传xls或xlsx格式的Excel文件', $backUrl);
		}
		$file = $this->savePayrollUpload('initial_file', $extname);
		if (!$file) {
			Utils::showMsg('文件上传失败，请重新上传', $backUrl);
		}
		$projects = SalaryProjectModel::factory()->getCompanyProjects($this->companyId);
		$autoCreate = empty($projects) ? true : false;
		$result = SalaryPayrollImportModel::factory()->importInitialSalaryFromExcel($this->companyId, WEBROOT . $file, $this->getOperatorId(), $autoCreate);
		if (!$result) {
			$this->view->setVar('errors', SalaryPayrollImportModel::factory()->getLastErrors());
			$this->view->pick('salary/importresult');
			return;
		}
		Utils::showMsg('初始工资表导入成功，共导入' . intval($result['employee_count']) . '人', $backUrl);
	}

	public function payrollAction()
	{
		$this->checkFeature('payroll');
		$payrollMonth = trim($this->request->get('payroll_month'));
		if (!preg_match('/^\d{4}\-\d{2}$/', $payrollMonth)) {
			$payrollMonth = date('Y-m');
		}
		$period = PayrollPeriodModel::factory()->getCompanyPeriodByMonth($this->companyId, $payrollMonth);
		$projects = SalaryProjectModel::factory()->getCompanyProjects($this->companyId);
		$rows = array();
		if ($period) {
			$rows = PayrollEmployeeRowModel::factory()->getPayrollMatrix($this->companyId, intval($period['id']));
			$period['status_name'] = PayrollPeriodModel::getStatusName($period['status']);
			$period['can_edit'] = in_array($period['status'], array('draft', 'calculated', 'rejected')) ? 1 : 0;
			$period['can_submit_audit'] = PayrollPeriodModel::canSubmitAudit($period['status']) ? 1 : 0;
			$period['can_publish'] = PayrollPeriodModel::canPublishPayslip($period['status']) ? 1 : 0;
			$period['can_archive'] = PayrollPeriodModel::canArchive($period['status']) ? 1 : 0;
		}
		$this->view->setVar('payrollMonth', $payrollMonth);
		$this->view->setVar('period', $period);
		$this->view->setVar('projects', $projects);
		$this->view->setVar('payrollRows', $rows);
		$this->view->setVar('canSendPayslip', $this->isSalaryFeatureEnabled('payslip'));
		$this->view->setVar('defaultPayrollMonth', date('Y-m'));
	}

	public function payslipAction()
	{
		$this->payrollAction();
		$this->view->pick('salary/payroll');
	}

	public function generatepayrollAction()
	{
		$this->checkFeature('payroll');
		$payrollMonth = trim($this->request->get('payroll_month'));
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll', 'payroll_month' => $payrollMonth));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$result = PayrollPeriodModel::factory()->generateFromInitial($this->companyId, $payrollMonth, $this->getOperatorId());
		if (!$result) {
			Utils::showMsg(PayrollPeriodModel::factory()->getLastError(), $backUrl);
		}
		Utils::showMsg('已从初始工资表生成本月工资表', $backUrl);
	}

	public function savepayrollAction()
	{
		$this->checkFeature('payroll');
		$periodId = intval($this->request->get('id'));
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll', 'payroll_month' => $period ? $period['payroll_month'] : date('Y-m')));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$result = PayrollPeriodModel::factory()->savePayrollMatrixFromPost($this->companyId, $periodId, $_POST, $this->getOperatorId());
		if (!$result) {
			Utils::showMsg(PayrollPeriodModel::factory()->getLastError(), $backUrl);
		}
		Utils::showMsg('工资表已保存', $backUrl);
	}

	public function payrolltemplateAction()
	{
		$this->checkFeature('payroll');
		$projects = SalaryProjectModel::factory()->getCompanyProjects($this->companyId);
		$headers = SalaryPayrollImportModel::getDefaultTemplateHeaders($projects);
		$oldReporting = error_reporting();
		error_reporting($oldReporting & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
		$objPHPExcel = \Phalcon\Di\FactoryDefault::getDefault()->get('phpexcel');
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('工资表模板');
		foreach ($headers as $index => $header) {
			$sheet->setCellValueByColumnAndRow($index, 1, $header);
			if ($index == 0) {
				$sheet->setCellValueByColumnAndRow($index, 2, '张三');
				$sheet->getColumnDimensionByColumn($index)->setWidth(14);
			} elseif ($index == 1) {
				$sheet->setCellValueByColumnAndRow($index, 2, '13800000000');
				$sheet->getColumnDimensionByColumn($index)->setWidth(16);
			} else {
				$sheet->setCellValueByColumnAndRow($index, 2, 0);
				$sheet->getColumnDimensionByColumn($index)->setWidth(14);
			}
		}
		$lastColumn = \PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EAF1FF');
		$sheet->getStyle('A1:' . $lastColumn . '2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
		$sheet->freezePane('A2');

		ob_clean();
		header("Content-Description: File Transfer");
		header("Content-type:application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition:attachment;filename=salary_payroll_template.xls");
		header("Content-Transfer-Encoding: binary");
		header("Pragma: public");
		header("Cache-Control:max-age=0");
		$writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$writer->save('php://output');
		error_reporting($oldReporting);
		exit();
	}

	public function uploadpayrollAction()
	{
		$this->checkFeature('payroll');
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}

		$payrollMonth = trim($this->request->get('payroll_month'));
		if (!preg_match('/^\d{4}\-\d{2}$/', $payrollMonth)) {
			Utils::showMsg('请填写正确的工资月份，例如2026-06', $backUrl);
		}
		if (empty($_FILES['payroll_file']['name'])) {
			Utils::showMsg('请先选择工资表Excel文件', $backUrl);
		}
		$extname = strtolower(pathinfo($_FILES['payroll_file']['name'], PATHINFO_EXTENSION));
		if (!in_array($extname, array('xls', 'xlsx'))) {
			Utils::showMsg('请上传xls或xlsx格式的Excel文件', $backUrl);
		}

		$file = $this->savePayrollUpload('payroll_file', $extname);
		if (!$file) {
			Utils::showMsg('文件上传失败，请重新上传', $backUrl);
		}
		$fullPath = WEBROOT . $file;
		$sourceName = $_FILES['payroll_file']['name'];
		$projects = SalaryProjectModel::factory()->getCompanyProjects($this->companyId);

		if (empty($projects)) {
			$previewOk = SalaryPayrollImportModel::factory()->previewFirstImport($this->companyId, $fullPath);
			$this->view->setVar('payrollMonth', $payrollMonth);
			$this->view->setVar('uploadedFile', $file);
			$this->view->setVar('sourceName', $sourceName);
			$this->view->setVar('previewProjects', SalaryPayrollImportModel::factory()->getPreviewProjects());
			$this->view->setVar('errors', SalaryPayrollImportModel::factory()->getLastErrors());
			$this->view->setVar('previewOk', $previewOk ? 1 : 0);
			$this->view->pick('salary/importconfirm');
			return;
		}

		$result = SalaryPayrollImportModel::factory()->importFromExcel($this->companyId, $payrollMonth, $fullPath, $sourceName, $this->getOperatorId(), false);
		$this->view->setVar('result', $result);
		$this->view->setVar('errors', SalaryPayrollImportModel::factory()->getLastErrors());
		$this->view->pick('salary/importresult');
	}

	public function confirmpayrollimportAction()
	{
		$this->checkFeature('payroll');
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$payrollMonth = trim($this->request->get('payroll_month'));
		$file = trim($this->request->get('uploaded_file'));
		$sourceName = trim($this->request->get('source_name'));
		$fullPath = $this->resolveUploadedPayrollFile($file);
		if (!$fullPath) {
			Utils::showMsg('上传文件已失效，请重新上传', $backUrl);
		}
		$result = SalaryPayrollImportModel::factory()->importFromExcel($this->companyId, $payrollMonth, $fullPath, $sourceName, $this->getOperatorId(), true);
		$this->view->setVar('result', $result);
		$this->view->setVar('errors', SalaryPayrollImportModel::factory()->getLastErrors());
		$this->view->pick('salary/importresult');
	}

	public function submitreviewAction()
	{
		$this->checkFeature('payroll');
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$periodId = intval($this->request->get('id'));
		$result = SalaryPayrollAuditModel::factory()->submitPeriod($this->companyId, $periodId, $this->getOperatorId());
		if (!$result) {
			Utils::showMsg(SalaryPayrollAuditModel::factory()->getLastError(), $backUrl);
		}
		Utils::showMsg('已提交工资表审核', $backUrl);
	}

	public function reviewperiodAction()
	{
		$this->checkFeature('payroll');
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$periodId = intval($this->request->get('id'));
		$reviewerId = intval($this->request->get('reviewer_id'));
		$status = $this->request->get('status');
		$opinion = trim($this->request->get('opinion'));
		$result = SalaryPayrollAuditModel::factory()->reviewPeriod($this->companyId, $periodId, $reviewerId, $status, $opinion);
		if (!$result) {
			Utils::showMsg(SalaryPayrollAuditModel::factory()->getLastError(), $backUrl);
		}
		Utils::showMsg('审核处理成功', $backUrl);
	}

	public function sendpayslipAction()
	{
		$this->checkFeature('payroll');
		$this->checkFeature('payslip');
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$periodId = intval($this->request->get('id'));
		if ($periodId <= 0) {
			Utils::showMsg('请选择工资表', $backUrl);
		}
		$result = PayrollSlipModel::factory()->publishByPeriod($this->companyId, $periodId, $this->getOperatorId());
		if (!$result) {
			Utils::showMsg(PayrollSlipModel::factory()->getLastError(), $backUrl);
		}
		Utils::showMsg('工资条发放成功，共发放' . intval($result) . '人', $backUrl);
	}

	public function archivepayrollAction()
	{
		$this->checkFeature('payroll');
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$periodId = intval($this->request->get('id'));
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		if (!$period) {
			Utils::showMsg('工资表不存在', $backUrl);
		}
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll', 'payroll_month' => $period['payroll_month']));
		if (!PayrollPeriodModel::canArchive($period['status'])) {
			Utils::showMsg('只有审核通过的工资表可以归档', $backUrl);
		}
		$archiveId = PayrollArchiveModel::factory()->createFromPeriod($this->companyId, $periodId, $this->getOperatorId());
		if (!$archiveId) {
			Utils::showMsg(PayrollArchiveModel::factory()->getLastError(), $backUrl);
		}
		PayrollPeriodModel::factory()->markArchived($this->companyId, $periodId, $this->getOperatorId());
		Utils::showMsg('工资表已归档', Helper::factory()->createUrl(array('p' => 'salary/archive')));
	}

	public function commissionAction()
	{
		$this->showFeature('commission', '提成核算');
	}

	public function performanceAction()
	{
		$this->showFeature('performance_salary', '绩效工资核算');
	}

	public function archiveAction()
	{
		$this->checkFeature('payroll');
		$this->view->setVar('periods', PayrollArchiveModel::factory()->getCompanyArchives($this->companyId));
		$this->view->setVar('canSendPayslip', $this->isSalaryFeatureEnabled('payslip'));
	}

	public function restorearchiveAction()
	{
		$this->checkFeature('payroll');
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/archive'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$archiveId = intval($this->request->get('id'));
		$archive = PayrollArchiveModel::factory()->getArchive($this->companyId, $archiveId);
		if (!$archive) {
			Utils::showMsg('归档工资表不存在', $backUrl);
		}
		$periodId = intval($archive['payroll_period_id']);
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		if (!$period) {
			Utils::showMsg('工资表不存在', $backUrl);
		}
		PayrollPeriodModel::factory()->markRejected($this->companyId, $periodId, $this->getOperatorId(), '从归档记录恢复到工资表核算');
		Utils::showMsg('已恢复到工资表核算，原归档记录仍保留', Helper::factory()->createUrl(array('p' => 'salary/payroll', 'payroll_month' => $period['payroll_month'])));
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
		$reviewerIds = SalaryPayrollAuditModel::factory()->getReviewerIds($this->companyId);
		$roleCountMap = SalaryViewRoleModel::factory()->getRoleCountMap($this->companyId);
		foreach ($userItems as $key => $item) {
			$userItems[$key]['role_count'] = isset($roleCountMap[intval($item['id'])]) ? $roleCountMap[intval($item['id'])] : 0;
			$userItems[$key]['is_audit_reviewer'] = in_array(intval($item['id']), $reviewerIds) ? 1 : 0;
		}
		$this->view->setVar('userItems', $userItems);
		$this->view->setVar('reviewerCount', count($reviewerIds));
	}

	public function saveauditreviewersAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/auth'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$reviewerIds = $this->request->get('reviewer_ids');
		if (empty($reviewerIds) || !is_array($reviewerIds)) {
			$reviewerIds = array();
		}
		SalaryPayrollAuditModel::factory()->saveReviewers($this->companyId, $reviewerIds);
		Utils::showMsg('工资表审核人已保存', $backUrl);
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
			array('code' => 'project', 'name' => '工资项目设置', 'url' => 'salary/project', 'desc' => '选择平台通用工资项目，维护企业自定义工资项目。'),
			array('code' => 'payroll', 'name' => '工资表核算', 'url' => 'salary/payroll', 'desc' => '导入或核算工资表，提交审核，审核通过后发工资条并归档。'),
			array('code' => 'archive', 'name' => '工资表归档记录', 'url' => 'salary/archive', 'desc' => '查看已归档工资表，可按归档数据发工资条或恢复重新核算。'),
			array('code' => 'commission', 'name' => '提成核算', 'url' => 'salary/commission', 'desc' => '预留销售提成规则、核算和明细查看入口。'),
			array('code' => 'performance_salary', 'name' => '绩效工资核算', 'url' => 'salary/performance', 'desc' => '预留绩效结果联动工资核算入口。'),
		);
		foreach ($items as $key => $item) {
			if ($item['code'] == 'project' || $item['code'] == 'archive') {
				$items[$key]['enabled'] = 1;
			} else {
				$items[$key]['enabled'] = CompanyModuleAuthModel::isEnabled($authMap, 'salary', $item['code']) ? 1 : 0;
			}
		}
		return $items;
	}

	protected function formatPayrollPeriods($archived = false)
	{
		$periods = PayrollPeriodModel::factory()->getCompanyPeriods($this->companyId, 36, $archived);
		$periodIds = array();
		foreach ($periods as $period) {
			$periodIds[] = intval($period['id']);
		}
		$auditMap = SalaryPayrollAuditModel::factory()->getPeriodAuditMap($this->companyId, $periodIds);
		foreach ($periods as $key => $period) {
			$periodId = intval($period['id']);
			$periods[$key]['status_name'] = PayrollPeriodModel::getStatusName($period['status']);
			$sourceType = isset($period['source_type']) ? $period['source_type'] : 'system';
			$sourceName = isset($period['source_name']) ? $period['source_name'] : '';
			$periods[$key]['source_label'] = PayrollPeriodModel::getSourceName($sourceType, $sourceName);
			$periods[$key]['can_submit_audit'] = PayrollPeriodModel::canSubmitAudit($period['status']) ? 1 : 0;
			$periods[$key]['can_publish'] = PayrollPeriodModel::canPublishPayslip($period['status']) ? 1 : 0;
			$periods[$key]['can_archive'] = PayrollPeriodModel::canArchive($period['status']) ? 1 : 0;
			$periods[$key]['archived_time'] = empty($period['archived_at']) ? '-' : date('Y-m-d H:i', intval($period['archived_at']));
			$periods[$key]['published_time'] = empty($period['published_at']) ? '-' : date('Y-m-d H:i', intval($period['published_at']));
			$periods[$key]['audit'] = isset($auditMap[$periodId]) ? $auditMap[$periodId] : array('items' => array(), 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0);
			$periods[$key]['audit_text'] = $this->formatAuditText($periods[$key]['audit']);
		}
		return $periods;
	}

	protected function formatAuditText($audit)
	{
		if (empty($audit['total'])) {
			return '未提交审核';
		}
		return '同意 ' . intval($audit['approved']) . '/' . intval($audit['total']) . '，待审 ' . intval($audit['pending']);
	}

	protected function isSalaryFeatureEnabled($featureCode)
	{
		$authMap = CompanyModuleAuthModel::getCompanyAuthMap($this->companyId);
		return CompanyModuleAuthModel::isEnabled($authMap, 'salary', $featureCode) ? 1 : 0;
	}

	protected function resolveUploadedPayrollFile($file)
	{
		$file = trim($file);
		if ($file == '' || strpos($file, '/media/excel/') !== 0 || strpos($file, '..') !== false) {
			return false;
		}
		$fullPath = WEBROOT . $file;
		return file_exists($fullPath) ? $fullPath : false;
	}

	protected function outputSalaryTemplate($headers)
	{
		$oldReporting = error_reporting();
		error_reporting($oldReporting & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
		$objPHPExcel = \Phalcon\Di\FactoryDefault::getDefault()->get('phpexcel');
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('工资表模板');
		foreach ($headers as $index => $header) {
			$sheet->setCellValueByColumnAndRow($index, 1, $header);
			if ($index == 0) {
				$sheet->setCellValueByColumnAndRow($index, 2, '张三');
				$sheet->getColumnDimensionByColumn($index)->setWidth(14);
			} elseif ($index == 1) {
				$sheet->setCellValueByColumnAndRow($index, 2, '13800000000');
				$sheet->getColumnDimensionByColumn($index)->setWidth(16);
			} else {
				$sheet->setCellValueByColumnAndRow($index, 2, 0);
				$sheet->getColumnDimensionByColumn($index)->setWidth(14);
			}
		}
		$lastColumn = \PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EAF1FF');
		$sheet->freezePane('A2');
		ob_clean();
		header("Content-Description: File Transfer");
		header("Content-type:application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition:attachment;filename=salary_initial_template.xls");
		header("Content-Transfer-Encoding: binary");
		header("Pragma: public");
		header("Cache-Control:max-age=0");
		$writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$writer->save('php://output');
		error_reporting($oldReporting);
		exit();
	}

	protected function savePayrollUpload($fileName, $extname)
	{
		if (!isset($_FILES[$fileName]) || empty($_FILES[$fileName]['tmp_name'])) {
			return '';
		}
		if (!in_array($extname, array('xls', 'xlsx'))) {
			return '';
		}
		$dir = WEBROOT . '/media/excel/' . date('Y-m-d') . '/';
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		$newFile = $dir . md5(microtime(true) . rand(1000, 9999)) . '.' . $extname;
		if (move_uploaded_file($_FILES[$fileName]['tmp_name'], $newFile)) {
			return str_replace(WEBROOT, '', $newFile);
		}
		return '';
	}

	protected function getOperatorId()
	{
		$user = Helper::factory()->getSession()->get('_user');
		return empty($user->user_id) ? 0 : intval($user->user_id);
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
