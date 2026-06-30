<?php
/**
 * Salary report queries with company and view-scope isolation.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class SalaryReportModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("payroll_employee_rows");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new SalaryReportModel();
		}
		return self::$_instance;
	}

	public function getLatestPayrollMonth($companyId)
	{
		$periodTable = $this->getTableName('payroll_periods');
		$sql = 'select payroll_month from `' . $periodTable . '` where company_id=' . intval($companyId) .
			' order by payroll_month desc,id desc limit 1';
		$row = $this->getDB()->query($sql)->fetch();
		return $row ? $row['payroll_month'] : '';
	}

	public function getPayrollMonths($companyId, $limit = 24)
	{
		$items = array();
		$periodTable = $this->getTableName('payroll_periods');
		$sql = 'select distinct payroll_month from `' . $periodTable . '` where company_id=' . intval($companyId) .
			' order by payroll_month desc limit ' . intval($limit);
		$rows = $this->getDB()->query($sql)->fetchAll();
		foreach ($rows as $row) {
			$items[] = $row['payroll_month'];
		}
		return $items;
	}

	public function getDepartments($companyId, $scope)
	{
		$where = 'company_id=' . intval($companyId) . ' and department_name<>""';
		$scopeSql = $this->buildScopeSql($scope);
		if ($scopeSql != '') {
			$where .= ' and ' . $scopeSql;
		}
		$sql = 'select distinct department_name from `' . $this->getSource() . '` where ' . $where . ' order by department_name asc';
		$rows = $this->getDB()->query($sql)->fetchAll();
		$return = array();
		foreach ($rows as $row) {
			$return[] = $row['department_name'];
		}
		return $return;
	}

	public function getSummary($companyId, $filters, $scope)
	{
		$sql = 'select count(*) as row_count,ifnull(sum(r.earning_total),0) as earning_total,' .
			'ifnull(sum(r.deduction_total),0) as deduction_total,ifnull(sum(r.net_amount),0) as net_total ' .
			$this->buildBaseSql($companyId, $filters, $scope);
		$row = $this->getDB()->query($sql)->fetch();
		return array(
			'row_count' => $row ? intval($row['row_count']) : 0,
			'earning_total' => $row ? $this->money($row['earning_total']) : '0.00',
			'deduction_total' => $row ? $this->money($row['deduction_total']) : '0.00',
			'net_total' => $row ? $this->money($row['net_total']) : '0.00',
		);
	}

	public function getRows($companyId, $filters, $scope, $page = 1, $pageSize = 50)
	{
		$page = max(1, intval($page));
		$pageSize = max(20, min(200, intval($pageSize)));
		$offset = ($page - 1) * $pageSize;
		$sql = 'select r.*,p.payroll_month,p.status,p.source_type,p.source_name,p.archived_at,p.published_at ' .
			$this->buildBaseSql($companyId, $filters, $scope) .
			' order by p.payroll_month desc,r.department_name asc,r.employee_name asc,r.id asc limit ' . $offset . ',' . $pageSize;
		$rows = $this->getDB()->query($sql)->fetchAll();
		return $this->formatRows($rows);
	}

	public function getAllRows($companyId, $filters, $scope, $limit = 5000)
	{
		$sql = 'select r.*,p.payroll_month,p.status,p.source_type,p.source_name,p.archived_at,p.published_at ' .
			$this->buildBaseSql($companyId, $filters, $scope) .
			' order by p.payroll_month desc,r.department_name asc,r.employee_name asc,r.id asc limit ' . intval($limit);
		$rows = $this->getDB()->query($sql)->fetchAll();
		return $this->formatRows($rows);
	}

	protected function buildBaseSql($companyId, $filters, $scope)
	{
		$periodTable = $this->getTableName('payroll_periods');
		$where = 'r.company_id=' . intval($companyId) . ' and p.company_id=' . intval($companyId);
		if (!empty($filters['payroll_month']) && preg_match('/^\d{4}\-\d{2}$/', $filters['payroll_month'])) {
			$where .= ' and p.payroll_month="' . addslashes($filters['payroll_month']) . '"';
		}
		if (!empty($filters['department_name'])) {
			$where .= ' and r.department_name="' . addslashes($filters['department_name']) . '"';
		}
		if (!empty($filters['keyword'])) {
			$keyword = addslashes($filters['keyword']);
			$where .= ' and (r.employee_name like "%' . $keyword . '%" or r.employee_no like "%' . $keyword . '%")';
		}
		$scopeSql = $this->buildScopeSql($scope, 'r');
		if ($scopeSql != '') {
			$where .= ' and ' . $scopeSql;
		}
		return 'from `' . $this->getSource() . '` r left join `' . $periodTable . '` p on r.payroll_period_id=p.id ' .
			'where ' . $where;
	}

	protected function buildScopeSql($scope, $alias = '')
	{
		if (!empty($scope['all'])) {
			return '';
		}
		$prefix = $alias == '' ? '' : $alias . '.';
		$parts = array();
		if (!empty($scope['employee_ids'])) {
			$ids = array();
			foreach ($scope['employee_ids'] as $id) {
				$id = intval($id);
				if ($id > 0) {
					$ids[] = $id;
				}
			}
			if (!empty($ids)) {
				$parts[] = $prefix . 'employee_id in (' . implode(',', array_unique($ids)) . ')';
			}
		}
		if (!empty($scope['department_names'])) {
			$names = array();
			foreach ($scope['department_names'] as $name) {
				$name = trim($name);
				if ($name != '') {
					$names[] = '"' . addslashes($name) . '"';
				}
			}
			if (!empty($names)) {
				$parts[] = $prefix . 'department_name in (' . implode(',', array_unique($names)) . ')';
			}
		}
		return empty($parts) ? '1=0' : '(' . implode(' or ', $parts) . ')';
	}

	protected function formatRows($rows)
	{
		foreach ($rows as $key => $row) {
			$rows[$key]['status_name'] = PayrollPeriodModel::getStatusName($row['status']);
			$rows[$key]['source_label'] = PayrollPeriodModel::getSourceName($row['source_type'], $row['source_name']);
			$rows[$key]['earning_total'] = $this->money($row['earning_total']);
			$rows[$key]['deduction_total'] = $this->money($row['deduction_total']);
			$rows[$key]['net_amount'] = $this->money($row['net_amount']);
		}
		return $rows;
	}

	protected function money($value)
	{
		return sprintf('%.2f', round(floatval($value), 2));
	}
}
