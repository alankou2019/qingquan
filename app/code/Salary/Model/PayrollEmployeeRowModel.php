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

	public function savePayrollProjectOrder($companyId, $periodId, $direction, $projectIds)
	{
		$companyId = intval($companyId);
		$periodId = intval($periodId);
		$direction = SalaryProjectModel::normalizeDirection($direction);
		if ($companyId <= 0 || $periodId <= 0 || !in_array($direction, array('earning', 'deduction', 'data', 'note', 'other'))) {
			$this->_lastError = '工资核算表排序参数不正确';
			return false;
		}

		$period = PayrollPeriodModel::factory()->getCompanyPeriod($companyId, $periodId);
		if (!$period) {
			$this->_lastError = '工资核算表不存在或不属于当前企业';
			return false;
		}
		if (!PayrollPeriodModel::canEdit($period['status'])) {
			$this->_lastError = '当前工资核算表已经不能调整项目顺序';
			return false;
		}

		$periodTable = $this->getTableName('payroll_periods');
		$column = $this->getDB()->query("SHOW COLUMNS FROM `" . $periodTable . "` LIKE 'project_snapshot'")->fetch();
		if (!$column) {
			$this->_lastError = '当前测试库缺少工资项目快照字段，请先完成数据库升级';
			return false;
		}

		$projects = $this->getPeriodProjectSnapshot($companyId, $periodId);
		if (empty($projects)) {
			$projects = $this->getPayrollProjectSnapshots($companyId, $periodId);
		}
		$orderedIds = $this->normalizeProjectOrderIds($projectIds);
		$groupItems = array();
		$groupPositions = array();
		foreach ($projects as $index => $project) {
			$projectDirection = isset($project['direction']) && $project['direction'] != '' ? SalaryProjectModel::normalizeDirection($project['direction']) : 'other';
			$isActive = !isset($project['status']) || $project['status'] == 'active';
			$isDeleted = isset($project['deleted_at']) && intval($project['deleted_at']) > 0;
			if ($isActive && !$isDeleted && $projectDirection == $direction) {
				$groupItems[intval($project['id'])] = $project;
				$groupPositions[] = $index;
			}
		}
		if (!$this->isCompleteProjectOrder($orderedIds, $groupItems)) {
			$this->_lastError = '工资项目已经发生变化，请刷新页面后重新排序';
			return false;
		}

		foreach ($groupPositions as $positionIndex => $projectIndex) {
			$projects[$projectIndex] = $groupItems[$orderedIds[$positionIndex]];
		}
		foreach ($projects as $index => $project) {
			$projects[$index]['direction'] = SalaryProjectModel::normalizeDirection($project['direction']);
			$projects[$index]['sort_order'] = ($index + 1) * 10;
		}

		$sql = 'update `' . $periodTable . '` set project_snapshot="' .
			addslashes(serialize(array_values($projects))) . '",updated_at=' . time() .
			' where company_id=' . $companyId . ' and id=' . $periodId;
		if (!$this->getDB()->execute($sql)) {
			$this->_lastError = '工资核算表项目排序保存失败，请稍后重试';
			return false;
		}
		return true;
	}

	protected function normalizeProjectOrderIds($projectIds)
	{
		if (!is_array($projectIds)) {
			$projectIds = explode(',', trim((string)$projectIds));
		}
		$return = array();
		foreach ($projectIds as $projectId) {
			$projectId = intval($projectId);
			if ($projectId > 0 && !in_array($projectId, $return)) {
				$return[] = $projectId;
			}
		}
		return $return;
	}

	protected function isCompleteProjectOrder($orderedIds, $groupItems)
	{
		if (count($orderedIds) != count($groupItems)) {
			return false;
		}
		foreach ($orderedIds as $projectId) {
			if (!isset($groupItems[$projectId])) {
				return false;
			}
		}
		return true;
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
