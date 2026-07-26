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
use ScshuxCms\Dacang\Model\PlatformUserIdentityModel;
use ScshuxCms\Salary\Model\CompanyModuleAuthModel;
use ScshuxCms\Salary\Model\CommissionArchiveModel;
use ScshuxCms\Salary\Model\CommissionEstimateModel;
use ScshuxCms\Salary\Model\CommissionPeriodModel;
use ScshuxCms\Salary\Model\CommissionProjectModel;
use ScshuxCms\Salary\Model\EmployeeSalaryStructureModel;
use ScshuxCms\Salary\Model\PayrollArchiveModel;
use ScshuxCms\Salary\Model\PayrollEmployeeRowModel;
use ScshuxCms\Salary\Model\PayrollPeriodModel;
use ScshuxCms\Salary\Model\PayrollSlipModel;
use ScshuxCms\Salary\Model\SalaryPayrollImportModel;
use ScshuxCms\Salary\Model\SalaryPayrollAuditModel;
use ScshuxCms\Salary\Model\SalaryOperationLogModel;
use ScshuxCms\Salary\Model\SalaryEmployeeDepartmentModel;
use ScshuxCms\Salary\Model\SalaryProjectModel;
use ScshuxCms\Salary\Model\SalaryProjectTemplateModel;
use ScshuxCms\Salary\Model\SalaryReportModel;
use ScshuxCms\Salary\Model\SalaryViewRoleModel;

class SalaryController extends FrontendBaseController
{
	public function indexAction()
	{
		$this->checkModule();
		$this->view->setVar('features', $this->getSalaryFeatures());
	}

	public function commissionAction()
	{
		$this->checkFeature('commission');
		$model = CommissionProjectModel::factory();
		$editItem = false;
		$editId = intval($this->request->get('id'));
		$projects = $model->getCompanyProjects($this->companyId);
		if ($editId > 0) {
			foreach ($projects as $project) {
				if (intval($project['id']) == $editId) {
					$editItem = $project;
					break;
				}
			}
		}
		$this->view->setVar('projects', $projects);
		$this->view->setVar('scopeOptions', $model->getScopeOptions($this->companyId));
		$this->view->setVar('editItem', $editItem);
		$this->view->setVar('metricLabels', CommissionProjectModel::getMetricLabels());
		$this->view->setVar('modeLabels', CommissionProjectModel::getModeLabels());
		$this->view->setVar('scopeLabels', CommissionProjectModel::getScopeLabels());
		$this->view->setVar('statusLabels', CommissionProjectModel::getStatusLabels());
		$this->view->setVar('rateTypeLabels', CommissionProjectModel::getRateTypeLabels());
	}

