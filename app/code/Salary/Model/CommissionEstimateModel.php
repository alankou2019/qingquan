<?php
/**
 * Employee commission and income estimates.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper;

class CommissionEstimateModel extends BaseModel
{
	protected static $_instance = null;
	protected static $_tableColumnMap = array();

	public function getSource()
	{
		return $this->getTableName('salary_commission_estimates');
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new CommissionEstimateModel();
		}
		return self::$_instance;
	}

	public function getCompanyEmployees($companyId)
	{
		return EmployeeSalaryStructureModel::factory()->getCompanyEmployees($companyId);
	}

	public function getEmployee($companyId, $employeeId)
	{
		$employees = $this->getCompanyEmployees($companyId);
		foreach ($employees as $employee) {
			if (intval($employee['id']) == intval($employeeId)) {
				return $employee;
			}
		}
		return false;
	}

	public function calculateEstimate($companyId, $employeeId, $inputValues)
	{
		$employee = $this->getEmployee($companyId, $employeeId);
		if (!$employee) {
			$this->_lastError = '员工不存在或不属于当前企业';
			return false;
		}
		$projects = CommissionProjectModel::factory()->getCompanyProjects($companyId);
		$projects = CommissionPeriodModel::factory()->matchEmployeeProjects($employee, $projects);
		$projectModel = CommissionProjectModel::factory();
		$levels = array('low', 'mid', 'high');
		$totals = array('low' => 0, 'mid' => 0, 'high' => 0);
		$rows = array();
		foreach ($projects as $project) {
			$projectId = intval($project['id']);
			$values = isset($inputValues[$projectId]) && is_array($inputValues[$projectId]) ? $inputValues[$projectId] : array();
			$enabled = !isset($values['enabled']) || intval($values['enabled']) == 1;
			$row = array(
				'project_id' => $projectId,
				'project_name' => $project['name'],
				'metric_name' => $project['metric_name'],
				'mode_label' => $project['mode_label'],
				'enabled' => $enabled ? 1 : 0,
				'position_name' => isset($employee['position_name']) && $employee['position_name'] !== '' ? $employee['position_name'] : '未设置',
				'rule_summary' => $project['rule_summary'],
				'rule_snapshot' => $projectModel->buildRuleSnapshot($project),
			);
			foreach ($levels as $level) {
				$value = isset($values[$level]) ? $this->money($values[$level]) : '0.00';
				$amount = $enabled ? $projectModel->calculateAmount($project, $value) : '0.00';
				$row[$level . '_value'] = $value;
				$row[$level . '_amount'] = $amount;
				$totals[$level] += floatval($amount);
			}
			$rows[] = $row;
		}

		$salary = $this->getEmployeeBaseSalary($companyId, $employeeId);
		$baseSalary = $salary['amount'];
		$income = array();
		$annual = array();
		$maxIncome = 0;
		foreach ($levels as $level) {
			$totals[$level] = $this->money($totals[$level]);
			$income[$level] = $this->money(floatval($baseSalary) + floatval($totals[$level]));
			$annual[$level] = sprintf('%.2f', round(floatval($income[$level]) * 12 / 10000, 2));
			$maxIncome = max($maxIncome, floatval($income[$level]));
		}
		$barWidth = array();
		foreach ($levels as $level) {
			$barWidth[$level] = $maxIncome > 0 ? max(2, round(floatval($income[$level]) * 100 / $maxIncome, 2)) : 0;
		}

		return array(
			'employee' => $employee,
			'base_salary' => $baseSalary,
			'base_salary_source' => $salary['source'],
			'rows' => $rows,
			'commission' => $totals,
			'income' => $income,
			'annual' => $annual,
			'bar_width' => $barWidth,
		);
	}

	public function saveEstimate($companyId, $estimate, $operatorId)
	{
		if (empty($estimate['employee']) || empty($estimate['rows'])) {
			$this->_lastError = '当前员工没有可保存的提成测算项目';
			return false;
		}
		$employee = $estimate['employee'];
		$now = time();
		$sql = 'insert into `' . $this->getSource() . '` (`company_id`,`employee_id`,`employee_name`,`department_name`,`position_name`,`base_salary`,`low_commission`,`mid_commission`,`high_commission`,`low_income`,`mid_income`,`high_income`,`snapshot_data`,`created_by`,`created_at`,`deleted_at`) values ' .
			'(' . intval($companyId) . ',' . intval($employee['id']) . ',"' . addslashes($employee['name']) . '","' . addslashes(isset($employee['department_name']) ? $employee['department_name'] : '') . '","' . addslashes(isset($employee['position_name']) ? $employee['position_name'] : '') . '",' . $this->money($estimate['base_salary']) . ',' . $this->money($estimate['commission']['low']) . ',' . $this->money($estimate['commission']['mid']) . ',' . $this->money($estimate['commission']['high']) . ',' . $this->money($estimate['income']['low']) . ',' . $this->money($estimate['income']['mid']) . ',' . $this->money($estimate['income']['high']) . ',"' . addslashes(serialize($estimate)) . '",' . intval($operatorId) . ',' . $now . ',0)';
		try {
			$this->getDB()->execute($sql);
			return intval($this->getDB()->lastInsertId());
		} catch (\Exception $e) {
			$this->_lastError = '保存提成测算记录失败：' . $e->getMessage();
			return false;
		}
	}

	public function getCompanyRecords($companyId, $limit = 50)
	{
		$items = $this->getDB()->query('select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' and deleted_at=0 order by id desc limit ' . intval($limit))->fetchAll();
		foreach ($items as $key => $item) {
			$items[$key]['created_time'] = $item['created_at'] ? date('Y-m-d H:i', intval($item['created_at'])) : '';
		}
		return $items;
	}

	public function getRecord($companyId, $recordId)
	{
		$item = $this->getDB()->query('select * from `' . $this->getSource() . '` where id=' . intval($recordId) . ' and company_id=' . intval($companyId) . ' and deleted_at=0 limit 1')->fetch();
		if (!$item) {
			return false;
		}
		$snapshot = @unserialize($item['snapshot_data']);
		$item['estimate'] = is_array($snapshot) ? $snapshot : array();
		return $item;
	}

	public function deleteRecord($companyId, $recordId, $operatorId)
	{
		$item = $this->getRecord($companyId, $recordId);
		if (!$item) {
			$this->_lastError = '提成测算记录不存在';
			return false;
		}
		return $this->getDB()->execute('update `' . $this->getSource() . '` set deleted_at=' . time() . ',deleted_by=' . intval($operatorId) . ' where id=' . intval($recordId) . ' and company_id=' . intval($companyId));
	}

	protected function getEmployeeBaseSalary($companyId, $employeeId)
	{
		$userTable = $this->getTableName('company_user');
		foreach (array('monthly_salary', 'month_salary', 'base_salary', 'salary') as $column) {
			if ($this->hasColumn($userTable, $column)) {
				$employee = $this->getDB()->query('select `' . $column . '` as amount from `' . $userTable . '` where id=' . intval($employeeId) . ' and company_id=' . intval($companyId) . ' limit 1')->fetch();
				return array('amount' => $this->money($employee && isset($employee['amount']) ? $employee['amount'] : 0), 'source' => '人事档案');
			}
		}
		$structureTable = $this->getTableName('employee_salary_structures');
		$valueTable = $this->getTableName('employee_salary_structure_values');
		$projectTable = $this->getTableName('salary_projects');
		$structure = $this->getDB()->query('select id from `' . $structureTable . '` where company_id=' . intval($companyId) . ' and employee_id=' . intval($employeeId) . ' and status="active" order by effective_date desc,version_no desc,id desc limit 1')->fetch();
		if (!$structure) {
			return array('amount' => '0.00', 'source' => '初始工资表');
		}
		$sql = 'select sum(v.default_amount) as amount from `' . $valueTable . '` v inner join `' . $projectTable . '` p on p.id=v.salary_project_id ' .
			'where v.structure_id=' . intval($structure['id']) . ' and p.company_id=' . intval($companyId) . ' and p.status="active" and p.deleted_at=0 and p.direction="earning" and p.linked_module!="commission"';
		$item = $this->getDB()->query($sql)->fetch();
		return array('amount' => $this->money($item && isset($item['amount']) ? $item['amount'] : 0), 'source' => '初始工资表应发类');
	}

	protected function hasColumn($table, $column)
	{
		if (!isset(self::$_tableColumnMap[$table])) {
			$cache = Helper::factory()->getCache();
			$cacheKey = 'salary_schema_columns_v1_' . md5($table);
			self::$_tableColumnMap[$table] = $cache->get($cacheKey);
			if (!is_array(self::$_tableColumnMap[$table])) {
				$columns = $this->getDB()->query("SHOW COLUMNS FROM `" . $table . "`")->fetchAll();
				self::$_tableColumnMap[$table] = array();
				foreach ($columns as $item) {
					if (!empty($item['Field'])) {
						self::$_tableColumnMap[$table][strtolower($item['Field'])] = 1;
					}
				}
				$cache->save($cacheKey, self::$_tableColumnMap[$table], 3600);
			}
		}
		return isset(self::$_tableColumnMap[$table][strtolower($column)]);
	}

	protected function money($value)
	{
		$value = str_replace(array(',', '，', '元', ' '), '', (string)$value);
		if (!is_numeric($value)) {
			$value = 0;
		}
		return sprintf('%.2f', round(floatval($value), 2));
	}
}
