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
		$employees = $this->getCompanyEmployees($companyId);
		$values = $this->getInitialValueMap($companyId);
		foreach ($employees as $key => $employee) {
			$rowValues = array();
			foreach ($projects as $project) {
				if ($project['status'] != 'active' || intval($project['deleted_at']) > 0) {
					continue;
				}
				$projectId = intval($project['id']);
				$rowValues[$projectId] = isset($values[intval($employee['id'])][$projectId]) ? $values[intval($employee['id'])][$projectId] : '0.00';
			}
			$employees[$key]['values'] = $rowValues;
		}
		return array('projects' => $projects, 'employees' => $employees);
	}

	public function saveInitialSalaryTable($companyId, $postData, $operatorId = 0)
	{
		$companyId = intval($companyId);
		if ($companyId <= 0) {
			$this->_lastError = '企业不存在';
			return false;
		}
		$amounts = isset($postData['amount']) && is_array($postData['amount']) ? $postData['amount'] : array();
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
				if ($employeeId <= 0 || !is_array($projectAmounts)) {
					continue;
				}
				$structureId = $this->getOrCreateInitialStructure($companyId, $employeeId, $operatorId);
				$numberValues = array();
				foreach ($projectMap as $projectId => $project) {
					if ($project['calculation_mode'] == 'formula') {
						continue;
					}
					$value = isset($projectAmounts[$projectId]) ? $projectAmounts[$projectId] : 0;
					$amount = $this->formatMoney($value);
					$numberValues[$project['name']] = $amount;
					$this->saveStructureValue($structureId, $projectId, $amount);
				}
				foreach ($projectMap as $projectId => $project) {
					if ($project['calculation_mode'] != 'formula') {
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
		$sql = 'select s.employee_id,v.salary_project_id,v.default_amount from `' . $structureTable . '` s ' .
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
			$return[$employeeId][$projectId] = sprintf('%.2f', floatval($item['default_amount']));
		}
		return $return;
	}

	public function getCompanyEmployees($companyId)
	{
		$db = $this->getDB();
		$userTable = $this->getTableName('company_user');
		$departTable = $this->getTableName('company_department');
		$mobileColumn = $this->getEmployeeMobileColumn($userTable);
		$mobileSelect = $mobileColumn ? 'u.`' . $mobileColumn . '` as mobile' : '"" as mobile';
		$sql = 'select u.id,u.name,' . $mobileSelect . ',u.department_id,d.name as department_name ' .
			'from `' . $userTable . '` u ' .
			'left join `' . $departTable . '` d on u.department_id=d.dingding_id and u.company_id=d.company_id ' .
			'where u.company_id=' . intval($companyId) . ' order by u.id asc';
		return $db->query($sql)->fetchAll();
	}

	protected function getOrCreateInitialStructure($companyId, $employeeId, $operatorId = 0)
	{
		$companyId = intval($companyId);
		$employeeId = intval($employeeId);
		$item = self::factory()->findFirst('company_id=' . $companyId . ' and employee_id=' . $employeeId . ' and status="active"');
		if ($item) {
			return intval($item->id);
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

	protected function saveStructureValue($structureId, $projectId, $amount)
	{
		$valueTable = $this->getTableName('employee_salary_structure_values');
		$now = time();
		$sql = 'insert into `' . $valueTable . '` (`structure_id`,`salary_project_id`,`default_amount`,`created_at`,`updated_at`) values ' .
			'(' . intval($structureId) . ',' . intval($projectId) . ',' . $this->formatMoney($amount) . ',' . $now . ',' . $now . ') ' .
			'on duplicate key update default_amount=values(default_amount),updated_at=values(updated_at)';
		return $this->getDB()->execute($sql);
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
