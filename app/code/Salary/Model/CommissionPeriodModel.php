<?php
/**
 * Monthly commission calculation periods.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class CommissionPeriodModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName('salary_commission_periods');
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new CommissionPeriodModel();
		}
		return self::$_instance;
	}

	public function getCompanyPeriodByMonth($companyId, $commissionMonth, $includeDeleted = false)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and commission_month="' . addslashes($commissionMonth) . '"';
		if (!$includeDeleted) {
			$sql .= ' and status!="deleted"';
		}
		$sql .= ' limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	public function getEditableCompanyPeriodByMonth($companyId, $commissionMonth)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and commission_month="' . addslashes($commissionMonth) . '" and status in ("draft","calculated") limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	public function getCompanyPeriod($companyId, $periodId)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and id=' . intval($periodId) . ' limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	public function generateFromEmployees($companyId, $commissionMonth, $operatorId)
	{
		if (!preg_match('/^\d{4}\-\d{2}$/', $commissionMonth)) {
			$this->_lastError = '提成月份不正确';
			return false;
		}
		$existing = $this->getEditableCompanyPeriodByMonth($companyId, $commissionMonth);
		if ($existing) {
			$this->_lastError = '该月份的提成核算表已经存在';
			return false;
		}
		if (CommissionArchiveModel::factory()->getActiveArchiveByMonth($companyId, $commissionMonth)) {
			$this->_lastError = '该月份提成表已归档，请到归档记录查看或恢复后再核算';
			return false;
		}
		$employees = EmployeeSalaryStructureModel::factory()->getCompanyEmployees($companyId);
		$projects = CommissionProjectModel::factory()->getCompanyProjects($companyId);
		$rows = array();
		foreach ($employees as $employee) {
			$items = $this->matchEmployeeProjects($employee, $projects);
			$rows[] = array('employee' => $employee, 'items' => $items, 'values' => array(), 'remark' => '');
		}
		return $this->saveCommissionMatrix($companyId, $commissionMonth, $rows, $operatorId, 0);
	}

	public function saveCommissionMatrixFromPost($companyId, $periodId, $postData, $operatorId)
	{
		$period = $this->getCompanyPeriod($companyId, $periodId);
		if (!$period) {
			$this->_lastError = '提成核算表不存在';
			return false;
		}
		if (!in_array($period['status'], array('draft', 'calculated'))) {
			$this->_lastError = '当前状态不能编辑提成核算表';
			return false;
		}
		$projects = CommissionProjectModel::factory()->getCompanyProjects($companyId);
		$projectMap = array();
		foreach ($projects as $project) {
			if ($project['status'] == 'active' && intval($project['deleted_at']) == 0) {
				$projectMap[intval($project['id'])] = $project;
			}
		}
		$currentRows = $this->getCommissionMatrix($companyId, $periodId);
		$inputs = isset($postData['input']) && is_array($postData['input']) ? $postData['input'] : array();
		$remarks = isset($postData['remark']) && is_array($postData['remark']) ? $postData['remark'] : array();
		$rows = array();
		foreach ($currentRows as $currentRow) {
			$employeeId = intval($currentRow['employee_id']);
			$employee = $this->buildEmployeeFromCommissionRow($currentRow);
			$items = array();
			foreach ($currentRow['items'] as $currentItem) {
				$projectId = intval($currentItem['commission_project_id']);
				if (isset($projectMap[$projectId])) {
					$items[] = $projectMap[$projectId];
				}
			}
			$rows[] = array(
				'employee' => $employee,
				'items' => $items,
				'values' => isset($inputs[$employeeId]) && is_array($inputs[$employeeId]) ? $inputs[$employeeId] : array(),
				'remark' => isset($remarks[$employeeId]) ? trim($remarks[$employeeId]) : '',
			);
		}
		return $this->saveCommissionMatrix($companyId, $period['commission_month'], $rows, $operatorId, intval($period['id']));
	}

	public function saveEmployeeProjectSelection($companyId, $periodId, $employeeId, $projectIds, $operatorId)
	{
		$period = $this->getCompanyPeriod($companyId, $periodId);
		if (!$period || !in_array($period['status'], array('draft', 'calculated'))) {
			$this->_lastError = '当前提成核算表不能修改员工项目';
			return false;
		}
		$employeeId = intval($employeeId);
		$selected = array();
		if (is_array($projectIds)) {
			foreach ($projectIds as $projectId) {
				$projectId = intval($projectId);
				if ($projectId > 0) {
					$selected[$projectId] = 1;
				}
			}
		}
		$projects = CommissionProjectModel::factory()->getCompanyProjects($companyId);
		$projectMap = array();
		foreach ($projects as $project) {
			if ($project['status'] == 'active' && intval($project['deleted_at']) == 0) {
				$projectMap[intval($project['id'])] = $project;
			}
		}
		$currentRows = $this->getCommissionMatrix($companyId, $periodId);
		$rows = array();
		$found = false;
		foreach ($currentRows as $currentRow) {
			$currentEmployeeId = intval($currentRow['employee_id']);
			$currentValues = array();
			foreach ($currentRow['items'] as $currentItem) {
				$currentValues[intval($currentItem['commission_project_id'])] = $currentItem['input_value'];
			}
			$items = array();
			if ($currentEmployeeId == $employeeId) {
				$found = true;
				foreach ($selected as $projectId => $unused) {
					if (isset($projectMap[$projectId])) {
						$items[] = $projectMap[$projectId];
					}
				}
			} else {
				foreach ($currentRow['items'] as $currentItem) {
					$projectId = intval($currentItem['commission_project_id']);
					if (isset($projectMap[$projectId])) {
						$items[] = $projectMap[$projectId];
					}
				}
			}
			$rows[] = array(
				'employee' => $this->buildEmployeeFromCommissionRow($currentRow),
				'items' => $items,
				'values' => $currentValues,
				'remark' => $currentRow['remark'],
			);
		}
		if (!$found) {
			$this->_lastError = '员工不在当前提成核算表中';
			return false;
		}
		return $this->saveCommissionMatrix($companyId, $period['commission_month'], $rows, $operatorId, intval($period['id']));
	}

	public function deleteEmployeeRow($companyId, $periodId, $employeeId)
	{
		$period = $this->getCompanyPeriod($companyId, $periodId);
		if (!$period || !in_array($period['status'], array('draft', 'calculated'))) {
			$this->_lastError = '当前提成核算表不能删除员工';
			return false;
		}
		$rowTable = $this->getTableName('salary_commission_rows');
		$valueTable = $this->getTableName('salary_commission_item_values');
		$employeeId = intval($employeeId);
		$row = $this->getDB()->query('select id from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($periodId) . ' and employee_id=' . $employeeId . ' limit 1')->fetch();
		if (!$row) {
			$this->_lastError = '员工不在当前提成核算表中';
			return false;
		}
		$db = $this->getDB();
		$db->begin();
		try {
			$db->execute('delete from `' . $valueTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($periodId) . ' and commission_row_id=' . intval($row['id']));
			$db->execute('delete from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($periodId) . ' and id=' . intval($row['id']));
			$db->commit();
			$this->refreshPeriodSummary($companyId, $periodId);
			return true;
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = '删除提成核算员工失败：' . $e->getMessage();
			return false;
		}
	}

	public function saveCommissionMatrix($companyId, $commissionMonth, $rows, $operatorId, $periodId = 0)
	{
		$db = $this->getDB();
		$periodTable = $this->getSource();
		$rowTable = $this->getTableName('salary_commission_rows');
		$valueTable = $this->getTableName('salary_commission_item_values');
		$now = time();
		$prepared = array();
		$employeeCount = 0;
		$matchedCount = 0;
		$totalAmount = 0;
		$projectModel = CommissionProjectModel::factory();

		foreach ($rows as $row) {
			$employee = $row['employee'];
			$items = isset($row['items']) && is_array($row['items']) ? $row['items'] : array();
			$values = isset($row['values']) && is_array($row['values']) ? $row['values'] : array();
			$itemRows = array();
			$rowTotal = 0;
			foreach ($items as $project) {
				$projectId = intval($project['id']);
				$inputValue = isset($values[$projectId]) ? $values[$projectId] : 0;
				$amount = $projectModel->calculateAmount($project, $inputValue);
				$itemRows[] = array(
					'project' => $project,
					'input_value' => $this->formatMoney($inputValue),
					'commission_amount' => $amount,
					'rule_snapshot' => $projectModel->buildRuleSnapshot($project),
				);
				$rowTotal += floatval($amount);
			}
			$employeeCount++;
			if (!empty($itemRows)) {
				$matchedCount++;
			}
			$totalAmount += $rowTotal;
			$prepared[] = array(
				'employee' => $employee,
				'items' => $itemRows,
				'total_amount' => $this->formatMoney($rowTotal),
				'remark' => isset($row['remark']) ? trim($row['remark']) : '',
			);
		}
		if (empty($prepared)) {
			$this->_lastError = '没有可生成的员工提成数据';
			return false;
		}

		$db->begin();
		try {
			if ($periodId <= 0) {
				$existing = $this->getCompanyPeriodByMonth($companyId, $commissionMonth, true);
				if ($existing) {
					$periodId = intval($existing['id']);
				}
			}
			if ($periodId > 0) {
				$db->execute('delete from `' . $valueTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($periodId));
				$db->execute('delete from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($periodId));
				$sql = 'update `' . $periodTable . '` set status="calculated",employee_count=' . intval($employeeCount) .
					',matched_count=' . intval($matchedCount) . ',total_amount=' . $this->formatMoney($totalAmount) .
					',generated_by=' . intval($operatorId) . ',generated_at=' . $now . ',calculated_at=' . $now . ',updated_at=' . $now .
					' where id=' . intval($periodId) . ' and company_id=' . intval($companyId);
				$db->execute($sql);
			} else {
				$sql = 'insert into `' . $periodTable . '` (`company_id`,`commission_month`,`status`,`employee_count`,`matched_count`,`total_amount`,`generated_by`,`generated_at`,`calculated_at`,`created_at`,`updated_at`) values ' .
					'(' . intval($companyId) . ',"' . addslashes($commissionMonth) . '","calculated",' . intval($employeeCount) . ',' . intval($matchedCount) . ',' . $this->formatMoney($totalAmount) . ',' . intval($operatorId) . ',' . $now . ',' . $now . ',' . $now . ',' . $now . ')';
				$db->execute($sql);
				$periodId = intval($db->lastInsertId());
			}
			foreach ($prepared as $row) {
				$employee = $row['employee'];
				$rowSql = 'insert into `' . $rowTable . '` (`company_id`,`commission_period_id`,`employee_id`,`employee_name`,`employee_no`,`department_id`,`department_name`,`position_name`,`matched_project_count`,`total_amount`,`remark`,`created_at`,`updated_at`) values ' .
					'(' . intval($companyId) . ',' . intval($periodId) . ',' . intval($employee['id']) . ',"' . addslashes($employee['name']) . '","' . addslashes(isset($employee['mobile']) ? $employee['mobile'] : '') . '","' . addslashes(isset($employee['department_id']) ? $employee['department_id'] : '') . '","' . addslashes(isset($employee['department_name']) ? $employee['department_name'] : '') . '","' . addslashes(isset($employee['position_name']) ? $employee['position_name'] : '') . '",' . count($row['items']) . ',' . $this->formatMoney($row['total_amount']) . ',"' . addslashes($row['remark']) . '",' . $now . ',' . $now . ')';
				$db->execute($rowSql);
				$rowId = intval($db->lastInsertId());
				foreach ($row['items'] as $item) {
					$project = $item['project'];
					$valueSql = 'insert into `' . $valueTable . '` (`company_id`,`commission_period_id`,`commission_row_id`,`employee_id`,`commission_project_id`,`project_name`,`metric_type`,`metric_name`,`commission_mode`,`scope_type`,`scope_value`,`scope_label`,`input_value`,`commission_amount`,`rule_snapshot`,`sort_order`,`remark`,`created_at`,`updated_at`) values ' .
						'(' . intval($companyId) . ',' . intval($periodId) . ',' . intval($rowId) . ',' . intval($employee['id']) . ',' . intval($project['id']) . ',"' . addslashes($project['name']) . '","' . addslashes($project['metric_type']) . '","' . addslashes($project['metric_name']) . '","' . addslashes($project['commission_mode']) . '","' . addslashes($project['scope_type']) . '","' . addslashes($project['scope_value']) . '","' . addslashes($project['scope_label']) . '",' . $this->formatMoney($item['input_value']) . ',' . $this->formatMoney($item['commission_amount']) . ',"' . addslashes($item['rule_snapshot']) . '",' . intval($project['priority']) . ',"",' . $now . ',' . $now . ')';
					$db->execute($valueSql);
				}
			}
			$db->commit();
			return $periodId;
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = '保存提成核算表失败：' . $e->getMessage();
			return false;
		}
	}

	public function getCommissionMatrix($companyId, $periodId)
	{
		$rowTable = $this->getTableName('salary_commission_rows');
		$valueTable = $this->getTableName('salary_commission_item_values');
		$rows = $this->getDB()->query('select * from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($periodId) . ' order by id asc')->fetchAll();
		$values = array();
		$items = $this->getDB()->query('select * from `' . $valueTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($periodId) . ' order by sort_order desc,id asc')->fetchAll();
		foreach ($items as $item) {
			$rowId = intval($item['commission_row_id']);
			if (!isset($values[$rowId])) {
				$values[$rowId] = array();
			}
			$values[$rowId][] = $item;
		}
		foreach ($rows as $key => $row) {
			$rows[$key]['items'] = isset($values[intval($row['id'])]) ? $values[intval($row['id'])] : array();
			$rows[$key]['item_map'] = array();
			foreach ($rows[$key]['items'] as $item) {
				$rows[$key]['item_map'][intval($item['commission_project_id'])] = $item;
			}
		}
		return $rows;
	}

	public static function getStatusName($status)
	{
		$map = array('draft' => '草稿', 'calculated' => '核算中', 'archived' => '已归档', 'published' => '已发布');
		return isset($map[$status]) ? $map[$status] : $status;
	}

	public function matchEmployeeProjects($employee, $projects)
	{
		$return = array();
		foreach ($projects as $project) {
			if ($project['status'] != 'active' || intval($project['deleted_at']) > 0) {
				continue;
			}
			if ($this->isProjectMatched($employee, $project)) {
				$return[] = $project;
			}
		}
		return $return;
	}

	public function isProjectMatched($employee, $project)
	{
		$type = isset($project['scope_type']) ? $project['scope_type'] : 'all';
		$value = trim(isset($project['scope_value']) ? $project['scope_value'] : '');
		$label = trim(isset($project['scope_label']) ? $project['scope_label'] : '');
		if ($type == 'all') {
			return true;
		}
		if ($type == 'employee') {
			return intval($value) == intval($employee['id']);
		}
		if ($type == 'department') {
			$departmentId = trim(isset($employee['department_id']) ? $employee['department_id'] : '');
			$departmentName = trim(isset($employee['department_name']) ? $employee['department_name'] : '');
			return ($departmentId !== '' && $departmentId == $value) || ($label !== '' && $departmentName == $label);
		}
		if ($type == 'position') {
			$positionName = trim(isset($employee['position_name']) ? $employee['position_name'] : '');
			return $positionName !== '' && ($positionName == $value || $positionName == $label);
		}
		return false;
	}

	protected function buildEmployeeFromCommissionRow($row)
	{
		return array(
			'id' => intval($row['employee_id']),
			'name' => $row['employee_name'],
			'mobile' => $row['employee_no'],
			'department_id' => $row['department_id'],
			'department_name' => $row['department_name'],
			'position_name' => $row['position_name'],
		);
	}

	protected function refreshPeriodSummary($companyId, $periodId)
	{
		$rowTable = $this->getTableName('salary_commission_rows');
		$summary = $this->getDB()->query('select count(*) as employee_count,sum(case when matched_project_count>0 then 1 else 0 end) as matched_count,sum(total_amount) as total_amount from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($periodId))->fetch();
		$sql = 'update `' . $this->getSource() . '` set employee_count=' . intval($summary['employee_count']) . ',matched_count=' . intval($summary['matched_count']) . ',total_amount=' . $this->formatMoney($summary['total_amount']) . ',updated_at=' . time() . ' where company_id=' . intval($companyId) . ' and id=' . intval($periodId);
		return $this->getDB()->execute($sql);
	}

	protected function formatMoney($value)
	{
		$value = str_replace(array(',', '￥', '元', ' '), '', (string)$value);
		if (!is_numeric($value)) {
			$value = 0;
		}
		return sprintf('%.2f', round(floatval($value), 2));
	}
}
