<?php
/**
 * Employee salary structure versions.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Salary\Model\SalaryProjectModel;

class EmployeeSalaryStructureModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("employee_salary_structures");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new EmployeeSalaryStructureModel();
		}
		return self::$_instance;
	}

	public function getInitialSalaryTable($companyId)
	{
		$companyId = intval($companyId);
		$projects = SalaryProjectModel::factory()->getCompanyProjects($companyId);
		$employees = $this->getInitialSalaryEmployees($companyId);
		$values = $this->getInitialValueMap($companyId);
		foreach ($employees as $key => $employee) {
			$rowValues = array();
			$earningTotal = 0;
			$deductionTotal = 0;
			foreach ($projects as $project) {
				if ($project['status'] != 'active' || intval($project['deleted_at']) > 0) {
					continue;
				}
				$projectId = intval($project['id']);
				if (isset($values[intval($employee['id'])][$projectId])) {
					$rowValues[$projectId] = $values[intval($employee['id'])][$projectId];
				} elseif (SalaryProjectModel::isTextProject($project)) {
					$rowValues[$projectId] = isset($project['default_text']) ? $project['default_text'] : '';
				} elseif (SalaryProjectModel::isFormulaProject($project)) {
					$rowValues[$projectId] = '0.00';
				} else {
					$rowValues[$projectId] = isset($project['default_number']) ? sprintf('%.2f', floatval($project['default_number'])) : '0.00';
				}
				if (!SalaryProjectModel::isTextProject($project)) {
					if (intval($project['include_earning'])) {
						$earningTotal += floatval($rowValues[$projectId]);
					}
					if (intval($project['include_deduction'])) {
						$deductionTotal += floatval($rowValues[$projectId]);
					}
				}
			}
			$rowValues['summary_earning_total'] = $this->formatMoney($earningTotal);
			$rowValues['summary_deduction_total'] = $this->formatMoney($deductionTotal);
			$rowValues['summary_net_total'] = $this->formatMoney($earningTotal - $deductionTotal);
			$employees[$key]['values'] = $rowValues;
		}
		return array(
			'projects' => $this->buildInitialDisplayProjects($projects),
			'payroll_projects' => $projects,
			'employees' => $employees,
			'excluded_employees' => $this->getExcludedInitialSalaryEmployees($companyId),
		);
	}

	public function saveInitialSalaryTable($companyId, $postData, $operatorId = 0)
	{
		$companyId = intval($companyId);
		if ($companyId <= 0) {
			$this->_lastError = '企业不存在';
			return false;
		}
		$amounts = isset($postData['amount']) && is_array($postData['amount']) ? $postData['amount'] : array();
		$onlyEmployeeId = isset($postData['initial_salary_employee_id']) ? intval($postData['initial_salary_employee_id']) : 0;
		$projects = SalaryProjectModel::factory()->getCompanyProjects($companyId);
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

		$db = $this->getDB();
		$db->begin();
		try {
			foreach ($amounts as $employeeId => $projectAmounts) {
				$employeeId = intval($employeeId);
				if ($onlyEmployeeId > 0 && $employeeId != $onlyEmployeeId) {
					continue;
				}
				if ($employeeId <= 0 || !is_array($projectAmounts)) {
					continue;
				}
				$structureId = $this->getOrCreateInitialStructure($companyId, $employeeId, $operatorId);
				$numberValues = array();
				foreach ($projectMap as $projectId => $project) {
					if (SalaryProjectModel::isFormulaProject($project)) {
						continue;
					}
					$value = isset($projectAmounts[$projectId]) ? $projectAmounts[$projectId] : 0;
					if (SalaryProjectModel::isTextProject($project)) {
						$this->saveStructureValue($structureId, $projectId, 0, $value);
						continue;
					}
					$amount = $this->formatMoney($value);
					$numberValues[$project['name']] = $amount;
					$this->saveStructureValue($structureId, $projectId, $amount);
				}
				foreach ($projectMap as $projectId => $project) {
					if (!SalaryProjectModel::isFormulaProject($project)) {
						continue;
					}
					$amount = $this->calculateFormulaAmount($project['formula_text'], $numberValues);
					$numberValues[$project['name']] = $amount;
					$this->saveStructureValue($structureId, $projectId, $amount);
				}
			}
			$db->commit();
			return true;
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = '保存初始工资表失败：' . $e->getMessage();
			return false;
		}
	}

	public function saveEmployeeInitialValues($companyId, $employeeId, $projectAmounts, $operatorId = 0)
	{
		return $this->saveInitialSalaryTable($companyId, array('amount' => array(intval($employeeId) => $projectAmounts)), $operatorId);
	}

	public function getInitialValueMap($companyId)
	{
		$return = array();
		$structureTable = $this->getSource();
		$valueTable = $this->getTableName('employee_salary_structure_values');
		$textColumn = $this->hasTextValueColumn($valueTable) ? 'v.text_value' : '"" as text_value';
		$sql = 'select s.employee_id,v.salary_project_id,v.default_amount,' . $textColumn . ' from `' . $structureTable . '` s ' .
			'left join `' . $valueTable . '` v on s.id=v.structure_id ' .
			'where s.company_id=' . intval($companyId) . ' and s.status="active"';
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $item) {
			$employeeId = intval($item['employee_id']);
			$projectId = intval($item['salary_project_id']);
			if ($employeeId <= 0 || $projectId <= 0) {
				continue;
			}
			if (!isset($return[$employeeId])) {
				$return[$employeeId] = array();
			}
			$return[$employeeId][$projectId] = $item['text_value'] !== '' ? $item['text_value'] : sprintf('%.2f', floatval($item['default_amount']));
		}
		return $return;
	}

	public function getCompanyEmployees($companyId)
	{
		return SalaryEmployeeDepartmentModel::factory()->getCompanyEmployees($companyId, 'mobile');
	}

	public function getInitialSalaryEmployees($companyId)
	{
		$employees = $this->getCompanyEmployees($companyId);
		$excluded = $this->getExcludedEmployeeIdMap($companyId);
		$return = array();
		foreach ($employees as $employee) {
			if (!isset($excluded[intval($employee['id'])])) {
				$return[] = $employee;
			}
		}
		return $return;
	}

	public function getExcludedInitialSalaryEmployees($companyId)
	{
		$employees = $this->getCompanyEmployees($companyId);
		$excluded = $this->getExcludedEmployeeIdMap($companyId);
		$return = array();
		foreach ($employees as $employee) {
			if (isset($excluded[intval($employee['id'])])) {
				$return[] = $employee;
			}
		}
		return $return;
	}

	public function deleteInitialSalaryEmployee($companyId, $employeeId, $operatorId = 0)
	{
		$companyId = intval($companyId);
		$employeeId = intval($employeeId);
		if ($companyId <= 0 || $employeeId <= 0) {
			$this->_lastError = '员工参数不正确';
			return false;
		}
		$item = self::factory()->findFirst('company_id=' . $companyId . ' and employee_id=' . $employeeId . ' and status="active"');
		if ($item) {
			return $item->save(array('status' => 'inactive', 'updated_at' => time()));
		}
		$existing = $this->getLatestStructure($companyId, $employeeId);
		if ($existing) {
			return true;
		}
		$now = time();
		$model = new EmployeeSalaryStructureModel();
		return $model->save(array(
			'company_id' => $companyId,
			'employee_id' => $employeeId,
			'version_no' => 1,
			'effective_date' => date('Y-m-d'),
			'status' => 'inactive',
			'created_by' => intval($operatorId),
			'created_at' => $now,
			'updated_at' => $now,
		));
	}

	public function restoreInitialSalaryEmployee($companyId, $employeeId)
	{
		$companyId = intval($companyId);
		$employeeId = intval($employeeId);
		$item = $this->getLatestStructure($companyId, $employeeId);
		if (!$item || $item->status != 'inactive') {
			$this->_lastError = '未找到已移出的员工';
			return false;
		}
		return $item->save(array('status' => 'active', 'updated_at' => time()));
	}

	protected function getOrCreateInitialStructure($companyId, $employeeId, $operatorId = 0)
	{
		$companyId = intval($companyId);
		$employeeId = intval($employeeId);
		$item = self::factory()->findFirst('company_id=' . $companyId . ' and employee_id=' . $employeeId . ' and status="active"');
		if ($item) {
			return intval($item->id);
		}
		$inactive = $this->getLatestStructure($companyId, $employeeId);
		if ($inactive) {
			$inactive->save(array('status' => 'active', 'updated_at' => time()));
			return intval($inactive->id);
		}
		$now = time();
		$model = new EmployeeSalaryStructureModel();
		$model->save(array(
			'company_id' => $companyId,
			'employee_id' => $employeeId,
			'version_no' => 1,
			'effective_date' => date('Y-m-d'),
			'status' => 'active',
			'created_by' => intval($operatorId),
			'created_at' => $now,
			'updated_at' => $now,
		));
		return intval($model->id);
	}

	protected function getExcludedEmployeeIdMap($companyId)
	{
		$return = array();
		$sql = 'select employee_id,max(case when status="active" then 1 else 0 end) as has_active,max(case when status="inactive" then 1 else 0 end) as has_inactive from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' group by employee_id';
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $item) {
			if (intval($item['has_active']) == 0 && intval($item['has_inactive']) == 1) {
				$return[intval($item['employee_id'])] = 1;
			}
		}
		return $return;
	}

	protected function getLatestStructure($companyId, $employeeId)
	{
		return self::factory()->findFirst(array(
			'conditions' => 'company_id=' . intval($companyId) . ' and employee_id=' . intval($employeeId),
			'order' => 'version_no desc,id desc',
		));
	}

	protected function buildInitialDisplayProjects($projects)
	{
		$groups = array('earning' => array(), 'deduction' => array(), 'data' => array(), 'note' => array(), 'other' => array());
		foreach ($projects as $project) {
			if ($project['status'] != 'active' || intval($project['deleted_at']) > 0) {
				continue;
			}
			$project['direction'] = SalaryProjectModel::normalizeDirection($project['direction']);
			$group = isset($groups[$project['direction']]) ? $project['direction'] : 'other';
			$project['initial_group'] = $group;
			$project['value_key'] = intval($project['id']);
			$project['is_summary_project'] = 0;
			$groups[$group][] = $project;
		}
		$return = array($this->buildSummaryProject('summary_earning_total', '应发总额'));
		$return = array_merge($return, $groups['earning']);
		$return[] = $this->buildSummaryProject('summary_deduction_total', '应扣总额');
		$return = array_merge($return, $groups['deduction']);
		$return[] = $this->buildSummaryProject('summary_net_total', '实发总额');
		return array_merge($return, $groups['data'], $groups['note'], $groups['other']);
	}

	public function buildSalaryTableDisplayProjects($projects)
	{
		return $this->buildInitialDisplayProjects($projects);
	}

	protected function buildSummaryProject($key, $name)
	{
		return array(
			'id' => 0,
			'value_key' => $key,
			'name' => $name,
			'status' => 'active',
			'deleted_at' => 0,
			'calculation_mode_label' => '系统计算',
			'is_text_project' => 0,
			'is_formula_project' => 1,
			'is_summary_project' => 1,
			'initial_group' => 'summary',
		);
	}

	protected function saveStructureValue($structureId, $projectId, $amount, $textValue = '')
	{
		$valueTable = $this->getTableName('employee_salary_structure_values');
		$now = time();
		if ($this->hasTextValueColumn($valueTable)) {
			$sql = 'insert into `' . $valueTable . '` (`structure_id`,`salary_project_id`,`default_amount`,`text_value`,`created_at`,`updated_at`) values ' .
				'(' . intval($structureId) . ',' . intval($projectId) . ',' . $this->formatMoney($amount) . ',"' . addslashes(trim((string)$textValue)) . '",' . $now . ',' . $now . ') ' .
				'on duplicate key update default_amount=values(default_amount),text_value=values(text_value),updated_at=values(updated_at)';
		} else {
			$sql = 'insert into `' . $valueTable . '` (`structure_id`,`salary_project_id`,`default_amount`,`created_at`,`updated_at`) values ' .
				'(' . intval($structureId) . ',' . intval($projectId) . ',' . $this->formatMoney($amount) . ',' . $now . ',' . $now . ') ' .
				'on duplicate key update default_amount=values(default_amount),updated_at=values(updated_at)';
		}
		return $this->getDB()->execute($sql);
	}

	protected function hasTextValueColumn($table)
	{
		$item = $this->getDB()->query("SHOW COLUMNS FROM `" . $table . "` LIKE 'text_value'")->fetch();
		return $item ? true : false;
	}

	public function calculateFormulaAmount($formula, $amountMap)
	{
		$formula = trim((string)$formula);
		if ($formula == '') {
			return 0.00;
		}
		uksort($amountMap, function ($a, $b) {
			return strlen($b) - strlen($a);
		});
		foreach ($amountMap as $name => $amount) {
			$formula = str_replace($name, '(' . $this->formatMoney($amount) . ')', $formula);
		}
		$expr = preg_replace('/\s+/', '', $formula);
		if (!preg_match('/^[0-9\.\+\-\*\/\(\)]+$/', $expr)) {
			return 0.00;
		}
		if (strpos($expr, '/0') !== false) {
			return 0.00;
		}
		$result = 0;
		try {
			$result = @eval('return ' . $expr . ';');
		} catch (\Exception $e) {
			$result = 0;
		}
		return $this->formatMoney($result);
	}

	protected function formatMoney($value)
	{
		$value = str_replace(array(',', '，', '￥', '元', ' '), '', (string)$value);
		if (!is_numeric($value)) {
			$value = 0;
		}
		return sprintf('%.2f', round(floatval($value), 2));
	}

	protected function getEmployeeMobileColumn($userTable)
	{
		$db = $this->getDB();
		$candidates = array('jobnumber', 'mobile', 'phone');
		foreach ($candidates as $column) {
			$item = $db->query("SHOW COLUMNS FROM `" . $userTable . "` LIKE '" . addslashes($column) . "'")->fetch();
			if ($item) {
				return $column;
			}
		}
		return '';
	}
}
