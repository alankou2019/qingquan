<?php
/**
 * Employee monthly payroll rows.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class PayrollEmployeeRowModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("payroll_employee_rows");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new PayrollEmployeeRowModel();
		}
		return self::$_instance;
	}

	public function getRowsByPeriod($companyId, $periodId)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and payroll_period_id=' . intval($periodId) . ' order by id asc';
		return $this->getDB()->query($sql)->fetchAll();
	}

	public function getPayrollMatrix($companyId, $periodId)
	{
		$rows = $this->getRowsByPeriod($companyId, $periodId);
		$valueTable = $this->getTableName('payroll_item_values');
		$values = array();
		$sql = 'select * from `' . $valueTable . '` where company_id=' . intval($companyId) .
			' and payroll_period_id=' . intval($periodId) . ' order by id asc';
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $item) {
			$rowId = intval($item['payroll_employee_row_id']);
			if (!isset($values[$rowId])) {
				$values[$rowId] = array();
			}
			if (SalaryProjectModel::isTextProject($item) && isset($item['text_value'])) {
				$values[$rowId][intval($item['salary_project_id'])] = $item['text_value'];
			} else {
				$values[$rowId][intval($item['salary_project_id'])] = sprintf('%.2f', floatval($item['final_amount']));
			}
		}
		foreach ($rows as $key => $row) {
			$rowId = intval($row['id']);
			$rows[$key]['values'] = isset($values[$rowId]) ? $values[$rowId] : array();
		}
		return $rows;
	}

	public function getPayrollProjectSnapshots($companyId, $periodId)
	{
		$projects = $this->getPeriodProjectSnapshot($companyId, $periodId);
		if (!empty($projects)) {
			return SalaryProjectModel::factory()->formatProjectItems($projects);
		}

		// Compatibility path for payroll periods created before project snapshots.
		$currentProjects = SalaryProjectModel::factory()->getCompanyProjects($companyId);
		$currentMap = array();
		foreach ($currentProjects as $project) {
			$currentMap[intval($project['id'])] = $project;
		}

		$valueTable = $this->getTableName('payroll_item_values');
		$sql = 'select * from `' . $valueTable . '` where company_id=' . intval($companyId) .
			' and payroll_period_id=' . intval($periodId) . ' order by id asc';
		$items = $this->getDB()->query($sql)->fetchAll();
		$projectMap = array();
		$sortOrder = 10;
		foreach ($items as $item) {
			$projectId = intval($item['salary_project_id']);
			if ($projectId <= 0 || isset($projectMap[$projectId])) {
				continue;
			}
			$current = isset($currentMap[$projectId]) ? $currentMap[$projectId] : array();
			$projectMap[$projectId] = array(
				'id' => $projectId,
				'company_id' => intval($companyId),
				'template_id' => isset($current['template_id']) ? $current['template_id'] : 0,
				'name' => $item['project_name'],
				'source_type' => $item['source_type'],
				'direction' => $item['direction'],
				'calculation_mode' => $item['calculation_mode'],
				'linked_module' => $item['linked_module'],
				'formula_text' => isset($current['formula_text']) ? $current['formula_text'] : '',
				'default_number' => '0.00',
				'default_text' => '',
				'include_earning' => intval($item['include_earning']),
				'include_deduction' => intval($item['include_deduction']),
				'include_net' => intval($item['include_net']),
				'sort_order' => isset($current['sort_order']) ? intval($current['sort_order']) : $sortOrder,
				'status' => 'active',
				'deleted_at' => 0,
			);
			$sortOrder += 10;
		}
		return SalaryProjectModel::factory()->formatProjectItems(array_values($projectMap));
	}

	protected function getPeriodProjectSnapshot($companyId, $periodId)
	{
		$periodTable = $this->getTableName('payroll_periods');
		$column = $this->getDB()->query("SHOW COLUMNS FROM `" . $periodTable . "` LIKE 'project_snapshot'")->fetch();
		if (!$column) {
			return array();
		}
		$sql = 'select project_snapshot from `' . $periodTable . '` where company_id=' . intval($companyId) .
			' and id=' . intval($periodId) . ' limit 1';
		$period = $this->getDB()->query($sql)->fetch();
		if (!$period || empty($period['project_snapshot'])) {
			return array();
		}
		$projects = @unserialize($period['project_snapshot']);
		return is_array($projects) ? array_values($projects) : array();
	}
}