	public function commissionsaveAction()
	{
		$this->checkFeature('commission');
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commission'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$model = CommissionProjectModel::factory();
		if (!$model->saveProject($this->companyId, $_POST, $this->getOperatorId())) {
			Utils::showMsg($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('commission_project_save', 'commission_project', intval($this->request->getPost('id')), '', '保存提成项目规则');
		Utils::showMsg('提成项目已保存', $backUrl);
	}

	public function commissiondeleteAction()
	{
		$this->checkFeature('commission');
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commission'));
		if (!$this->request->isPost()) {
			$this->respondSalaryDeleteError('不支持的请求方式', $backUrl);
		}
		$projectId = intval($this->request->getPost('id'));
		$model = CommissionProjectModel::factory();
		if (!$model->deleteProject($this->companyId, $projectId)) {
			$this->respondSalaryDeleteError($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('commission_project_delete', 'commission_project', $projectId, '', '删除提成项目规则');
		$this->respondSalaryDeleteSuccess('提成项目已删除', $backUrl, array('project_id' => $projectId));
	}

	public function commissionestimateAction()
	{
		$this->checkFeature('commission');
		$model = CommissionEstimateModel::factory();
		$employees = $model->getCompanyEmployees($this->companyId);
		$recordId = intval($this->request->get('record_id'));
		$record = $recordId > 0 ? $model->getRecord($this->companyId, $recordId) : false;
		$employeeId = intval($this->request->get('employee_id'));
		if ($this->request->isPost()) {
			$employeeId = intval($this->request->getPost('employee_id'));
		}
		if ($record && !empty($record['estimate']['employee']['id'])) {
			$employeeId = intval($record['estimate']['employee']['id']);
		}
		if ($employeeId <= 0 && !empty($employees)) {
			$employeeId = intval($employees[0]['id']);
		}

		$inputValues = $this->request->isPost() && isset($_POST['estimate']) && is_array($_POST['estimate']) ? $_POST['estimate'] : array();
		$estimate = $record && !empty($record['estimate']) ? $record['estimate'] : ($employeeId > 0 ? $model->calculateEstimate($this->companyId, $employeeId, $inputValues) : false);
		if ($this->request->isPost() && trim($this->request->getPost('estimate_action')) == 'save') {
			$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commissionestimate', 'employee_id' => $employeeId));
			if (!$estimate) {
				Utils::showMsg($model->getLastError(), $backUrl);
			}
			$savedId = $model->saveEstimate($this->companyId, $estimate, $this->getOperatorId());
			if (!$savedId) {
				Utils::showMsg($model->getLastError(), $backUrl);
			}
			$this->addSalaryLog('commission_estimate_save', 'commission_estimate', intval($savedId), '', '保存员工月收入提成测算');
			Utils::showMsg('提成测算记录已保存', Helper::factory()->createUrl(array('p' => 'salary/commissionestimate', 'record_id' => intval($savedId))));
		}

		$this->view->setVar('employees', $employees);
		$this->view->setVar('employeeId', $employeeId);
		$this->view->setVar('estimate', $estimate);
		$this->view->setVar('estimateRecord', $record);
		$this->view->setVar('estimateRecords', $model->getCompanyRecords($this->companyId));
	}

	/**
	 * Recalculate an estimate without saving a record.
	 */
	public function commissionestimatecalculateAction()
	{
		$this->checkFeature('commission');
		if (!$this->request->isPost()) {
			$this->sendErrorResult('Unsupported request method');
		}
		$employeeId = intval($this->request->getPost('employee_id'));
		$inputValues = isset($_POST['estimate']) && is_array($_POST['estimate']) ? $_POST['estimate'] : array();
		$model = CommissionEstimateModel::factory();
		$estimate = $model->calculateEstimate($this->companyId, $employeeId, $inputValues);
		if (!$estimate) {
			$this->sendErrorResult($model->getLastError());
		}
		$this->sendSuccessResult($estimate);
	}

	/**
	 * Update a project's calculation rule from the live estimate and recalculate it.
	 */
	public function commissionestimatesaveruleAction()
	{
		$this->checkFeature('commission');
		if (!$this->request->isPost()) {
			$this->sendErrorResult('Unsupported request method');
		}
		$employeeId = intval($this->request->getPost('employee_id'));
		$estimateModel = CommissionEstimateModel::factory();
		if (!$estimateModel->getEmployee($this->companyId, $employeeId)) {
			$this->sendErrorResult('员工不存在或不属于当前企业');
		}

		$projectId = intval($this->request->getPost('project_id'));
		$projectModel = CommissionProjectModel::factory();
		$project = $projectModel->saveCalculationRule($this->companyId, $projectId, $_POST);
		if (!$project) {
			$this->sendErrorResult($projectModel->getLastError());
		}

		$inputValues = isset($_POST['estimate']) && is_array($_POST['estimate']) ? $_POST['estimate'] : array();
		$estimate = $estimateModel->calculateEstimate($this->companyId, $employeeId, $inputValues);
		if (!$estimate) {
			$this->sendErrorResult($estimateModel->getLastError());
		}
		$this->addSalaryLog('commission_project_rule_save', 'commission_project', $projectId, '', '在提成测算中修改提成项目规则：' . $project['name'] . '，新规则：' . $project['rule_summary']);
		$this->sendSuccessResult(array(
			'estimate' => $estimate,
			'project' => array(
				'id' => intval($project['id']),
				'rule_summary' => $project['rule_summary'],
			),
		));
	}

	public function deletecommissionestimateAction()
	{
		$this->checkFeature('commission');
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commissionestimate'));
		if (!$this->request->isPost()) {
			$this->respondSalaryDeleteError('不支持的请求方式', $backUrl);
		}
		$recordId = intval($this->request->getPost('id'));
		$model = CommissionEstimateModel::factory();
		if (!$model->deleteRecord($this->companyId, $recordId, $this->getOperatorId())) {
			$this->respondSalaryDeleteError($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('commission_estimate_delete', 'commission_estimate', $recordId, '', '删除员工月收入提成测算');
		$this->respondSalaryDeleteSuccess('提成测算记录已删除', $backUrl, array('record_id' => $recordId));
	}

	public function commissionpayrollAction()
	{
		$this->checkFeature('commission');
		$commissionMonth = trim($this->request->get('commission_month'));
		if (!preg_match('/^\d{4}\-\d{2}$/', $commissionMonth)) {
			$commissionMonth = date('Y-m');
		}
		$model = CommissionPeriodModel::factory();
		$period = $model->getEditableCompanyPeriodByMonth($this->companyId, $commissionMonth);
		$archivedPeriod = CommissionArchiveModel::factory()->getActiveArchiveByMonth($this->companyId, $commissionMonth);
		$rows = array();
		$editEmployeeId = intval($this->request->get('edit_employee_id'));
		$editRow = false;
		$selectedProjectMap = array();
		if ($period) {
			$period['status_name'] = CommissionPeriodModel::getStatusName($period['status']);
			$period['can_edit'] = in_array($period['status'], array('draft', 'calculated')) ? 1 : 0;
			$rows = $model->getCommissionMatrix($this->companyId, intval($period['id']));
			foreach ($rows as $row) {
				if ($editEmployeeId > 0 && intval($row['employee_id']) == $editEmployeeId) {
					$editRow = $row;
					foreach ($row['items'] as $item) {
						$selectedProjectMap[intval($item['commission_project_id'])] = 1;
					}
					break;
				}
			}
		}
		$projects = CommissionProjectModel::factory()->getCompanyProjects($this->companyId);
		$this->view->setVar('commissionMonth', $commissionMonth);
		$this->view->setVar('period', $period);
		$this->view->setVar('commissionRows', $rows);
		$this->view->setVar('commissionProjects', $projects);
		$this->view->setVar('archivedPeriod', $archivedPeriod);
		$this->view->setVar('editRow', $editRow);
		$this->view->setVar('selectedProjectMap', $selectedProjectMap);
	}

	public function generatecommissionAction()
	{
		$this->checkFeature('commission');
		$commissionMonth = trim($this->request->getPost('commission_month'));
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commissionpayroll', 'commission_month' => $commissionMonth));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$model = CommissionPeriodModel::factory();
		if (CommissionArchiveModel::factory()->getActiveArchiveByMonth($this->companyId, $commissionMonth)) {
			Utils::showMsg('该月份提成表已归档，请到归档记录查看或恢复后再核算', $backUrl);
		}
		if ($model->getEditableCompanyPeriodByMonth($this->companyId, $commissionMonth)) {
			Utils::showMsg('该月份的提成核算表已经存在，已为您打开', $backUrl);
		}
		$result = $model->generateFromEmployees($this->companyId, $commissionMonth, $this->getOperatorId());
		if (!$result) {
			Utils::showMsg($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('commission_generate', 'commission_period', intval($result), $commissionMonth, '生成月提成核算表');
		Utils::showMsg('月提成核算表已生成', $backUrl);
	}

	public function savecommissionpayrollAction()
	{
		$this->checkFeature('commission');
		$periodId = intval($this->request->getPost('id'));
		$period = CommissionPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commissionpayroll', 'commission_month' => $period ? $period['commission_month'] : date('Y-m')));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$result = CommissionPeriodModel::factory()->saveCommissionMatrixFromPost($this->companyId, $periodId, $_POST, $this->getOperatorId());
		if (!$result) {
			Utils::showMsg(CommissionPeriodModel::factory()->getLastError(), $backUrl);
		}
		$this->addSalaryLog('commission_save', 'commission_period', $periodId, $period ? $period['commission_month'] : '', '保存月提成核算表');
		Utils::showMsg('月提成核算表已保存', $backUrl);
	}

	public function savecommissionemployeeprojectsAction()
	{
		$this->checkFeature('commission');
		$periodId = intval($this->request->getPost('id'));
		$employeeId = intval($this->request->getPost('employee_id'));
		$period = CommissionPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commissionpayroll', 'commission_month' => $period ? $period['commission_month'] : date('Y-m')));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$projectIds = isset($_POST['project_ids']) ? $_POST['project_ids'] : array();
		$model = CommissionPeriodModel::factory();
		if (!$model->saveEmployeeProjectSelection($this->companyId, $periodId, $employeeId, $projectIds, $this->getOperatorId())) {
			Utils::showMsg($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('commission_employee_projects_save', 'commission_period', $periodId, $period ? $period['commission_month'] : '', '修改员工提成项目，员工ID：' . $employeeId);
		Utils::showMsg('员工提成项目已更新', $backUrl);
	}

	public function deletecommissionemployeeAction()
	{
		$this->checkFeature('commission');
		$periodId = intval($this->request->getPost('id'));
		$employeeId = intval($this->request->getPost('employee_id'));
		$period = CommissionPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commissionpayroll', 'commission_month' => $period ? $period['commission_month'] : date('Y-m')));
		if (!$this->request->isPost()) {
			$this->respondSalaryDeleteError('不支持的请求方式', $backUrl);
		}
		$model = CommissionPeriodModel::factory();
		if (!$model->deleteEmployeeRow($this->companyId, $periodId, $employeeId)) {
			$this->respondSalaryDeleteError($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('commission_employee_delete', 'commission_period', $periodId, $period ? $period['commission_month'] : '', '从月提成核算表删除员工，员工ID：' . $employeeId);
		$period = $model->getCompanyPeriod($this->companyId, $periodId);
		$this->respondSalaryDeleteSuccess('员工已从当前月提成核算表删除，不影响人事档案', $backUrl, array(
			'employee_id' => $employeeId,
			'employee_count' => $period ? intval($period['employee_count']) : 0,
			'matched_count' => $period ? intval($period['matched_count']) : 0,
			'total_amount' => $period ? $period['total_amount'] : '0.00',
		));
	}

	public function commissionarchiveAction()
	{
		$this->checkFeature('commission');
		$filter = array(
			'commission_month' => trim($this->request->get('commission_month')),
			'department_name' => trim($this->request->get('department_name')),
			'employee_name' => trim($this->request->get('employee_name')),
		);
		$archives = CommissionArchiveModel::factory()->getCompanyArchives($this->companyId, $filter);
		$this->view->setVar('filter', $filter);
		$this->view->setVar('archives', $archives);
	}

	public function commissionarchiveviewAction()
	{
		$this->checkFeature('commission');
		$archiveId = intval($this->request->get('id'));
		$model = CommissionArchiveModel::factory();
		$archive = $model->getArchive($this->companyId, $archiveId);
		if (!$archive) {
			Utils::showMsg('Commission archive record does not exist', Helper::factory()->createUrl(array('p' => 'salary/commissionarchive')));
		}
		$this->view->setVar('archive', $archive);
		$this->view->setVar('rows', $model->getArchiveRows($this->companyId, $archiveId));
	}

	public function archivecommissionAction()
	{
		$this->checkFeature('commission');
		$periodId = intval($this->request->getPost('id'));
		$period = CommissionPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commissionpayroll', 'commission_month' => $period ? $period['commission_month'] : date('Y-m')));
		if (!$this->request->isPost()) {
			Utils::showMsg('Unsupported request method', $backUrl);
		}
		$model = CommissionArchiveModel::factory();
		$archiveId = $model->archivePeriod($this->companyId, $periodId, $this->getOperatorId());
		if (!$archiveId) {
			Utils::showMsg($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('commission_archive', 'commission_archive', intval($archiveId), $period ? $period['commission_month'] : '', 'Archive monthly commission calculation sheet');
		Utils::showMsg('Commission calculation sheet archived', Helper::factory()->createUrl(array('p' => 'salary/commissionarchive')));
	}

	public function restorecommissionarchiveAction()
	{
		$this->checkFeature('commission');
		$archiveId = intval($this->request->getPost('id'));
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commissionarchive'));
		if (!$this->request->isPost()) {
			Utils::showMsg('Unsupported request method', $backUrl);
		}
		$model = CommissionArchiveModel::factory();
		$archive = $model->getArchive($this->companyId, $archiveId);
		$result = $model->restoreToCalculation($this->companyId, $archiveId, $this->getOperatorId());
		if (!$result) {
			Utils::showMsg($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('commission_restore', 'commission_archive', $archiveId, $archive ? $archive['commission_month'] : '', 'Restore commission archive to calculation sheet');
		Utils::showMsg('Restored to monthly commission calculation sheet', Helper::factory()->createUrl(array('p' => 'salary/commissionpayroll', 'commission_month' => $archive ? $archive['commission_month'] : date('Y-m'))));
	}

	public function deletecommissionarchiveAction()
	{
		$this->checkFeature('commission');
		$archiveId = intval($this->request->getPost('id'));
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/commissionarchive'));
		if (!$this->request->isPost()) {
			$this->respondSalaryDeleteError('不支持的请求方式', $backUrl);
		}
		$model = CommissionArchiveModel::factory();
		$archive = $model->getArchive($this->companyId, $archiveId);
		if (!$model->deleteArchive($this->companyId, $archiveId, $this->getOperatorId())) {
			$this->respondSalaryDeleteError($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('commission_archive_delete', 'commission_archive', $archiveId, $archive ? $archive['commission_month'] : '', 'Delete commission archive record');
		$this->respondSalaryDeleteSuccess('归档记录已删除，服务器备份保留六个月', $backUrl, array('archive_id' => $archiveId));
	}

	public function logAction()
	{
		$this->checkModule();
		$filter = array(
			'action_code' => trim($this->request->get('action_code')),
			'payroll_month' => trim($this->request->get('payroll_month')),
		);
		$page = intval($this->request->get('page'));
		if ($page <= 0) {
			$page = 1;
		}
		$pageSize = 30;
		$model = SalaryOperationLogModel::factory();
		$total = $model->getCompanyLogCount($this->companyId, $filter);
		$logs = $model->getCompanyLogs($this->companyId, $filter, $page, $pageSize);
		$pageCount = max(1, ceil($total / $pageSize));
		$this->view->setVar('logs', $logs);
		$this->view->setVar('filter', $filter);
		$this->view->setVar('actionLabels', SalaryOperationLogModel::getActionLabels());
		$this->view->setVar('page', $page);
		$this->view->setVar('pageCount', $pageCount);
		$this->view->setVar('total', $total);
	}

	public function reportAction()
	{
		$this->checkFeature('payroll');
		$reportModel = SalaryReportModel::factory();
		$filter = $this->buildSalaryReportFilter($reportModel);
		$scope = $this->getSalaryReportScope();
		$page = intval($this->request->get('page'));
		if ($page <= 0) {
			$page = 1;
		}
		$pageSize = 50;
		$summary = $reportModel->getSummary($this->companyId, $filter, $scope);
		$rows = $reportModel->getRows($this->companyId, $filter, $scope, $page, $pageSize);
		$pageCount = max(1, ceil(intval($summary['row_count']) / $pageSize));
		$this->view->setVar('filter', $filter);
		$this->view->setVar('summary', $summary);
		$this->view->setVar('rows', $rows);
		$this->view->setVar('months', $reportModel->getPayrollMonths($this->companyId));
		$this->view->setVar('departments', $reportModel->getDepartments($this->companyId, $scope));
		$this->view->setVar('scope', $scope);
		$this->view->setVar('page', $page);
		$this->view->setVar('pageCount', $pageCount);
	}

	public function reportexportAction()
	{
		$this->checkFeature('payroll');
		$reportModel = SalaryReportModel::factory();
		$filter = $this->buildSalaryReportFilter($reportModel);
		$scope = $this->getSalaryReportScope();
		if (empty($scope['can_export'])) {
			Utils::showMsg('当前账号没有薪酬报表导出权限', Helper::factory()->createUrl(array('p' => 'salary/report')));
		}
		$rows = $reportModel->getAllRows($this->companyId, $filter, $scope);
		$this->addSalaryLog('salary_report_export', 'salary_report', 0, $filter['payroll_month'], '导出薪酬报表，条数' . count($rows));
		$this->outputSalaryReportExport($filter, $rows);
	}

	public function projectAction()
	{
		$this->checkModule();
		$templates = SalaryProjectTemplateModel::factory()->getActiveTemplates();
		$templateProjectMap = SalaryProjectModel::factory()->getCompanyTemplateProjectMap($this->companyId, true);
		$directions = SalaryProjectModel::getDirectionLabels();
		$sourceTypes = SalaryProjectModel::getSourceTypeOptions();
		$sourceTypeLabels = SalaryProjectModel::getSourceTypeLabels();
		foreach ($templates as $key => $template) {
			$templateId = intval($template['id']);
			$companyProject = isset($templateProjectMap[$templateId]) ? $templateProjectMap[$templateId] : false;
			if ($companyProject) {
				foreach (array('name', 'source_type', 'direction', 'calculation_mode', 'linked_module', 'formula_text', 'default_number', 'default_text', 'sort_order', 'status') as $field) {
					if (isset($companyProject[$field])) {
						$templates[$key][$field] = $companyProject[$field];
					}
				}
			}
			$templates[$key]['project_id'] = $companyProject ? intval($companyProject['id']) : 0;
			$templates[$key]['is_selected'] = $companyProject && $companyProject['status'] == 'active' ? 1 : 0;
			$templates[$key]['direction'] = SalaryProjectModel::normalizeDirection($templates[$key]['direction']);
			$templates[$key]['direction_label'] = SalaryProjectModel::label($directions, $templates[$key]['direction']);
			$templates[$key]['source_type_label'] = SalaryProjectModel::label($sourceTypeLabels, $templates[$key]['source_type']);
		}
		$templates = array_values($templates);

		$editId = intval($this->request->get('id'));
		$editTemplateId = intval($this->request->get('template_id'));
		$editItem = false;
		if ($editId > 0) {
			$editItem = SalaryProjectModel::factory()->findFirst('id=' . $editId . ' and company_id=' . intval($this->companyId) . ' and deleted_at=0');
		} elseif ($editTemplateId > 0) {
			if (isset($templateProjectMap[$editTemplateId]) && intval($templateProjectMap[$editTemplateId]['deleted_at']) == 0) {
				$projectId = intval($templateProjectMap[$editTemplateId]['id']);
				$editItem = SalaryProjectModel::factory()->findFirst('id=' . $projectId . ' and company_id=' . intval($this->companyId) . ' and deleted_at=0');
			} else {
				$template = SalaryProjectTemplateModel::factory()->findFirst('id=' . $editTemplateId . ' and status="active"');
				if ($template) {
					$editItem = new \stdClass();
					foreach (array('name', 'source_type', 'direction', 'calculation_mode', 'linked_module', 'sort_order') as $field) {
						$editItem->$field = $template->$field;
					}
					$editItem->id = 0;
					$editItem->template_id = $editTemplateId;
					$editItem->formula_text = '';
					$editItem->default_number = '0.00';
					$editItem->default_text = '';
					$editItem->status = 'inactive';
				}
			}
		}
		if ($editItem) {
			$editItem->direction = SalaryProjectModel::normalizeDirection($editItem->direction);
			if (intval($editItem->template_id) <= 0) {
				$editItem->status = 'active';
			}
		}

		$projects = SalaryProjectModel::factory()->getCompanyProjects($this->companyId);
		$companyProjectsForView = array();
		$formulaProjects = array();
		$editProjectId = $editItem ? intval($editItem->id) : 0;
		foreach ($projects as $project) {
			if (empty($project['template_id']) || $project['status'] == 'active') {
				$companyProjectsForView[] = $project;
			}
			if ($project['status'] != 'active' || intval($project['deleted_at']) > 0) {
				continue;
			}
			if ($editProjectId > 0 && intval($project['id']) == $editProjectId) {
				continue;
			}
			if (SalaryProjectModel::isTextProject($project)) {
				continue;
			}
			$formulaProjects[] = $project;
		}

		$this->view->setVar('templates', $templates);
		$this->view->setVar('fixedProjects', SalaryProjectModel::getFixedSummaryProjects());
		$this->view->setVar('projects', $companyProjectsForView);
		$this->view->setVar('formulaProjects', $formulaProjects);
		$this->view->setVar('editItem', $editItem);
		$this->view->setVar('sourceTypes', $sourceTypes);
		$this->view->setVar('directions', $directions);
		$this->view->setVar('calculationModes', SalaryProjectModel::getCalculationModeLabels());
		$this->view->setVar('statusLabels', SalaryProjectModel::getStatusLabels());
		$initialTable = EmployeeSalaryStructureModel::factory()->getInitialSalaryTable($this->companyId);
		$initialDepartments = array();
		$initialPositions = array();
		foreach ($initialTable['employees'] as $employee) {
			$departmentName = trim($employee['department_name']);
			$positionName = trim($employee['position_name']);
			$departmentName = $departmentName == '' ? '未设置部门' : $departmentName;
			$positionName = $positionName == '' ? '未设置岗位' : $positionName;
			$initialDepartments[$departmentName] = $departmentName;
			$initialPositions[$positionName] = $positionName;
		}
		$initialDepartments = array_values($initialDepartments);
		$initialPositions = array_values($initialPositions);
		sort($initialDepartments);
		sort($initialPositions);
		$this->view->setVar('initialProjects', $initialTable['projects']);
		$this->view->setVar('initialEmployees', $initialTable['employees']);
		$this->view->setVar('excludedInitialEmployees', $initialTable['excluded_employees']);
		$this->view->setVar('initialDepartments', $initialDepartments);
		$this->view->setVar('initialPositions', $initialPositions);
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
		$this->addSalaryLog('project_template_save', 'salary_project', 0, '', '保存通用工资项目选择');
		Utils::showMsg('通用工资项目已保存', $backUrl);
	}

	public function projectenabletemplateAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$templateId = intval($this->request->getPost('template_id'));
		$model = SalaryProjectModel::factory();
		if (!$model->enableCompanyTemplateProject($this->companyId, $templateId)) {
			if ($this->isSalaryAjaxRequest()) {
				$this->sendErrorResult($model->getLastError());
			}
			Utils::showMsg($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('project_template_enable', 'salary_project', $templateId, '', '启用通用工资项目');
		if ($this->isSalaryAjaxRequest()) {
			$project = false;
			$projects = $model->getCompanyProjects($this->companyId);
			foreach ($projects as $item) {
				if (intval($item['template_id']) == $templateId && $item['status'] == 'active') {
					$project = $item;
					break;
				}
			}
			if (!$project) {
				$this->sendErrorResult('通用工资项目已启用，但页面数据获取失败，请刷新后查看');
			}
			$project['edit_url'] = Helper::factory()->createUrl(array('p' => 'salary/project', 'id' => intval($project['id'])));
			$project['disable_url'] = Helper::factory()->createUrl(array('p' => 'salary/projectdisable'));
			$this->sendSuccessResult(array('message' => '通用工资项目已启用', 'project' => $project));
		}
		Utils::showMsg('通用工资项目已启用，并已纳入企业工资项目', $backUrl);
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
		$this->addSalaryLog('project_save', 'salary_project', intval(isset($_POST['id']) ? $_POST['id'] : 0), '', '保存工资项目');
		Utils::showMsg('工资项目已保存', $backUrl);
	}

	public function saveprojectorderAction()
	{
		$this->checkModule();
		if (!$this->request->isPost()) {
			$this->sendErrorResult('不支持的请求方式');
		}
		$direction = trim($this->request->getPost('direction'));
		$projectIds = $this->request->getPost('project_ids');
		$model = SalaryProjectModel::factory();
		if (!$model->saveCompanyProjectOrder($this->companyId, $direction, $projectIds)) {
			$this->sendErrorResult($model->getLastError());
		}
		$this->addSalaryLog('project_order_save', 'salary_project', 0, '', '调整初始工资表工资项目顺序');
		$this->sendSuccessResult(array('message' => '项目顺序已保存'));
	}

	public function projectdeleteAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			$this->respondSalaryDeleteError('不支持的请求方式', $backUrl);
		}
		$projectId = intval($this->request->getPost('id'));
		$templateId = intval($this->request->getPost('template_id'));
		if ($templateId > 0) {
			$result = SalaryProjectModel::factory()->deleteCompanyTemplateProject($this->companyId, $templateId);
		} else {
			$result = SalaryProjectModel::factory()->deleteCompanyProject($this->companyId, $projectId);
		}
		if (!$result) {
			$this->respondSalaryDeleteError(SalaryProjectModel::factory()->getLastError(), $backUrl);
		}
		$this->addSalaryLog('project_delete', 'salary_project', $projectId, '', $templateId > 0 ? '删除当前企业通用工资项目' : '删除工资项目');
		$this->respondSalaryDeleteSuccess('工资项目已删除', $backUrl, array('project_id' => $projectId, 'template_id' => $templateId));
	}

	public function projectdisableAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			if ($this->isSalaryAjaxRequest()) {
				$this->sendErrorResult('不支持的请求方式');
			}
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$projectId = intval($this->request->getPost('id'));
		$model = SalaryProjectModel::factory();
		$project = $model->findFirst('id=' . $projectId . ' and company_id=' . intval($this->companyId) . ' and deleted_at=0');
		if (!$project) {
			if ($this->isSalaryAjaxRequest()) {
				$this->sendErrorResult('工资项目不存在');
			}
			Utils::showMsg('工资项目不存在', $backUrl);
		}
		$templateId = intval($project->template_id);
		$result = $model->disableCompanyProject($this->companyId, $projectId);
		if (!$result) {
			if ($this->isSalaryAjaxRequest()) {
				$this->sendErrorResult($model->getLastError());
			}
			Utils::showMsg($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('project_disable', 'salary_project', $projectId, '', '停用工资项目');
		if ($this->isSalaryAjaxRequest()) {
			$this->sendSuccessResult(array(
				'message' => '通用工资项目已停用',
				'project_id' => $projectId,
				'template_id' => $templateId,
			));
		}
		Utils::showMsg('工资项目已停用', $backUrl);
	}

	public function saveinitialsalaryAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			$this->respondSalaryDeleteError('不支持的请求方式', $backUrl);
		}
		$model = EmployeeSalaryStructureModel::factory();
		$result = $model->saveInitialSalaryTable($this->companyId, $_POST, $this->getOperatorId());
		if (!$result) {
			$this->respondSalaryDeleteError($model->getLastError(), $backUrl);
		}
		$employeeId = intval($this->request->getPost('initial_salary_employee_id'));
		$this->addSalaryLog('initial_salary_employee_save', 'employee_salary_structure', $employeeId, '', '保存员工初始工资数据');
		$this->respondSalaryDeleteSuccess('员工初始工资数据已保存', $backUrl, array('employee_id' => $employeeId));
	}

	public function deleteinitialsalaryemployeeAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			$this->respondSalaryDeleteError('不支持的请求方式', $backUrl);
		}
		$employeeId = intval($this->request->getPost('initial_salary_employee_id'));
		$model = EmployeeSalaryStructureModel::factory();
		if (!$model->deleteInitialSalaryEmployee($this->companyId, $employeeId, $this->getOperatorId())) {
			$this->respondSalaryDeleteError($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('initial_salary_employee_delete', 'employee_salary_structure', $employeeId, '', '从初始工资表移出员工');
		$this->respondSalaryDeleteSuccess('员工已从初始工资表移出，不影响人事档案和历史工资记录', $backUrl, array('employee_id' => $employeeId));
	}

	public function restoreinitialsalaryemployeeAction()
	{
		$this->checkModule();
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/project'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$employeeId = intval($this->request->getPost('employee_id'));
		$model = EmployeeSalaryStructureModel::factory();
		if (!$model->restoreInitialSalaryEmployee($this->companyId, $employeeId)) {
			Utils::showMsg($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('initial_salary_employee_restore', 'employee_salary_structure', $employeeId, '', '恢复员工到初始工资表');
		Utils::showMsg('员工已恢复到初始工资表', $backUrl);
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
		$this->addSalaryLog('initial_salary_import', 'employee_salary_structure', 0, '', '导入初始工资表，人数' . intval($result['employee_count']));
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
			$projects = PayrollEmployeeRowModel::factory()->getPayrollProjectSnapshots($this->companyId, intval($period['id']));
			$rows = PayrollEmployeeRowModel::factory()->getPayrollMatrix($this->companyId, intval($period['id']));
			$period['status_name'] = PayrollPeriodModel::getStatusName($period['status']);
			$period['can_edit'] = PayrollPeriodModel::canEdit($period['status']) ? 1 : 0;
			$period['can_archive'] = PayrollPeriodModel::canArchive($period['status']) ? 1 : 0;
		}
		$displayProjects = EmployeeSalaryStructureModel::factory()->buildSalaryTableDisplayProjects($projects);
		$departments = array();
		$positions = array();
		foreach ($rows as $key => $row) {
			$rows[$key]['values']['summary_earning_total'] = sprintf('%.2f', floatval($row['earning_total']));
			$rows[$key]['values']['summary_deduction_total'] = sprintf('%.2f', floatval($row['deduction_total']));
			$rows[$key]['values']['summary_net_total'] = sprintf('%.2f', floatval($row['net_amount']));
			$departmentName = trim($row['department_name']) == '' ? '未设置部门' : trim($row['department_name']);
			$positionName = trim($row['position_name']) == '' ? '未设置岗位' : trim($row['position_name']);
			$departments[$departmentName] = $departmentName;
			$positions[$positionName] = $positionName;
		}
		$departments = array_values($departments);
		$positions = array_values($positions);
		sort($departments);
		sort($positions);
		$this->view->setVar('payrollMonth', $payrollMonth);
		$this->view->setVar('period', $period);
		$this->view->setVar('projects', $projects);
		$this->view->setVar('payrollDisplayProjects', $displayProjects);
		$this->view->setVar('payrollRows', $rows);
		$this->view->setVar('payrollDepartments', $departments);
		$this->view->setVar('payrollPositions', $positions);
		$this->view->setVar('canExportPayroll', $this->canExportSalaryData());
		$this->view->setVar('defaultPayrollMonth', date('Y-m'));
	}

	public function payslipAction()
	{
		$this->checkFeature('payroll');
		$this->checkFeature('payslip');
		$periods = PayrollSlipModel::factory()->getCompanyPublishedPeriods($this->companyId);
		$this->view->setVar('periods', $this->formatPayslipPeriods($periods));
	}

	public function payslipdetailAction()
	{
		$this->checkFeature('payroll');
		$this->checkFeature('payslip');
		$periodId = intval($this->request->get('id'));
		$status = trim($this->request->get('status'));
		$from = trim($this->request->get('from'));
		if (!in_array($status, array('all', 'unviewed', 'viewed_unconfirmed', 'confirmed'))) {
			$status = 'all';
		}
		$backUrl = $from == 'archive' ? Helper::factory()->createUrl(array('p' => 'salary/archive')) : Helper::factory()->createUrl(array('p' => 'salary/payslip'));
		if ($periodId <= 0) {
			Utils::showMsg('请选择工资表', $backUrl);
		}
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		if (!$period) {
			Utils::showMsg('工资表不存在', $backUrl);
		}
		$items = PayrollSlipModel::factory()->getPeriodSlipDetails($this->companyId, $periodId, $status);
		$items = $this->formatPayslipDetailItems($items);
		$periodItems = $this->appendPayslipConfirmStats(array($period));
		$period = $this->formatPayslipPeriods($periodItems);
		$exportScope = $this->buildPayslipExportScope($items);
		$this->view->setVar('period', empty($period) ? false : $period[0]);
		$this->view->setVar('items', $items);
		$this->view->setVar('departments', $exportScope['departments']);
		$this->view->setVar('employees', $exportScope['employees']);
		$this->view->setVar('canExportPayslip', $this->canExportSalaryData());
		$this->view->setVar('status', $status);
		$this->view->setVar('sourcePage', $from == 'archive' ? 'archive' : 'payslip');
		$this->view->setVar('backUrl', $backUrl);
	}

	public function payslipexportAction()
	{
		$this->checkFeature('payroll');
		$this->checkFeature('payslip');
		$periodId = intval($this->request->get('id'));
		$status = trim($this->request->get('status'));
		$from = trim($this->request->get('from'));
		if (!in_array($status, array('all', 'unviewed', 'viewed_unconfirmed', 'confirmed'))) {
			$status = 'all';
		}
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payslipdetail', 'id' => $periodId, 'status' => $status, 'from' => $from));
		if ($periodId <= 0) {
			Utils::showMsg('请选择工资表', Helper::factory()->createUrl(array('p' => 'salary/payslip')));
		}
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		if (!$period) {
			Utils::showMsg('工资表不存在', Helper::factory()->createUrl(array('p' => 'salary/payslip')));
		}
		if (!$this->canExportSalaryData()) {
			Utils::showMsg('No salary export permission', $backUrl);
		}
		$items = PayrollSlipModel::factory()->getPeriodSlipDetails($this->companyId, $periodId, $status);
		$items = $this->formatPayslipDetailItems($items);
		$filteredItems = $this->filterPayslipExportItems($items, $_GET);
		if ($filteredItems === false) {
			Utils::showMsg('请选择导出范围', $backUrl);
		}
		$this->addSalaryLog('payslip_export', 'payroll_period', $periodId, $period['payroll_month'], '导出工资条确认结果，条数' . count($filteredItems));
		$this->outputPayslipConfirmExport($period, $filteredItems);
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
			$this->respondSalaryDeleteError(PayrollPeriodModel::factory()->getLastError(), $backUrl);
		}
		$this->addSalaryLog('payroll_generate', 'payroll_period', intval($result), $payrollMonth, '从初始工资表生成工资表');
		if ($this->isSalaryAjaxRequest()) {
			$this->respondSalaryDeleteSuccess('已从初始工资表生成本月工资表', $backUrl, array(
				'payroll_month' => $payrollMonth,
				'reload_url' => $backUrl,
			));
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
		$model = PayrollPeriodModel::factory();
		$result = $model->savePayrollMatrixFromPost($this->companyId, $periodId, $_POST, $this->getOperatorId());
		if (!$result) {
			$this->respondSalaryDeleteError($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('payroll_save', 'payroll_period', $periodId, $period ? $period['payroll_month'] : '', '保存工资表核算数据');
		if ($this->isSalaryAjaxRequest()) {
			$savedPeriod = $model->getCompanyPeriod($this->companyId, $periodId);
			$employeeId = intval($this->request->getPost('payroll_employee_id'));
			$savedRow = false;
			foreach (PayrollEmployeeRowModel::factory()->getPayrollMatrix($this->companyId, $periodId) as $row) {
				if (intval($row['employee_id']) == $employeeId) {
					$savedRow = $row;
					break;
				}
			}
			$this->respondSalaryDeleteSuccess('工资表数据已保存', $backUrl, array(
				'employee_id' => $employeeId,
				'earning_total' => $savedRow ? $savedRow['earning_total'] : '0.00',
				'deduction_total' => $savedRow ? $savedRow['deduction_total'] : '0.00',
				'net_amount' => $savedRow ? $savedRow['net_amount'] : '0.00',
				'period_earning_total' => $savedPeriod ? $savedPeriod['earning_total'] : '0.00',
				'period_deduction_total' => $savedPeriod ? $savedPeriod['deduction_total'] : '0.00',
				'period_net_total' => $savedPeriod ? $savedPeriod['net_total'] : '0.00',
			));
		}
		Utils::showMsg('工资表已保存', $backUrl);
	}

	public function savepayrollprojectorderAction()
	{
		$this->checkFeature('payroll');
		if (!$this->request->isPost()) {
			$this->sendErrorResult('不支持的请求方式');
		}
		$periodId = intval($this->request->getPost('id'));
		$direction = trim($this->request->getPost('direction'));
		$projectIds = $this->request->getPost('project_ids');
		$model = PayrollEmployeeRowModel::factory();
		if (!$model->savePayrollProjectOrder($this->companyId, $periodId, $direction, $projectIds)) {
			$this->sendErrorResult($model->getLastError());
		}
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		$this->addSalaryLog(
			'payroll_project_order_save',
			'payroll_period',
			$periodId,
			$period ? $period['payroll_month'] : '',
			'调整当前月工资核算表项目顺序'
		);
		$this->sendSuccessResult(array('message' => '当前月项目顺序已保存'));
	}

	public function deletepayrollemployeeAction()
	{
		$this->checkFeature('payroll');
		$periodId = intval($this->request->getPost('id'));
		$employeeId = intval($this->request->getPost('employee_id'));
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll', 'payroll_month' => $period ? $period['payroll_month'] : date('Y-m')));
		if (!$this->request->isPost()) {
			$this->respondSalaryDeleteError('不支持的请求方式', $backUrl);
		}
		$model = PayrollPeriodModel::factory();
		if (!$model->deletePayrollEmployee($this->companyId, $periodId, $employeeId)) {
			$this->respondSalaryDeleteError($model->getLastError(), $backUrl);
		}
		$this->addSalaryLog('payroll_employee_delete', 'payroll_period', $periodId, $period ? $period['payroll_month'] : '', '从工资核算表删除员工，员工ID：' . $employeeId);
		$savedPeriod = $model->getCompanyPeriod($this->companyId, $periodId);
		$this->respondSalaryDeleteSuccess('员工已从当前工资核算表删除，不影响初始工资表和人事档案', $backUrl, array(
			'employee_id' => $employeeId,
			'employee_count' => $savedPeriod ? intval($savedPeriod['employee_count']) : 0,
			'period_earning_total' => $savedPeriod ? $savedPeriod['earning_total'] : '0.00',
			'period_deduction_total' => $savedPeriod ? $savedPeriod['deduction_total'] : '0.00',
			'period_net_total' => $savedPeriod ? $savedPeriod['net_total'] : '0.00',
		));
	}

	public function exportpayrollAction()
	{
		$this->checkFeature('payroll');
		$periodId = intval($this->request->get('id'));
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll', 'payroll_month' => $period ? $period['payroll_month'] : date('Y-m')));
		if (!$period) {
			Utils::showMsg('工资表不存在', $backUrl);
		}
		if (!$this->canExportSalaryData()) {
			Utils::showMsg('当前账号没有薪酬数据导出权限', $backUrl);
		}
		$projects = PayrollEmployeeRowModel::factory()->getPayrollProjectSnapshots($this->companyId, $periodId);
		$rows = PayrollEmployeeRowModel::factory()->getPayrollMatrix($this->companyId, $periodId);
		$this->addSalaryLog('payroll_export', 'payroll_period', $periodId, $period['payroll_month'], '导出工资核算表，人数' . count($rows));
		$this->outputPayrollExport($period, $projects, $rows);
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
		if ($result) {
			$this->addSalaryLog('payroll_import', 'payroll_period', intval($result['period_id']), $payrollMonth, '导入工资表，人数' . intval($result['employee_count']));
		}
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
		if ($result) {
			$this->addSalaryLog('payroll_import', 'payroll_period', intval($result['period_id']), $payrollMonth, '首次导入工资表并生成工资项目，人数' . intval($result['employee_count']));
		}
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
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		$this->addSalaryLog('payroll_submit_review', 'payroll_period', $periodId, $period ? $period['payroll_month'] : '', '提交工资表审核');
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
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		$result = SalaryPayrollAuditModel::factory()->reviewPeriod($this->companyId, $periodId, $reviewerId, $status, $opinion);
		if (!$result) {
			Utils::showMsg(SalaryPayrollAuditModel::factory()->getLastError(), $backUrl);
		}
		$statusLabel = $status == 'approved' ? '审核同意' : '审核驳回';
		$this->addSalaryLog('payroll_review', 'payroll_period', $periodId, $period ? $period['payroll_month'] : '', $statusLabel);
		Utils::showMsg('审核处理成功', $backUrl);
	}

	public function payslipconfirmAction()
	{
		$this->checkFeature('payroll');
		$this->checkFeature('payslip');
		$periodId = intval($this->request->get('id'));
		$from = trim($this->request->get('from'));
		$archiveId = intval($this->request->get('archive_id'));
		$backUrl = $from == 'archive' ? Helper::factory()->createUrl(array('p' => 'salary/archive')) : Helper::factory()->createUrl(array('p' => 'salary/payroll'));
		if ($periodId <= 0) {
			Utils::showMsg('请选择工资表', $backUrl);
		}
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		if (!$period) {
			Utils::showMsg('工资表不存在', $backUrl);
		}
		if ($from != 'archive') {
			$backUrl = Helper::factory()->createUrl(array('p' => 'salary/payroll', 'payroll_month' => $period['payroll_month']));
		}

		$archive = false;
		if ($from == 'archive') {
			if ($archiveId <= 0) {
				Utils::showMsg('请选择归档记录', Helper::factory()->createUrl(array('p' => 'salary/archive')));
			}
			$archive = PayrollArchiveModel::factory()->getArchive($this->companyId, $archiveId);
			if (!$archive || intval($archive['payroll_period_id']) != $periodId) {
				Utils::showMsg('归档记录不存在', Helper::factory()->createUrl(array('p' => 'salary/archive')));
			}
		} elseif (!PayrollPeriodModel::canPublishPayslip($period['status'])) {
			Utils::showMsg('请先归档工资表，再从归档记录发工资条', $backUrl);
		}

		$rows = PayrollEmployeeRowModel::factory()->getPayrollMatrix($this->companyId, $periodId);
		$publishedMap = PayrollSlipModel::factory()->getPublishedEmployeeIdMap($this->companyId, $periodId);
		$departments = array();
		foreach ($rows as $key => $row) {
			$employeeId = intval($row['employee_id']);
			$departmentName = trim($row['department_name']);
			if ($departmentName == '') {
				$departmentName = '未设置部门';
			}
			$rows[$key]['department_name'] = $departmentName;
			$rows[$key]['is_published'] = isset($publishedMap[$employeeId]) ? 1 : 0;
			$departments[$departmentName] = $departmentName;
		}
		ksort($departments);
		$period['status_name'] = PayrollPeriodModel::getStatusName($period['status']);

		$this->view->setVar('period', $period);
		$this->view->setVar('archive', $archive);
		$this->view->setVar('sourcePage', $from == 'archive' ? 'archive' : 'payroll');
		$this->view->setVar('archiveId', $archiveId);
		$this->view->setVar('rows', $rows);
		$this->view->setVar('departments', $departments);
		$this->view->setVar('publishedCount', count($publishedMap));
		$this->view->setVar('backUrl', $backUrl);
	}

	public function sendpayslipAction()
	{
		$this->checkFeature('payroll');
		$this->checkFeature('payslip');
		$from = trim($this->request->get('from'));
		$backUrl = $from == 'archive' ? Helper::factory()->createUrl(array('p' => 'salary/archive')) : Helper::factory()->createUrl(array('p' => 'salary/payroll'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}
		$periodId = intval($this->request->get('id'));
		if ($periodId <= 0) {
			Utils::showMsg('请选择工资表', $backUrl);
		}
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($this->companyId, $periodId);
		if ($period) {
			$backUrl = $from == 'archive' ? Helper::factory()->createUrl(array('p' => 'salary/archive')) : Helper::factory()->createUrl(array('p' => 'salary/payroll', 'payroll_month' => $period['payroll_month']));
		}
		$rows = PayrollEmployeeRowModel::factory()->getRowsByPeriod($this->companyId, $periodId);
		$employeeIds = $this->buildPayslipEmployeeScope($rows, $_POST);
		if ($employeeIds === false) {
			Utils::showMsg('请选择工资条发放范围', Helper::factory()->createUrl(array('p' => 'salary/payslipconfirm', 'id' => $periodId, 'from' => $from, 'archive_id' => intval($this->request->get('archive_id')))));
		}
		$allowArchivedRecord = false;
		if ($from == 'archive') {
			$archiveId = intval($this->request->get('archive_id'));
			$archive = PayrollArchiveModel::factory()->getArchive($this->companyId, $archiveId);
			if (!$archive || intval($archive['payroll_period_id']) != $periodId) {
				Utils::showMsg('归档记录不存在', Helper::factory()->createUrl(array('p' => 'salary/archive')));
			}
			$allowArchivedRecord = true;
		}
		$result = PayrollSlipModel::factory()->publishByPeriod($this->companyId, $periodId, $this->getOperatorId(), $employeeIds, $allowArchivedRecord);
		if (!$result) {
			Utils::showMsg(PayrollSlipModel::factory()->getLastError(), $backUrl);
		}
		$this->addSalaryLog('payslip_publish', 'payroll_period', $periodId, $period ? $period['payroll_month'] : '', '发放工资条，人数' . intval($result));
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
			Utils::showMsg('当前工资表状态不能归档', $backUrl);
		}
		$archiveId = PayrollArchiveModel::factory()->createFromPeriod($this->companyId, $periodId, $this->getOperatorId());
		if (!$archiveId) {
			Utils::showMsg(PayrollArchiveModel::factory()->getLastError(), $backUrl);
		}
		if (!PayrollPeriodModel::factory()->archivePeriod($this->companyId, $periodId, $this->getOperatorId())) {
			Utils::showMsg(PayrollPeriodModel::factory()->getLastError(), $backUrl);
		}
		$this->addSalaryLog('payroll_archive', 'salary_payroll_archive', $archiveId, $period['payroll_month'], '归档工资表');
		Utils::showMsg('工资表已归档', Helper::factory()->createUrl(array('p' => 'salary/archive')));
	}

	public function performanceAction()
	{
		$this->showFeature('performance_salary', '绩效工资核算');
	}

	public function archiveAction()
	{
		$this->checkFeature('payroll');
		$periods = PayrollArchiveModel::factory()->getCompanyArchives($this->companyId);
		$periods = $this->appendPayslipConfirmStats($periods, 'payroll_period_id');
		$this->view->setVar('periods', $periods);
		$this->view->setVar('canSendPayslip', $this->isSalaryFeatureEnabled('payslip'));
	}

	public function archiveviewAction()
	{
		$this->checkFeature('payroll');
		$archiveId = intval($this->request->get('id'));
		$model = PayrollArchiveModel::factory();
		$snapshot = $model->getArchiveSnapshot($this->companyId, $archiveId);
		if (!$snapshot) {
			Utils::showMsg($model->getLastError(), Helper::factory()->createUrl(array('p' => 'salary/archive')));
		}
		$displayProjects = EmployeeSalaryStructureModel::factory()->buildSalaryTableDisplayProjects($snapshot['projects']);
		$rows = $snapshot['rows'];
		foreach ($rows as $key => $row) {
			$rows[$key]['values']['summary_earning_total'] = sprintf('%.2f', floatval($row['earning_total']));
			$rows[$key]['values']['summary_deduction_total'] = sprintf('%.2f', floatval($row['deduction_total']));
			$rows[$key]['values']['summary_net_total'] = sprintf('%.2f', floatval($row['net_amount']));
		}
		$this->view->setVar('archiveItem', $snapshot['archive']);
		$this->view->setVar('archiveDisplayProjects', $displayProjects);
		$this->view->setVar('archiveRows', $rows);
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
		$restorePeriodId = PayrollArchiveModel::factory()->restoreToPayroll($this->companyId, $archiveId, $this->getOperatorId());
		if (!$restorePeriodId) {
			Utils::showMsg(PayrollArchiveModel::factory()->getLastError(), $backUrl);
		}
		$this->addSalaryLog('payroll_restore', 'salary_payroll_archive', $archiveId, $period['payroll_month'], '从归档记录恢复到工资表核算');
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
				'url' => $this->getHelper()->createUrl(array('p' => 'department/async', 'from' => 'salary')),
				'action' => '进入同步',
			),
			'wecom' => array(
				'name' => '企业微信',
				'status' => '已接入',
				'desc' => '已支持企业微信自建应用、免登录、通讯录同步和后台配置。',
				'url' => $this->getHelper()->createUrl(array('p' => 'wecom/index', 'company_id' => $this->companyId)),
				'action' => '去企业微信配置',
			),
			'feishu' => array(
				'name' => '飞书',
				'status' => '预留',
				'desc' => '飞书接口尚未接入，当前先保留企业平台选项和后续接入口。',
				'url' => '',
				'action' => '',
			),
			'manual' => array(
				'name' => '手工/Excel',
				'status' => '可用',
				'desc' => '适合暂未接入第三方通讯平台的企业，员工资料通过后台导入或维护。',
				'url' => '',
				'action' => '',
				'upload_url' => $this->getHelper()->createUrl(array('p' => 'department/uploadexcel', 'from' => 'salary')),
				'template_url' => $this->getHelper()->createUrl(array('p' => 'department/exportexceltpl')),
			),
		);

		$this->view->setVar('company', $company);
		$this->view->setVar('platform', $platform);
		$this->view->setVar('platformName', isset($platformOptions[$platform]) ? $platformOptions[$platform] : $platform);
		$this->view->setVar('syncItems', $syncItems);
		$this->view->setVar('userItems', $this->getCompanyUsers());
		$this->view->setVar('departmentItems', $this->getSalaryEmployeeDepartments());
	}

	public function employeesaveAction()
	{
		$this->checkModule();
		$this->requireSalaryEmployeeManager();
		if (!$this->request->isPost()) {
			$this->sendErrorResult('不支持的请求方式');
		}

		$employeeId = intval($this->request->getPost('employee_id'));
		$userInfo = CompanyUserModel::findFirst('company_id=' . intval($this->companyId) . ' and id=' . $employeeId);
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

		$departmentMap = $this->getSalaryEmployeeDepartmentMap();
		if ($departmentId > 0 && !isset($departmentMap[$departmentId])) {
			$this->sendErrorResult('所选部门不存在或不属于当前企业');
		}

		$userTable = CompanyUserModel::factory()->getSource();
		$employeeDepartmentModel = SalaryEmployeeDepartmentModel::factory();
		$mobileColumn = $employeeDepartmentModel->getEmployeeMobileColumn($userTable);
		$positionColumn = $employeeDepartmentModel->getEmployeePositionColumn($userTable);
		if ($mobileColumn != '' && $mobile != '') {
			$duplicate = $this->getDI()->get('db')->query(
				'select id from `' . $userTable . '` where company_id=' . intval($this->companyId) .
				' and id<>' . $employeeId .
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
		$this->addSalaryLog('salary_employee_save', 'company_user', $employeeId, '', '编辑员工信息：' . $name);
		$this->sendSuccessResult(array(
			'message' => '员工信息已保存',
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

	public function employeedeleteAction()
	{
		$this->checkModule();
		$this->requireSalaryEmployeeManager();
		if (!$this->request->isPost()) {
			$this->sendErrorResult('不支持的请求方式');
		}

		$employeeId = intval($this->request->getPost('employee_id'));
		$userInfo = CompanyUserModel::findFirst('company_id=' . intval($this->companyId) . ' and id=' . $employeeId);
		if (!$userInfo) {
			$this->sendErrorResult('员工不存在或已经删除');
		}
		if (intval($userInfo->is_admin) == 1) {
			$this->sendErrorResult('企业管理员不能删除');
		}
		if ($employeeId == $this->getSalaryRoleUserId()) {
			$this->sendErrorResult('不能删除当前登录员工');
		}
		if (intval($userInfo->addreport) == 1) {
			$this->sendErrorResult('该员工正在使用绩效考核，不能在薪酬页面直接删除');
		}

		$employeeName = $userInfo->name;
		$db = $this->getDI()->get('db');
		$db->begin();
		try {
			SalaryViewRoleModel::factory()->deleteBySql(
				'company_id=' . intval($this->companyId) .
				' and (user_id=' . $employeeId .
				' or (scope_type="employee" and target_id=' . $employeeId . '))'
			);
			$db->execute(
				'delete from `' . SalaryPayrollAuditModel::factory()->getRoleTable() . '`' .
				' where company_id=' . intval($this->companyId) . ' and reviewer_id=' . $employeeId
			);
			$platformIdentityTable = PlatformUserIdentityModel::factory()->getSource();
			if ($this->tableExists($platformIdentityTable)) {
				$db->execute(
					'delete from `' . $platformIdentityTable . '` where company_id=' .
					intval($this->companyId) . ' and company_user_id=' . $employeeId
				);
			}
			if (!$db->execute(
				'delete from `' . CompanyUserModel::factory()->getSource() . '` where company_id=' .
				intval($this->companyId) . ' and id=' . $employeeId
			)) {
				throw new \Exception('员工删除失败');
			}
			$db->commit();
		} catch (\Exception $exception) {
			$db->rollback();
			$this->sendErrorResult('员工删除失败，请稍后重试');
		}

		$this->addSalaryLog('salary_employee_delete', 'company_user', $employeeId, '', '删除员工：' . $employeeName . '；历史薪酬记录保留');
		$this->sendSuccessResult(array(
			'message' => '员工已删除，历史薪酬记录仍然保留',
			'employee_id' => $employeeId,
		));
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
		$this->addSalaryLog('salary_auth_audit_reviewer_save', 'salary_payroll_audit_role', 0, '', '保存工资表审核人，人数' . count($reviewerIds));
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
		$this->addSalaryLog('salary_auth_scope_save', 'salary_view_role', $userId, '', '保存薪酬查询授权');
		Utils::showMsg('操作成功', $backUrl);
	}

	protected function addSalaryLog($actionCode, $objectType = '', $objectId = 0, $payrollMonth = '', $summary = '')
	{
		SalaryOperationLogModel::factory()->addLog($this->companyId, $this->getOperatorId(), $actionCode, $objectType, $objectId, $payrollMonth, $summary);
	}

	protected function isSalaryAjaxRequest()
	{
		return $this->request->isAjax() || intval($this->request->getPost('salary_ajax')) == 1;
	}

	protected function respondSalaryDeleteError($message, $backUrl)
	{
		if ($this->isSalaryAjaxRequest()) {
			$this->sendErrorResult($message);
		}
		Utils::showMsg($message, $backUrl);
	}

	protected function respondSalaryDeleteSuccess($message, $backUrl, $data = array())
	{
		if ($this->isSalaryAjaxRequest()) {
			$data['message'] = $message;
			$this->sendSuccessResult($data);
		}
		Utils::showMsg($message, $backUrl);
	}

	protected function buildSalaryReportFilter($reportModel)
	{
		$payrollMonth = trim($this->request->get('payroll_month'));
		if ($payrollMonth == '') {
			$payrollMonth = $reportModel->getLatestPayrollMonth($this->companyId);
		}
		if ($payrollMonth != '' && !preg_match('/^\d{4}\-\d{2}$/', $payrollMonth)) {
			$payrollMonth = '';
		}
		return array(
			'payroll_month' => $payrollMonth,
			'department_name' => trim($this->request->get('department_name')),
			'keyword' => trim($this->request->get('keyword')),
		);
	}

	protected function getSalaryReportScope()
	{
		$user = Helper::factory()->getSession()->get('_user');
		if (!empty($user->is_admin)) {
			return array('all' => 1, 'can_export' => 1, 'employee_ids' => array(), 'department_names' => array());
		}
		$roleUserId = $this->getSalaryRoleUserId();
		if ($roleUserId <= 0) {
			return array('all' => 0, 'can_export' => 0, 'employee_ids' => array(), 'department_names' => array());
		}
		$departmentIds = SalaryViewRoleModel::factory()->getUserScope($this->companyId, $roleUserId, 'department');
		$employeeIds = SalaryViewRoleModel::factory()->getUserScope($this->companyId, $roleUserId, 'employee');
		return array(
			'all' => 0,
			'can_export' => SalaryViewRoleModel::factory()->getUserCanExport($this->companyId, $roleUserId),
			'employee_ids' => $employeeIds,
			'department_names' => $this->getSalaryDepartmentNames($departmentIds),
		);
	}

	protected function canExportSalaryData()
	{
		$scope = $this->getSalaryReportScope();
		return !empty($scope['can_export']);
	}

	protected function getSalaryRoleUserId()
	{
		$operatorId = $this->getOperatorId();
		$user = Helper::factory()->getSession()->get('_user');
		if ($operatorId > 0) {
			$item = CompanyUserModel::findFirst('company_id=' . intval($this->companyId) . ' and id=' . $operatorId);
			if ($item) {
				return $operatorId;
			}
		}
		$phone = empty($user->phone) ? '' : trim($user->phone);
		if ($phone == '') {
			return 0;
		}
		$userTable = CompanyUserModel::factory()->getSource();
		foreach (array('phone', 'mobile', 'jobnumber') as $column) {
			if ($this->tableHasColumn($userTable, $column)) {
				$item = CompanyUserModel::findFirst('company_id=' . intval($this->companyId) . ' and `' . $column . '`="' . addslashes($phone) . '"');
				if ($item) {
					return intval($item->id);
				}
			}
		}
		return 0;
	}

	protected function getSalaryDepartmentNames($departmentIds)
	{
		$names = array();
		if (empty($departmentIds)) {
			return $names;
		}
		$ids = array();
		foreach ($departmentIds as $departmentId) {
			$departmentId = intval($departmentId);
			if ($departmentId > 0) {
				$ids[] = $departmentId;
			}
		}
		if (empty($ids)) {
			return $names;
		}
		$items = DepartmentModel::find('company_id=' . intval($this->companyId) . ' and id in (' . implode(',', array_unique($ids)) . ')');
		foreach ($items as $item) {
			$names[] = $item->name;
		}
		return array_unique($names);
	}

	protected function tableHasColumn($tableName, $columnName)
	{
		$sql = 'SHOW COLUMNS FROM `' . $tableName . '` LIKE "' . addslashes($columnName) . '"';
		$columns = $this->getDI()->get('db')->query($sql)->fetchAll();
		return empty($columns) ? false : true;
	}

	protected function outputSalaryReportExport($filter, $rows)
	{
		$oldReporting = error_reporting();
		error_reporting($oldReporting & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
		$objPHPExcel = \Phalcon\Di\FactoryDefault::getDefault()->get('phpexcel');
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('薪酬报表');
		$headers = array('工资月份', '员工姓名', '手机号', '部门', '状态', '来源', '应发总额', '应扣总额', '实发总额');
		foreach ($headers as $index => $header) {
			$sheet->setCellValueByColumnAndRow($index, 1, $header);
			$sheet->getColumnDimensionByColumn($index)->setWidth($index >= 6 ? 14 : 16);
		}
		$rowNumber = 2;
		foreach ($rows as $row) {
			$sheet->setCellValueByColumnAndRow(0, $rowNumber, $row['payroll_month']);
			$sheet->setCellValueByColumnAndRow(1, $rowNumber, $row['employee_name']);
			$sheet->setCellValueByColumnAndRow(2, $rowNumber, $row['employee_no']);
			$sheet->setCellValueByColumnAndRow(3, $rowNumber, $row['department_name']);
			$sheet->setCellValueByColumnAndRow(4, $rowNumber, $row['status_name']);
			$sheet->setCellValueByColumnAndRow(5, $rowNumber, $row['source_label']);
			$sheet->setCellValueByColumnAndRow(6, $rowNumber, $row['earning_total']);
			$sheet->setCellValueByColumnAndRow(7, $rowNumber, $row['deduction_total']);
			$sheet->setCellValueByColumnAndRow(8, $rowNumber, $row['net_amount']);
			$rowNumber++;
		}
		$lastColumn = \PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);
		$lastRow = max(2, $rowNumber - 1);
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EAF1FF');
		$sheet->getStyle('A1:' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
		$sheet->freezePane('A2');

		$fileMonth = empty($filter['payroll_month']) ? date('Ym') : str_replace('-', '', $filter['payroll_month']);
		ob_clean();
		header("Content-Description: File Transfer");
		header("Content-type:application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition:attachment;filename=salary_report_" . $fileMonth . ".xls");
		header("Content-Transfer-Encoding: binary");
		header("Pragma: public");
		header("Cache-Control:max-age=0");
		$writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$writer->save('php://output');
		error_reporting($oldReporting);
		exit();
	}

	protected function outputPayrollExport($period, $projects, $rows)
	{
		$oldReporting = error_reporting();
		error_reporting($oldReporting & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
		$objPHPExcel = \Phalcon\Di\FactoryDefault::getDefault()->get('phpexcel');
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('工资核算表');
		$displayProjects = EmployeeSalaryStructureModel::factory()->buildSalaryTableDisplayProjects($projects);
		$headers = array('员工姓名', '手机号', '部门', '岗位');
		foreach ($displayProjects as $project) {
			$headers[] = $project['name'];
		}
		foreach ($headers as $index => $header) {
			$sheet->setCellValueByColumnAndRow($index, 1, $header);
			$sheet->getColumnDimensionByColumn($index)->setWidth($index < 4 ? 16 : 14);
		}
		$rowNumber = 2;
		foreach ($rows as $row) {
			$sheet->setCellValueByColumnAndRow(0, $rowNumber, $row['employee_name']);
			$sheet->setCellValueExplicitByColumnAndRow(1, $rowNumber, $row['employee_no'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValueByColumnAndRow(2, $rowNumber, $row['department_name']);
			$sheet->setCellValueByColumnAndRow(3, $rowNumber, $row['position_name']);
			$summaryValues = array(
				'summary_earning_total' => $row['earning_total'],
				'summary_deduction_total' => $row['deduction_total'],
				'summary_net_total' => $row['net_amount'],
			);
			foreach ($displayProjects as $projectIndex => $project) {
				$valueKey = $project['value_key'];
				if (!empty($project['is_summary_project'])) {
					$value = isset($summaryValues[$valueKey]) ? $summaryValues[$valueKey] : '0.00';
				} else {
					$value = isset($row['values'][intval($project['id'])]) ? $row['values'][intval($project['id'])] : (empty($project['is_text_project']) ? '0.00' : '');
				}
				$sheet->setCellValueByColumnAndRow(4 + $projectIndex, $rowNumber, $value);
			}
			$rowNumber++;
		}
		$lastColumn = \PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);
		$lastRow = max(2, $rowNumber - 1);
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EAF1FF');
		$sheet->getStyle('A1:' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
		$sheet->freezePane('A2');

		ob_clean();
		header('Content-Description: File Transfer');
		header('Content-type:application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition:attachment;filename=salary_payroll_' . str_replace('-', '', $period['payroll_month']) . '.xls');
		header('Content-Transfer-Encoding: binary');
		header('Pragma: public');
		header('Cache-Control:max-age=0');
		$writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$writer->save('php://output');
		error_reporting($oldReporting);
		exit();
	}

	protected function showFeature($featureCode, $featureName)
	{
		$this->checkFeature($featureCode);
		$this->view->setVar('featureCode', $featureCode);
		$this->view->setVar('featureName', $featureName);
		$this->view->pick('salary/feature');
	}

	protected function buildPayslipEmployeeScope($rows, $postData)
	{
		$rangeType = isset($postData['range_type']) ? trim($postData['range_type']) : 'all';
		$employeeIds = array();
		if ($rangeType == 'all') {
			foreach ($rows as $row) {
				$employeeId = intval($row['employee_id']);
				if ($employeeId > 0) {
					$employeeIds[$employeeId] = $employeeId;
				}
			}
			return array_values($employeeIds);
		}

		if ($rangeType == 'department') {
			$departments = isset($postData['departments']) && is_array($postData['departments']) ? $postData['departments'] : array();
			$departmentMap = array();
			foreach ($departments as $department) {
				$department = trim($department);
				if ($department != '') {
					$departmentMap[$department] = 1;
				}
			}
			if (empty($departmentMap)) {
				return false;
			}
			foreach ($rows as $row) {
				$departmentName = trim($row['department_name']);
				if ($departmentName == '') {
					$departmentName = '未设置部门';
				}
				$employeeId = intval($row['employee_id']);
				if ($employeeId > 0 && isset($departmentMap[$departmentName])) {
					$employeeIds[$employeeId] = $employeeId;
				}
			}
			return empty($employeeIds) ? false : array_values($employeeIds);
		}

		if ($rangeType == 'employee') {
			$selectedIds = isset($postData['employee_ids']) && is_array($postData['employee_ids']) ? $postData['employee_ids'] : array();
			$allowedMap = array();
			foreach ($rows as $row) {
				$employeeId = intval($row['employee_id']);
				if ($employeeId > 0) {
					$allowedMap[$employeeId] = 1;
				}
			}
			foreach ($selectedIds as $employeeId) {
				$employeeId = intval($employeeId);
				if ($employeeId > 0 && isset($allowedMap[$employeeId])) {
					$employeeIds[$employeeId] = $employeeId;
				}
			}
			return empty($employeeIds) ? false : array_values($employeeIds);
		}

		return false;
	}

	protected function appendPayslipConfirmStats($periods, $periodIdKey = 'id')
	{
		$periodIds = array();
		foreach ($periods as $period) {
			if (isset($period[$periodIdKey])) {
				$periodIds[] = intval($period[$periodIdKey]);
			}
		}
		$statsMap = PayrollSlipModel::factory()->getPeriodConfirmStats($this->companyId, $periodIds);
		foreach ($periods as $key => $period) {
			$periodId = isset($period[$periodIdKey]) ? intval($period[$periodIdKey]) : 0;
			$stats = isset($statsMap[$periodId]) ? $statsMap[$periodId] : array('published_count' => 0, 'viewed_count' => 0, 'confirmed_count' => 0);
			$periods[$key]['published_count'] = intval($stats['published_count']);
			$periods[$key]['viewed_count'] = intval($stats['viewed_count']);
			$periods[$key]['confirmed_count'] = intval($stats['confirmed_count']);
			$periods[$key]['unconfirmed_count'] = max(0, intval($stats['published_count']) - intval($stats['confirmed_count']));
		}
		return $periods;
	}

	protected function formatPayslipPeriods($periods)
	{
		foreach ($periods as $key => $period) {
			$periods[$key]['status_name'] = PayrollPeriodModel::getStatusName($period['status']);
			$sourceType = isset($period['source_type']) ? $period['source_type'] : 'system';
			$sourceName = isset($period['source_name']) ? $period['source_name'] : '';
			$periods[$key]['source_label'] = PayrollPeriodModel::getSourceName($sourceType, $sourceName);
			$periods[$key]['published_time'] = empty($period['published_at']) ? '-' : date('Y-m-d H:i', intval($period['published_at']));
			$periods[$key]['published_count'] = isset($period['published_count']) ? intval($period['published_count']) : 0;
			$periods[$key]['viewed_count'] = isset($period['viewed_count']) ? intval($period['viewed_count']) : 0;
			$periods[$key]['confirmed_count'] = isset($period['confirmed_count']) ? intval($period['confirmed_count']) : 0;
			$periods[$key]['unconfirmed_count'] = max(0, intval($periods[$key]['published_count']) - intval($periods[$key]['confirmed_count']));
			$periods[$key]['row_count'] = isset($period['row_count']) ? intval($period['row_count']) : intval($period['employee_count']);
			$periods[$key]['can_publish'] = PayrollPeriodModel::canPublishPayslip($period['status']) ? 1 : 0;
		}
		return $periods;
	}

	protected function formatPayslipDetailItems($items)
	{
		foreach ($items as $key => $item) {
			$items[$key]['published_time'] = empty($item['published_at']) ? '-' : date('Y-m-d H:i', intval($item['published_at']));
			$items[$key]['viewed_time'] = empty($item['viewed_at']) ? '-' : date('Y-m-d H:i', intval($item['viewed_at']));
			$items[$key]['confirmed_time'] = empty($item['confirmed_at']) ? '-' : date('Y-m-d H:i', intval($item['confirmed_at']));
			if (intval($item['confirmed_at']) > 0) {
				$items[$key]['confirm_status'] = '已确认';
			} elseif (intval($item['viewed_at']) > 0) {
				$items[$key]['confirm_status'] = '已查看未确认';
			} else {
				$items[$key]['confirm_status'] = '未查看';
			}
		}
		return $items;
	}

	protected function buildPayslipExportScope($items)
	{
		$departments = array();
		$employees = array();
		foreach ($items as $item) {
			$departmentName = trim($item['department_name']);
			if ($departmentName == '') {
				$departmentName = '未设置部门';
			}
			$departments[$departmentName] = $departmentName;
			$employeeId = intval($item['employee_id']);
			if ($employeeId > 0) {
				$employees[$employeeId] = array(
					'id' => $employeeId,
					'name' => $item['employee_name'],
					'mobile' => $item['employee_no'],
					'department_name' => $departmentName,
				);
			}
		}
		ksort($departments);
		return array('departments' => $departments, 'employees' => $employees);
	}

	protected function filterPayslipExportItems($items, $requestData)
	{
		$rangeType = isset($requestData['range_type']) ? trim($requestData['range_type']) : 'all';
		if ($rangeType == 'all') {
			return $items;
		}
		$return = array();
		if ($rangeType == 'department') {
			$departments = isset($requestData['departments']) && is_array($requestData['departments']) ? $requestData['departments'] : array();
			$departmentMap = array();
			foreach ($departments as $department) {
				$department = trim($department);
				if ($department != '') {
					$departmentMap[$department] = 1;
				}
			}
			if (empty($departmentMap)) {
				return false;
			}
			foreach ($items as $item) {
				$departmentName = trim($item['department_name']);
				if ($departmentName == '') {
					$departmentName = '未设置部门';
				}
				if (isset($departmentMap[$departmentName])) {
					$return[] = $item;
				}
			}
			return $return;
		}
		if ($rangeType == 'employee') {
			$employeeIds = isset($requestData['employee_ids']) && is_array($requestData['employee_ids']) ? $requestData['employee_ids'] : array();
			$employeeMap = array();
			foreach ($employeeIds as $employeeId) {
				$employeeId = intval($employeeId);
				if ($employeeId > 0) {
					$employeeMap[$employeeId] = 1;
				}
			}
			if (empty($employeeMap)) {
				return false;
			}
			foreach ($items as $item) {
				$employeeId = intval($item['employee_id']);
				if (isset($employeeMap[$employeeId])) {
					$return[] = $item;
				}
			}
			return $return;
		}
		return false;
	}

	protected function outputPayslipConfirmExport($period, $items)
	{
		$oldReporting = error_reporting();
		error_reporting($oldReporting & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
		$objPHPExcel = \Phalcon\Di\FactoryDefault::getDefault()->get('phpexcel');
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setTitle('工资条确认结果');
		$headers = array('工资月份', '员工姓名', '手机号', '部门', '应发总额', '应扣总额', '实发总额', '发放时间', '查看时间', '确认时间', '确认状态');
		foreach ($headers as $index => $header) {
			$sheet->setCellValueByColumnAndRow($index, 1, $header);
			$sheet->getColumnDimensionByColumn($index)->setWidth($index >= 7 ? 18 : 14);
		}
		$rowNumber = 2;
		foreach ($items as $item) {
			$sheet->setCellValueByColumnAndRow(0, $rowNumber, $period['payroll_month']);
			$sheet->setCellValueByColumnAndRow(1, $rowNumber, $item['employee_name']);
			$sheet->setCellValueByColumnAndRow(2, $rowNumber, $item['employee_no']);
			$sheet->setCellValueByColumnAndRow(3, $rowNumber, $item['department_name']);
			$sheet->setCellValueByColumnAndRow(4, $rowNumber, $item['earning_total']);
			$sheet->setCellValueByColumnAndRow(5, $rowNumber, $item['deduction_total']);
			$sheet->setCellValueByColumnAndRow(6, $rowNumber, $item['net_amount']);
			$sheet->setCellValueByColumnAndRow(7, $rowNumber, $item['published_time']);
			$sheet->setCellValueByColumnAndRow(8, $rowNumber, $item['viewed_time']);
			$sheet->setCellValueByColumnAndRow(9, $rowNumber, $item['confirmed_time']);
			$sheet->setCellValueByColumnAndRow(10, $rowNumber, $item['confirm_status']);
			$rowNumber++;
		}
		$lastColumn = \PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);
		$lastRow = max(2, $rowNumber - 1);
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EAF1FF');
		$sheet->getStyle('A1:' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
		$sheet->freezePane('A2');

		ob_clean();
		header("Content-Description: File Transfer");
		header("Content-type:application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition:attachment;filename=payslip_confirm_" . str_replace('-', '', $period['payroll_month']) . ".xls");
		header("Content-Transfer-Encoding: binary");
		header("Pragma: public");
		header("Cache-Control:max-age=0");
		$writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$writer->save('php://output');
		error_reporting($oldReporting);
		exit();
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
			array('code' => 'payroll', 'name' => '工资表核算', 'url' => 'salary/payroll', 'desc' => '生成并核算月份工资表，可导出Excel复核，完成后直接归档。'),
			array('code' => 'payslip', 'name' => '工资条发放', 'url' => 'salary/payslip', 'desc' => '查看工资条发放记录、员工查看确认进度和未确认记录。'),
			array('code' => 'archive', 'name' => '工资表归档记录', 'url' => 'salary/archive', 'desc' => '查看已归档工资表，可按归档数据发工资条或恢复重新核算。'),
			array('code' => 'report', 'name' => '薪酬统计报表', 'url' => 'salary/report', 'desc' => '按月份、部门、员工查询薪酬汇总和明细，按授权范围控制查看和导出。'),
			array('code' => 'log', 'name' => '薪酬操作日志', 'url' => 'salary/log', 'desc' => '记录工资项目、核算、审核、发放、归档、恢复和授权等关键操作。'),
			array('code' => 'commission', 'name' => '提成核算', 'url' => 'salary/commission', 'desc' => '设置提成项目与适用范围，后续按项目规则完成月度提成核算。'),
			array('code' => 'performance_salary', 'name' => '绩效工资核算', 'url' => 'salary/performance', 'desc' => '预留绩效结果联动工资核算入口。'),
		);
		foreach ($items as $key => $item) {
			if ($item['code'] == 'project' || $item['code'] == 'log') {
				$items[$key]['enabled'] = 1;
			} elseif ($item['code'] == 'archive' || $item['code'] == 'report') {
				$items[$key]['enabled'] = CompanyModuleAuthModel::isEnabled($authMap, 'salary', 'payroll') ? 1 : 0;
			} else {
				$items[$key]['enabled'] = CompanyModuleAuthModel::isEnabled($authMap, 'salary', $item['code']) ? 1 : 0;
			}
		}
		return $items;
	}

	protected function formatPayrollPeriods($archived = false)
	{
		$periods = PayrollPeriodModel::factory()->getCompanyPeriods($this->companyId, 36, $archived);
		$periods = $this->appendPayslipConfirmStats($periods);
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
		$userTable = CompanyUserModel::factory()->getSource();
		$employeeDepartmentModel = SalaryEmployeeDepartmentModel::factory();
		$departmentSql = $employeeDepartmentModel->getDepartmentSql($this->companyId, 'u');
		$mobileColumn = $employeeDepartmentModel->getEmployeeMobileColumn($userTable);
		$positionColumn = $employeeDepartmentModel->getEmployeePositionColumn($userTable);
		$mobileSelect = $mobileColumn ? 'u.`' . $mobileColumn . '`' : '""';
		$positionSelect = $positionColumn ? 'u.`' . $positionColumn . '`' : '""';
		$sql = 'select u.id,u.name,u.department_id,u.is_admin,u.is_leader,' .
			$mobileSelect . ' as mobile,' . $positionSelect . ' as position_name,' .
			$departmentSql['select'] . ' as departmentname ' .
			'from `' . $userTable . '` u ' .
			$departmentSql['join'] .
			'where u.company_id=' . intval($this->companyId) . ' order by departmentname asc,u.id asc';
		return $this->getDI()->get('db')->query($sql)->fetchAll();
	}

	protected function getSalaryEmployeeDepartments()
	{
		return array_values($this->getSalaryEmployeeDepartmentMap());
	}

	protected function getSalaryEmployeeDepartmentMap()
	{
		$return = array();
		$platform = SalaryEmployeeDepartmentModel::factory()->getCompanyPlatform($this->companyId);
		$items = DepartmentModel::find(array(
			'conditions' => 'company_id=' . intval($this->companyId),
			'order' => 'id asc',
		));
		foreach ($items as $item) {
			$value = $platform == 'dingding' ? intval($item->dingding_id) : intval($item->id);
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

	protected function requireSalaryEmployeeManager()
	{
		$user = Helper::factory()->getSession()->get('_user');
		if (empty($user->is_admin)) {
			$this->sendErrorResult('只有企业管理员可以编辑或删除员工');
		}
		return true;
	}

	protected function tableExists($tableName)
	{
		$item = $this->getDI()->get('db')->query(
			'select count(*) as num from information_schema.tables where table_schema=database()' .
			' and table_name="' . addslashes($tableName) . '"'
		)->fetch();
		return $item && intval($item['num']) > 0;
	}
}
