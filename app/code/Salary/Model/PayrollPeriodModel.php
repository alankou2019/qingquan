<?php
/**
 * Monthly payroll periods.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Salary\Model\EmployeeSalaryStructureModel;
use ScshuxCms\Salary\Model\SalaryProjectModel;

class PayrollPeriodModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("payroll_periods");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new PayrollPeriodModel();
		}
		return self::$_instance;
	}

	public function getCompanyPeriods($companyId, $limit = 36, $archived = false)
	{
		$db = $this->getDB();
		$periodTable = $this->getSource();
		$rowTable = $this->getTableName('payroll_employee_rows');
		$slipTable = $this->getTableName('payroll_slips');
		$sql = 'select p.*,count(distinct r.id) as row_count,count(distinct s.id) as slip_count,' .
			'ifnull(sum(case when s.status="published" then 1 else 0 end),0) as published_count ' .
			'from `' . $periodTable . '` p ' .
			'left join `' . $rowTable . '` r on p.id=r.payroll_period_id and p.company_id=r.company_id ' .
			'left join `' . $slipTable . '` s on p.id=s.payroll_period_id and p.company_id=s.company_id ' .
			'where p.company_id=' . intval($companyId) . ' and p.status ' . ($archived ? 'in ("archived","published") ' : 'not in ("archived","published") ') .
			'group by p.id order by p.payroll_month desc,p.id desc limit ' . intval($limit);
		return $db->query($sql)->fetchAll();
	}

	public function getCompanyPeriodByMonth($companyId, $payrollMonth, $includeArchived = false)
	{
		$where = 'company_id=' . intval($companyId) . ' and payroll_month="' . addslashes($payrollMonth) . '"';
		if (!$includeArchived) {
			$where .= ' and status not in ("archived","published")';
		}
		$sql = 'select * from `' . $this->getSource() . '` where ' . $where . ' order by id desc limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	public function getCompanyPeriod($companyId, $periodId)
	{
		$db = $this->getDB();
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and id=' . intval($periodId) . ' limit 1';
		return $db->query($sql)->fetch();
	}

	public function markPublished($companyId, $periodId, $operatorId)
	{
		$now = time();
		$sql = 'update `' . $this->getSource() . '` set published_by=' . intval($operatorId) .
			',published_at=' . $now . ',updated_at=' . $now .
			' where company_id=' . intval($companyId) . ' and id=' . intval($periodId);
		return $this->getDB()->execute($sql);
	}

	public function markArchived($companyId, $periodId, $operatorId)
	{
		$now = time();
		$sql = 'update `' . $this->getSource() . '` set status="archived",archived_by=' . intval($operatorId) .
			',archived_at=' . $now . ',updated_at=' . $now .
			' where company_id=' . intval($companyId) . ' and id=' . intval($periodId);
		return $this->getDB()->execute($sql);
	}

	public function archivePeriod($companyId, $periodId, $operatorId)
	{
		if (!$this->markArchived($companyId, $periodId, $operatorId)) {
			$this->_lastError = 'Archive status update failed';
			return false;
		}
		$period = $this->getCompanyPeriod($companyId, $periodId);
		if (!$period || $period['status'] != 'archived') {
			$this->_lastError = 'Archive status verify failed';
			return false;
		}
		return true;
	}

	public function markSubmitted($companyId, $periodId, $operatorId)
	{
		$now = time();
		$sql = 'update `' . $this->getSource() . '` set status="submitted",submitted_by=' . intval($operatorId) .
			',submitted_at=' . $now . ',rejected_by=NULL,rejected_at=NULL,rejected_reason="",updated_at=' . $now .
			' where company_id=' . intval($companyId) . ' and id=' . intval($periodId);
		return $this->getDB()->execute($sql);
	}

	public function markApproved($companyId, $periodId, $operatorId)
	{
		$now = time();
		$sql = 'update `' . $this->getSource() . '` set status="approved",approved_by=' . intval($operatorId) .
			',approved_at=' . $now . ',updated_at=' . $now .
			' where company_id=' . intval($companyId) . ' and id=' . intval($periodId);
		return $this->getDB()->execute($sql);
	}

	public function markRejected($companyId, $periodId, $operatorId, $reason = '')
	{
		$now = time();
		$sql = 'update `' . $this->getSource() . '` set status="rejected",rejected_by=' . intval($operatorId) .
			',rejected_at=' . $now . ',rejected_reason="' . addslashes($reason) . '",updated_at=' . $now .
			' where company_id=' . intval($companyId) . ' and id=' . intval($periodId);
		return $this->getDB()->execute($sql);
	}

	public function generateFromInitial($companyId, $payrollMonth, $operatorId)
	{
		$companyId = intval($companyId);
		if (!preg_match('/^\d{4}\-\d{2}$/', $payrollMonth)) {
			$this->_lastError = '工资月份不正确';
			return false;
		}
		$period = $this->getCompanyPeriodByMonth($companyId, $payrollMonth, true);
		if ($period && in_array($period['status'], array('archived', 'published'))) {
			$this->_lastError = '该月份工资表已经归档，如需重新核算请先在归档记录中恢复';
			return false;
		}
		$initial = EmployeeSalaryStructureModel::factory()->getInitialSalaryTable($companyId);
		$rows = array();
		foreach ($initial['employees'] as $employee) {
			$rows[] = array('employee' => $employee, 'values' => $employee['values']);
		}
		$projects = isset($initial['payroll_projects']) ? $initial['payroll_projects'] : SalaryProjectModel::factory()->getCompanyProjects($companyId);
		return $this->savePayrollMatrix($companyId, $payrollMonth, $projects, $rows, $operatorId, 'initial', '初始工资表生成');
	}

	public function savePayrollMatrixFromPost($companyId, $periodId, $postData, $operatorId)
	{
		$period = $this->getCompanyPeriod($companyId, $periodId);
		if (!$period) {
			$this->_lastError = '工资表不存在';
			return false;
		}
		if (!self::canEdit($period['status'])) {
			$this->_lastError = '当前状态不能编辑工资表';
			return false;
		}
		$amounts = isset($postData['amount']) && is_array($postData['amount']) ? $postData['amount'] : array();
		$projects = PayrollEmployeeRowModel::factory()->getPayrollProjectSnapshots($companyId, $periodId);
		$payrollRows = PayrollEmployeeRowModel::factory()->getPayrollMatrix($companyId, $periodId);
		$rows = array();
		foreach ($payrollRows as $payrollRow) {
			$employeeId = intval($payrollRow['employee_id']);
			$values = isset($amounts[$employeeId]) && is_array($amounts[$employeeId]) ? $amounts[$employeeId] : $payrollRow['values'];
			$rows[] = array(
				'employee' => array(
					'id' => $employeeId,
					'name' => $payrollRow['employee_name'],
					'mobile' => $payrollRow['employee_no'],
					'department_name' => $payrollRow['department_name'],
					'position_name' => $payrollRow['position_name'],
				),
				'values' => $values,
			);
		}
		return $this->savePayrollMatrix($companyId, $period['payroll_month'], $projects, $rows, $operatorId, $period['source_type'], $period['source_name'], intval($period['id']));
	}

	/**
	 * Keep editable payroll periods aligned with a changed formula project.
	 * Archived periods intentionally keep their historical project snapshot and values.
	 */
	public function recalculateOpenPeriodsForProject($companyId, $projectId, $operatorId = 0)
	{
		$companyId = intval($companyId);
		$projectId = intval($projectId);
		if ($companyId <= 0 || $projectId <= 0) {
			return 0;
		}

		$currentProject = false;
		foreach (SalaryProjectModel::factory()->getCompanyProjects($companyId) as $project) {
			if (intval($project['id']) == $projectId && $project['status'] == 'active' && intval($project['deleted_at']) == 0) {
				$currentProject = $project;
				break;
			}
		}
		if (!$currentProject || !SalaryProjectModel::isFormulaProject($currentProject)) {
			return 0;
		}

		$affectedCount = 0;
		$rowModel = PayrollEmployeeRowModel::factory();
		$periods = $this->getCompanyPeriods($companyId, 500, false);
		foreach ($periods as $period) {
			$periodId = intval($period['id']);
			$projects = $rowModel->getPayrollProjectSnapshots($companyId, $periodId);
			$matched = false;
			foreach ($projects as $index => $project) {
				if (intval($project['id']) != $projectId) {
					continue;
				}
				$sortOrder = isset($project['sort_order']) ? $project['sort_order'] : 0;
				$projects[$index] = $currentProject;
				$projects[$index]['sort_order'] = $sortOrder;
				$matched = true;
				break;
			}
			if (!$matched) {
				continue;
			}

			$rows = array();
			foreach ($rowModel->getPayrollMatrix($companyId, $periodId) as $payrollRow) {
				$rows[] = array(
					'employee' => array(
						'id' => intval($payrollRow['employee_id']),
						'name' => $payrollRow['employee_name'],
						'mobile' => $payrollRow['employee_no'],
						'department_name' => $payrollRow['department_name'],
						'position_name' => $payrollRow['position_name'],
					),
					'values' => $payrollRow['values'],
				);
			}
			if (empty($rows) || !$this->savePayrollMatrix($companyId, $period['payroll_month'], $projects, $rows, $operatorId, $period['source_type'], $period['source_name'], $periodId)) {
				$this->_lastError = $this->_lastError ? $this->_lastError : '重新核算工资表失败';
				return false;
			}
			$affectedCount++;
		}
		return $affectedCount;
	}

	public function deletePayrollEmployee($companyId, $periodId, $employeeId)
	{
		$companyId = intval($companyId);
		$periodId = intval($periodId);
		$employeeId = intval($employeeId);
		$period = $this->getCompanyPeriod($companyId, $periodId);
		if (!$period) {
			$this->_lastError = '工资表不存在';
			return false;
		}
		if (!self::canEdit($period['status'])) {
			$this->_lastError = '当前状态不能删除员工';
			return false;
		}
		$rowTable = $this->getTableName('payroll_employee_rows');
		$valueTable = $this->getTableName('payroll_item_values');
		$sql = 'select id from `' . $rowTable . '` where company_id=' . $companyId . ' and payroll_period_id=' . $periodId .
			' and employee_id=' . $employeeId . ' limit 1';
		$row = $this->getDB()->query($sql)->fetch();
		if (!$row) {
			$this->_lastError = '工资表中没有该员工';
			return false;
		}
		$db = $this->getDB();
		$db->begin();
		try {
			$rowId = intval($row['id']);
			$db->execute('delete from `' . $valueTable . '` where company_id=' . $companyId . ' and payroll_period_id=' . $periodId . ' and payroll_employee_row_id=' . $rowId);
			$db->execute('delete from `' . $rowTable . '` where company_id=' . $companyId . ' and payroll_period_id=' . $periodId . ' and id=' . $rowId);
			$this->refreshPeriodTotals($companyId, $periodId, $db);
			$db->commit();
			return true;
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = '删除工资表员工失败：' . $e->getMessage();
			return false;
		}
	}

	protected function refreshPeriodTotals($companyId, $periodId, $db = null)
	{
		if (!$db) {
			$db = $this->getDB();
		}
		$rowTable = $this->getTableName('payroll_employee_rows');
		$sql = 'select count(*) as employee_count,ifnull(sum(earning_total),0) as earning_total,ifnull(sum(deduction_total),0) as deduction_total,ifnull(sum(net_amount),0) as net_total ' .
			'from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and payroll_period_id=' . intval($periodId);
		$totals = $db->query($sql)->fetch();
		$now = time();
		$sql = 'update `' . $this->getSource() . '` set employee_count=' . intval($totals['employee_count']) .
			',earning_total=' . $this->formatMoney($totals['earning_total']) . ',deduction_total=' . $this->formatMoney($totals['deduction_total']) .
			',net_total=' . $this->formatMoney($totals['net_total']) . ',updated_at=' . $now .
			' where company_id=' . intval($companyId) . ' and id=' . intval($periodId);
		return $db->execute($sql);
	}

	public function savePayrollMatrix($companyId, $payrollMonth, $projects, $rows, $operatorId, $sourceType = 'system', $sourceName = '', $periodId = 0)
	{
		$db = $this->getDB();
		$periodTable = $this->getSource();
		$rowTable = $this->getTableName('payroll_employee_rows');
		$valueTable = $this->getTableName('payroll_item_values');
		$auditTable = $this->getTableName('salary_payroll_audit_record');
		$now = time();
		$projectMap = array();
		foreach ($projects as $project) {
			if ($project['status'] == 'active' && intval($project['deleted_at']) == 0) {
				$projectMap[intval($project['id'])] = $project;
			}
		}
		if (empty($projectMap)) {
			$this->_lastError = '请先设置工资项目';
			return false;
		}
		$projectSnapshotSql = '';
		$projectSnapshotColumn = '';
		$projectSnapshotValue = '';
		$hasTextValueColumn = $this->hasTextValueColumn($valueTable);
		if ($this->hasProjectSnapshotColumn($periodTable)) {
			$serializedProjects = addslashes(serialize(array_values($projectMap)));
			$projectSnapshotSql = 'project_snapshot="' . $serializedProjects . '",';
			$projectSnapshotColumn = ',`project_snapshot`';
			$projectSnapshotValue = ',"' . $serializedProjects . '"';
		}
		$preparedRows = array();
		$employeeCount = 0;
		$earningTotal = 0;
		$deductionTotal = 0;
		$netTotal = 0;
		foreach ($rows as $row) {
			$employee = $row['employee'];
			$values = isset($row['values']) && is_array($row['values']) ? $row['values'] : array();
			$nameAmountMap = array();
			$itemValues = array();
			foreach ($projectMap as $projectId => $project) {
				if (SalaryProjectModel::isFormulaProject($project)) {
					continue;
				}
				$amount = isset($values[$projectId]) ? $values[$projectId] : 0;
				if (SalaryProjectModel::isTextProject($project)) {
					$itemValues[$projectId] = array('amount' => '0.00', 'text' => trim((string)$amount));
					continue;
				}
				$amount = $this->formatMoney($amount);
				$nameAmountMap[$project['name']] = $amount;
				$itemValues[$projectId] = array('amount' => $amount, 'text' => '');
			}
			foreach ($projectMap as $projectId => $project) {
				if (!SalaryProjectModel::isFormulaProject($project)) {
					continue;
				}
				$amount = EmployeeSalaryStructureModel::factory()->calculateFormulaAmount($project['formula_text'], $nameAmountMap);
				$nameAmountMap[$project['name']] = $amount;
				$itemValues[$projectId] = array('amount' => $amount, 'text' => '');
			}
			$rowEarning = 0;
			$rowDeduction = 0;
			foreach ($projectMap as $projectId => $project) {
				$amount = isset($itemValues[$projectId]['amount']) ? floatval($itemValues[$projectId]['amount']) : 0;
				if (intval($project['include_earning'])) {
					$rowEarning += $amount;
				}
				if (intval($project['include_deduction'])) {
					$rowDeduction += $amount;
				}
			}
			$rowNet = $rowEarning - $rowDeduction;
			$preparedRows[] = array('employee' => $employee, 'items' => $itemValues, 'earning_total' => $rowEarning, 'deduction_total' => $rowDeduction, 'net_amount' => $rowNet);
			$employeeCount++;
			$earningTotal += $rowEarning;
			$deductionTotal += $rowDeduction;
			$netTotal += $rowNet;
		}
		if (empty($preparedRows)) {
			$this->_lastError = '没有可保存的工资表数据';
			return false;
		}
		$db->begin();
		try {
			if ($periodId <= 0) {
				$existing = $this->getCompanyPeriodByMonth($companyId, $payrollMonth, true);
				if ($existing) {
					$periodId = intval($existing['id']);
				}
			}
			if ($periodId > 0) {
				$db->execute('delete from `' . $valueTable . '` where company_id=' . intval($companyId) . ' and payroll_period_id=' . $periodId);
				$db->execute('delete from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and payroll_period_id=' . $periodId);
				$db->execute('delete from `' . $auditTable . '` where company_id=' . intval($companyId) . ' and payroll_period_id=' . $periodId);
				$sql = 'update `' . $periodTable . '` set status="calculated",source_type="' . addslashes($sourceType) . '",source_name="' . addslashes($sourceName) . '",' . $projectSnapshotSql .
					'employee_count=' . intval($employeeCount) . ',earning_total=' . $this->formatMoney($earningTotal) . ',deduction_total=' . $this->formatMoney($deductionTotal) . ',net_total=' . $this->formatMoney($netTotal) . ',' .
					'generated_by=' . intval($operatorId) . ',submitted_by=NULL,approved_by=NULL,rejected_by=NULL,published_by=NULL,archived_by=NULL,' .
					'generated_at=' . $now . ',calculated_at=' . $now . ',submitted_at=NULL,approved_at=NULL,rejected_at=NULL,published_at=NULL,archived_at=NULL,rejected_reason="",updated_at=' . $now .
					' where id=' . $periodId . ' and company_id=' . intval($companyId);
				$db->execute($sql);
			} else {
				$sql = 'insert into `' . $periodTable . '` (`company_id`,`payroll_month`,`source_type`,`source_name`' . $projectSnapshotColumn . ',`status`,`employee_count`,`earning_total`,`deduction_total`,`net_total`,`generated_by`,`generated_at`,`calculated_at`,`created_at`,`updated_at`) values ' .
					'(' . intval($companyId) . ',"' . addslashes($payrollMonth) . '","' . addslashes($sourceType) . '","' . addslashes($sourceName) . '"' . $projectSnapshotValue . ',"calculated",' . intval($employeeCount) . ',' . $this->formatMoney($earningTotal) . ',' . $this->formatMoney($deductionTotal) . ',' . $this->formatMoney($netTotal) . ',' . intval($operatorId) . ',' . $now . ',' . $now . ',' . $now . ',' . $now . ')';
				$db->execute($sql);
				$periodId = intval($db->lastInsertId());
			}
			$valueColumns = $hasTextValueColumn ?
				'(`company_id`,`payroll_period_id`,`payroll_employee_row_id`,`employee_id`,`salary_project_id`,`project_name`,`source_type`,`direction`,`calculation_mode`,`linked_module`,`include_earning`,`include_deduction`,`include_net`,`initial_amount`,`final_amount`,`text_value`,`entry_source`,`remark`,`created_at`,`updated_at`)' :
				'(`company_id`,`payroll_period_id`,`payroll_employee_row_id`,`employee_id`,`salary_project_id`,`project_name`,`source_type`,`direction`,`calculation_mode`,`linked_module`,`include_earning`,`include_deduction`,`include_net`,`initial_amount`,`final_amount`,`entry_source`,`remark`,`created_at`,`updated_at`)';
			$rowTuples = array();
			foreach ($preparedRows as $row) {
				$employee = $row['employee'];
				$rowTuples[] = '(' . intval($companyId) . ',' . $periodId . ',' . intval($employee['id']) . ',"' . addslashes($employee['name']) . '","' . addslashes(isset($employee['mobile']) ? $employee['mobile'] : '') . '","' . addslashes(isset($employee['department_name']) ? $employee['department_name'] : '') . '","' . addslashes(isset($employee['position_name']) ? $employee['position_name'] : '') . '",NULL,' . $this->formatMoney($row['earning_total']) . ',' . $this->formatMoney($row['deduction_total']) . ',' . $this->formatMoney($row['net_amount']) . ',"",' . $now . ',' . $now . ')';
			}
			$db->execute('insert into `' . $rowTable . '` (`company_id`,`payroll_period_id`,`employee_id`,`employee_name`,`employee_no`,`department_name`,`position_name`,`salary_structure_id`,`earning_total`,`deduction_total`,`net_amount`,`remark`,`created_at`,`updated_at`) values ' . implode(',', $rowTuples));
			$savedRows = $db->query('select id,employee_id from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and payroll_period_id=' . $periodId)->fetchAll();
			$rowIdMap = array();
			foreach ($savedRows as $savedRow) {
				$rowIdMap[intval($savedRow['employee_id'])] = intval($savedRow['id']);
			}
			$valueTuples = array();
			foreach ($preparedRows as $row) {
				$employee = $row['employee'];
				$employeeId = intval($employee['id']);
				if (!isset($rowIdMap[$employeeId])) {
					throw new \Exception('Payroll employee row was not saved');
				}
				$rowId = $rowIdMap[$employeeId];
				foreach ($projectMap as $projectId => $project) {
					$value = isset($row['items'][$projectId]) ? $row['items'][$projectId] : array('amount' => '0.00', 'text' => '');
					$amount = isset($value['amount']) ? $value['amount'] : '0.00';
					$textValue = isset($value['text']) ? $value['text'] : '';
					$valueTuple = '(' . intval($companyId) . ',' . $periodId . ',' . $rowId . ',' . $employeeId . ',' . intval($projectId) . ',"' . addslashes($project['name']) . '","' . addslashes($project['source_type']) . '","' . addslashes($project['direction']) . '","' . addslashes($project['calculation_mode']) . '","' . addslashes($project['linked_module']) . '",' . intval($project['include_earning']) . ',' . intval($project['include_deduction']) . ',' . intval($project['include_net']) . ',' . $this->formatMoney($amount) . ',' . $this->formatMoney($amount);
					if ($hasTextValueColumn) {
						$valueTuple .= ',"' . addslashes(trim((string)$textValue)) . '"';
					}
					$valueTuples[] = $valueTuple . ',"manual","",' . $now . ',' . $now . ')';
					if (count($valueTuples) >= 200) {
						$db->execute('insert into `' . $valueTable . '` ' . $valueColumns . ' values ' . implode(',', $valueTuples));
						$valueTuples = array();
					}
				}
			}
			if (!empty($valueTuples)) {
				$db->execute('insert into `' . $valueTable . '` ' . $valueColumns . ' values ' . implode(',', $valueTuples));
			}
			$db->commit();
			return $periodId;
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = '保存工资表失败：' . $e->getMessage();
			return false;
		}
	}

	protected function formatMoney($value)
	{
		$value = str_replace(array(',', '，', '￥', '元', ' '), '', (string)$value);
		if (!is_numeric($value)) {
			$value = 0;
		}
		return sprintf('%.2f', round(floatval($value), 2));
	}

	protected function hasTextValueColumn($table)
	{
		$item = $this->getDB()->query("SHOW COLUMNS FROM `" . $table . "` LIKE 'text_value'")->fetch();
		return $item ? true : false;
	}

	protected function hasProjectSnapshotColumn($table)
	{
		$item = $this->getDB()->query("SHOW COLUMNS FROM `" . $table . "` LIKE 'project_snapshot'")->fetch();
		return $item ? true : false;
	}

	public static function getStatusName($status)
	{
		$map = array(
			'draft' => '草稿',
			'calculated' => '已核算',
			'submitted' => '审核中',
			'approved' => '审核通过',
			'rejected' => '已驳回',
			'archived' => '已归档',
			'published' => '已发工资条/已归档',
		);
		return isset($map[$status]) ? $map[$status] : $status;
	}

	public static function getSourceName($sourceType, $sourceName = '')
	{
		if ($sourceName !== '') {
			return $sourceName;
		}
		$map = array(
			'system' => '系统生成',
			'excel' => 'Excel导入',
			'manual' => '手工维护',
		);
		return isset($map[$sourceType]) ? $map[$sourceType] : '月工资表';
	}

	public static function canPublishPayslip($status)
	{
		return in_array($status, array('archived', 'published'));
	}

	public static function canArchive($status)
	{
		return self::canEdit($status) || in_array($status, array('submitted', 'approved'));
	}

	public static function canEdit($status)
	{
		return in_array($status, array('draft', 'calculated', 'rejected'));
	}

	public static function canSubmitAudit($status)
	{
		return in_array($status, array('draft', 'calculated', 'rejected'));
	}
}
