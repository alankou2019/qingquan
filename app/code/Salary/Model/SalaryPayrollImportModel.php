<?php
/**
 * Excel payroll import service for company salary module.
 */
namespace ScshuxCms\Salary\Model;

use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;

class SalaryPayrollImportModel extends BaseModel
{
	protected static $_instance = null;
	protected $_lastErrors = array();
	protected $_previewProjects = array();

	public function getSource()
	{
		return $this->getTableName("payroll_periods");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new SalaryPayrollImportModel();
		}
		return self::$_instance;
	}

	public function getLastErrors()
	{
		return $this->_lastErrors;
	}

	public function getPreviewProjects()
	{
		return $this->_previewProjects;
	}

	public function previewFirstImport($companyId, $filePath)
	{
		$this->_lastErrors = array();
		$parsed = $this->parseExcel($filePath);
		if (!$parsed) {
			return false;
		}
		if (empty($parsed['project_headers'])) {
			$this->_lastErrors[] = array('row' => 1, 'name' => '', 'mobile' => '', 'reason' => 'Excel表头没有可识别的工资项目列');
			return false;
		}
		$this->_previewProjects = array();
		$sort = 10;
		foreach ($parsed['project_headers'] as $header) {
			$direction = $this->guessDirection($header['name']);
			$this->_previewProjects[] = array(
				'name' => $header['name'],
				'direction' => $direction,
				'direction_name' => $this->directionName($direction),
				'sort_order' => $sort,
			);
			$sort += 10;
		}
		return true;
	}

	public function importFromExcel($companyId, $payrollMonth, $filePath, $sourceName, $operatorId, $autoCreateProjects = false)
	{
		$this->_lastErrors = array();
		$companyId = intval($companyId);
		$operatorId = intval($operatorId);
		$payrollMonth = trim($payrollMonth);
		if ($companyId <= 0 || !preg_match('/^\d{4}\-\d{2}$/', $payrollMonth)) {
			$this->_lastErrors[] = array('row' => 0, 'name' => '', 'mobile' => '', 'reason' => '工资月份不正确，请使用YYYY-MM格式');
			return false;
		}

		$parsed = $this->parseExcel($filePath);
		if (!$parsed) {
			return false;
		}

		$projects = SalaryProjectModel::factory()->getCompanyProjects($companyId);
		if (empty($projects)) {
			if (!$autoCreateProjects) {
				$this->_lastErrors[] = array('row' => 0, 'name' => '', 'mobile' => '', 'reason' => '企业还没有工资项目，请先确认按Excel表头自动生成工资项目');
				return false;
			}
			if (!$this->createProjectsFromHeaders($companyId, $parsed['project_headers'])) {
				return false;
			}
			$projects = SalaryProjectModel::factory()->getCompanyProjects($companyId);
		}

		$projectMap = array();
		foreach ($projects as $project) {
			if ($project['status'] == 'active' && intval($project['deleted_at']) == 0) {
				$projectMap[$this->normalizeHeader($project['name'])] = $project;
			}
		}

		$importProjects = array();
		foreach ($parsed['project_headers'] as $header) {
			$key = $this->normalizeHeader($header['name']);
			if (!isset($projectMap[$key])) {
				$this->_lastErrors[] = array('row' => 1, 'name' => '', 'mobile' => '', 'reason' => '工资项目“' . $header['name'] . '”未在工资项目设置中启用');
			} else {
				$importProjects[] = array('header' => $header, 'project' => $projectMap[$key]);
			}
		}
		if (empty($importProjects)) {
			$this->_lastErrors[] = array('row' => 1, 'name' => '', 'mobile' => '', 'reason' => '没有可导入的工资项目列');
		}
		if (!empty($this->_lastErrors)) {
			return false;
		}

		$employees = $this->getCompanyEmployees($companyId);
		$rows = $this->buildImportRows($parsed['rows'], $importProjects, $employees);
		if (!empty($this->_lastErrors)) {
			return false;
		}

		return $this->savePayrollRows($companyId, $payrollMonth, $sourceName, $operatorId, $rows);
	}

	public static function getDefaultTemplateHeaders($projects)
	{
		$headers = array('姓名', '手机号');
		if (!empty($projects)) {
			foreach ($projects as $project) {
				if ($project['status'] == 'active' && intval($project['deleted_at']) == 0) {
					$headers[] = $project['name'];
				}
			}
		}
		if (count($headers) <= 2) {
			$headers = array('姓名', '手机号', '基本工资', '岗位工资', '绩效工资', '提成', '补贴', '社保', '公积金', '个税');
		}
		return $headers;
	}

	protected function parseExcel($filePath)
	{
		if (!$filePath || !file_exists($filePath)) {
			$this->_lastErrors[] = array('row' => 0, 'name' => '', 'mobile' => '', 'reason' => 'Excel文件不存在，请重新上传');
			return false;
		}
		try {
			FactoryDefault::getDefault()->get('phpexcel');
			$objPHPExcel = \PHPExcel_IOFactory::load($filePath);
			$sheet = $objPHPExcel->getSheet(0);
		} catch (\Exception $e) {
			$this->_lastErrors[] = array('row' => 0, 'name' => '', 'mobile' => '', 'reason' => '读取Excel失败：' . $e->getMessage());
			return false;
		}

		$highestColumn = $sheet->getHighestColumn();
		$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
		$highestRow = intval($sheet->getHighestRow());
		$columns = array();
		$nameColumn = -1;
		$mobileColumn = -1;
		$projectHeaders = array();
		for ($col = 0; $col < $highestColumnIndex; $col++) {
			$value = $this->cellString($sheet->getCellByColumnAndRow($col, 1)->getValue());
			$name = trim($value);
			if ($name == '') {
				continue;
			}
			$key = $this->normalizeHeader($name);
			$columns[$col] = array('name' => $name, 'key' => $key);
			if ($this->isNameHeader($key)) {
				$nameColumn = $col;
			} elseif ($this->isMobileHeader($key)) {
				$mobileColumn = $col;
			} elseif (!$this->isIgnoredHeader($key)) {
				$projectHeaders[] = array('column' => $col, 'name' => $name, 'key' => $key);
			}
		}

		if ($nameColumn < 0) {
			$this->_lastErrors[] = array('row' => 1, 'name' => '', 'mobile' => '', 'reason' => 'Excel表头缺少“姓名”列');
		}
		if ($mobileColumn < 0) {
			$this->_lastErrors[] = array('row' => 1, 'name' => '', 'mobile' => '', 'reason' => 'Excel表头缺少“手机号”列');
		}
		if (!empty($this->_lastErrors)) {
			return false;
		}

		$rows = array();
		for ($row = 2; $row <= $highestRow; $row++) {
			$name = trim($this->cellString($sheet->getCellByColumnAndRow($nameColumn, $row)->getValue()));
			$mobile = $this->normalizeMobile($this->cellString($sheet->getCellByColumnAndRow($mobileColumn, $row)->getValue()));
			$hasProjectValue = false;
			$values = array();
			foreach ($projectHeaders as $header) {
				$raw = $this->cellString($sheet->getCellByColumnAndRow($header['column'], $row)->getCalculatedValue());
				if (trim($raw) !== '') {
					$hasProjectValue = true;
				}
				$values[$header['key']] = array('raw' => $raw, 'name' => $header['name']);
			}
			if ($name == '' && $mobile == '' && !$hasProjectValue) {
				continue;
			}
			$rows[] = array(
				'excel_row' => $row,
				'name' => $name,
				'mobile' => $mobile,
				'values' => $values,
			);
		}

		return array(
			'columns' => $columns,
			'project_headers' => $projectHeaders,
			'rows' => $rows,
		);
	}

	protected function createProjectsFromHeaders($companyId, $headers)
	{
		if (empty($headers)) {
			$this->_lastErrors[] = array('row' => 1, 'name' => '', 'mobile' => '', 'reason' => 'Excel没有可自动生成的工资项目');
			return false;
		}
		$sort = 10;
		foreach ($headers as $header) {
			$name = trim($header['name']);
			if ($name == '') {
				continue;
			}
			$direction = $this->guessDirection($name);
			$postData = array(
				'name' => $name,
				'source_type' => 'calculated',
				'direction' => $direction,
				'calculation_mode' => 'manual',
				'linked_module' => 'none',
				'formula_text' => '首次导入工资表时按Excel表头自动生成',
				'include_earning' => $direction == 'earning' ? 1 : 0,
				'include_deduction' => $direction == 'deduction' ? 1 : 0,
				'include_net' => $direction == 'statistic' ? 0 : 1,
				'sort_order' => $sort,
				'status' => 'active',
			);
			if (!SalaryProjectModel::factory()->saveCustomProject($companyId, $postData)) {
				$this->_lastErrors[] = array('row' => 1, 'name' => '', 'mobile' => '', 'reason' => SalaryProjectModel::factory()->getLastError());
				return false;
			}
			$sort += 10;
		}
		return true;
	}

	protected function getCompanyEmployees($companyId)
	{
		$db = $this->getDB();
		$userTable = $this->getTableName('company_user');
		$departTable = $this->getTableName('company_department');
		$mobileColumn = $this->getEmployeeMobileColumn($userTable);
		$mobileSelect = $mobileColumn ? 'u.`' . $mobileColumn . '` as mobile_source' : '"" as mobile_source';
		$sql = 'select u.id,u.name,' . $mobileSelect . ',u.department_id,d.name as department_name ' .
			'from `' . $userTable . '` u ' .
			'left join `' . $departTable . '` d on u.department_id=d.dingding_id and u.company_id=d.company_id ' .
			'where u.company_id=' . intval($companyId) . ' order by u.id asc';
		$items = $db->query($sql)->fetchAll();
		$nameMap = array();
		foreach ($items as $item) {
			$key = $this->normalizeName($item['name']);
			if (!isset($nameMap[$key])) {
				$nameMap[$key] = array();
			}
			$item['jobnumber'] = isset($item['mobile_source']) ? $item['mobile_source'] : '';
			$item['mobile'] = $this->normalizeMobile($item['jobnumber']);
			$nameMap[$key][] = $item;
		}
		return $nameMap;
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

	protected function buildImportRows($excelRows, $importProjects, $employees)
	{
		$return = array();
		$employeeRowMap = array();
		foreach ($excelRows as $excelRow) {
			$name = trim($excelRow['name']);
			$mobile = $excelRow['mobile'];
			if ($name == '') {
				$this->_lastErrors[] = array('row' => $excelRow['excel_row'], 'name' => $name, 'mobile' => $mobile, 'reason' => '姓名不能为空');
				continue;
			}
			$employee = $this->matchEmployee($excelRow, $employees);
			if (!$employee) {
				continue;
			}
			$employeeId = intval($employee['id']);
			if (isset($employeeRowMap[$employeeId])) {
				$this->_lastErrors[] = array('row' => $excelRow['excel_row'], 'name' => $name, 'mobile' => $mobile, 'reason' => 'Excel中同一员工重复出现');
				continue;
			}
			$employeeRowMap[$employeeId] = 1;

			$earningTotal = 0;
			$deductionTotal = 0;
			$itemValues = array();
			$summary = array('earning' => null, 'deduction' => null, 'net' => null);
			foreach ($importProjects as $item) {
				$header = $item['header'];
				$project = $item['project'];
				$valueInfo = isset($excelRow['values'][$header['key']]) ? $excelRow['values'][$header['key']] : array('raw' => '');
				$amount = $this->parseAmount($valueInfo['raw']);
				if ($amount === false) {
					$this->_lastErrors[] = array('row' => $excelRow['excel_row'], 'name' => $name, 'mobile' => $mobile, 'reason' => '工资项目“' . $header['name'] . '”金额格式不正确');
					continue;
				}
				if (intval($project['include_earning'])) {
					$earningTotal += $amount;
				}
				if (intval($project['include_deduction'])) {
					$deductionTotal += $amount;
				}
				$summaryType = $this->summaryType($project['name']);
				if ($summaryType) {
					$summary[$summaryType] = $amount;
				}
				$itemValues[] = array('project' => $project, 'amount' => $amount);
			}
			if ($summary['earning'] !== null) {
				$earningTotal = $summary['earning'];
			}
			if ($summary['deduction'] !== null) {
				$deductionTotal = $summary['deduction'];
			}
			$netAmount = $earningTotal - $deductionTotal;
			if ($summary['net'] !== null) {
				$netAmount = $summary['net'];
			}

			$return[] = array(
				'excel_row' => $excelRow['excel_row'],
				'employee' => $employee,
				'earning_total' => $earningTotal,
				'deduction_total' => $deductionTotal,
				'net_amount' => $netAmount,
				'values' => $itemValues,
			);
		}
		return $return;
	}

	protected function matchEmployee($excelRow, $employees)
	{
		$nameKey = $this->normalizeName($excelRow['name']);
		$mobile = $excelRow['mobile'];
		if (!isset($employees[$nameKey]) || empty($employees[$nameKey])) {
			$this->_lastErrors[] = array('row' => $excelRow['excel_row'], 'name' => $excelRow['name'], 'mobile' => $mobile, 'reason' => '系统中找不到这个姓名的员工');
			return false;
		}
		$matched = $employees[$nameKey];
		if (count($matched) == 1) {
			return $matched[0];
		}
		if ($mobile == '') {
			$this->_lastErrors[] = array('row' => $excelRow['excel_row'], 'name' => $excelRow['name'], 'mobile' => $mobile, 'reason' => '企业内存在重名员工，请填写手机号用于区分');
			return false;
		}
		if (!$this->isMobileLike($mobile)) {
			$this->_lastErrors[] = array('row' => $excelRow['excel_row'], 'name' => $excelRow['name'], 'mobile' => $mobile, 'reason' => '重名员工的手机号格式不正确');
			return false;
		}
		foreach ($matched as $employee) {
			if ($employee['mobile'] == $mobile) {
				return $employee;
			}
		}
		$this->_lastErrors[] = array('row' => $excelRow['excel_row'], 'name' => $excelRow['name'], 'mobile' => $mobile, 'reason' => '姓名重名，但手机号没有匹配到系统员工');
		return false;
	}

	protected function savePayrollRows($companyId, $payrollMonth, $sourceName, $operatorId, $rows)
	{
		if (empty($rows)) {
			$this->_lastErrors[] = array('row' => 0, 'name' => '', 'mobile' => '', 'reason' => '没有可导入的工资数据');
			return false;
		}
		$db = $this->getDB();
		$periodTable = $this->getTableName('payroll_periods');
		$rowTable = $this->getTableName('payroll_employee_rows');
		$valueTable = $this->getTableName('payroll_item_values');
		$slipTable = $this->getTableName('payroll_slips');
		$auditTable = $this->getTableName('salary_payroll_audit_record');
		$now = time();

		$period = $db->query('select * from `' . $periodTable . '` where company_id=' . intval($companyId) . ' and payroll_month="' . addslashes($payrollMonth) . '" limit 1')->fetch();
		if ($period && !in_array($period['status'], array('draft', 'calculated', 'rejected'))) {
			$this->_lastErrors[] = array('row' => 0, 'name' => '', 'mobile' => '', 'reason' => '该月份工资表已经提交审核、审核通过或已发工资条，不能直接覆盖导入');
			return false;
		}

		$employeeCount = count($rows);
		$earningTotal = 0;
		$deductionTotal = 0;
		$netTotal = 0;
		foreach ($rows as $row) {
			$earningTotal += $row['earning_total'];
			$deductionTotal += $row['deduction_total'];
			$netTotal += $row['net_amount'];
		}

		$db->begin();
		try {
			if ($period) {
				$periodId = intval($period['id']);
				$db->execute('delete from `' . $valueTable . '` where company_id=' . intval($companyId) . ' and payroll_period_id=' . $periodId);
				$db->execute('delete from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and payroll_period_id=' . $periodId);
				$db->execute('delete from `' . $slipTable . '` where company_id=' . intval($companyId) . ' and payroll_period_id=' . $periodId);
				$db->execute('delete from `' . $auditTable . '` where company_id=' . intval($companyId) . ' and payroll_period_id=' . $periodId);
				$sql = 'update `' . $periodTable . '` set status="calculated",source_type="excel",source_name="' . addslashes($sourceName) . '",' .
					'employee_count=' . intval($employeeCount) . ',earning_total=' . $this->money($earningTotal) . ',deduction_total=' . $this->money($deductionTotal) . ',net_total=' . $this->money($netTotal) . ',' .
					'generated_by=' . intval($operatorId) . ',submitted_by=NULL,approved_by=NULL,rejected_by=NULL,published_by=NULL,archived_by=NULL,' .
					'generated_at=' . $now . ',calculated_at=' . $now . ',submitted_at=NULL,approved_at=NULL,rejected_at=NULL,published_at=NULL,archived_at=NULL,rejected_reason="",updated_at=' . $now .
					' where id=' . $periodId . ' and company_id=' . intval($companyId);
				$db->execute($sql);
			} else {
				$sql = 'insert into `' . $periodTable . '` (`company_id`,`payroll_month`,`source_type`,`source_name`,`status`,`employee_count`,`earning_total`,`deduction_total`,`net_total`,`generated_by`,`generated_at`,`calculated_at`,`created_at`,`updated_at`) values ' .
					'(' . intval($companyId) . ',"' . addslashes($payrollMonth) . '","excel","' . addslashes($sourceName) . '","calculated",' . intval($employeeCount) . ',' . $this->money($earningTotal) . ',' . $this->money($deductionTotal) . ',' . $this->money($netTotal) . ',' . intval($operatorId) . ',' . $now . ',' . $now . ',' . $now . ',' . $now . ')';
				$db->execute($sql);
				$periodId = intval($db->lastInsertId());
			}

			foreach ($rows as $row) {
				$employee = $row['employee'];
				$rowSql = 'insert into `' . $rowTable . '` (`company_id`,`payroll_period_id`,`employee_id`,`employee_name`,`employee_no`,`department_name`,`position_name`,`salary_structure_id`,`earning_total`,`deduction_total`,`net_amount`,`remark`,`created_at`,`updated_at`) values ' .
					'(' . intval($companyId) . ',' . $periodId . ',' . intval($employee['id']) . ',"' . addslashes($employee['name']) . '","' . addslashes($employee['jobnumber']) . '","' . addslashes($employee['department_name']) . '","",NULL,' . $this->money($row['earning_total']) . ',' . $this->money($row['deduction_total']) . ',' . $this->money($row['net_amount']) . ',"Excel第' . intval($row['excel_row']) . '行导入",' . $now . ',' . $now . ')';
				$db->execute($rowSql);
				$rowId = intval($db->lastInsertId());
				foreach ($row['values'] as $value) {
					$project = $value['project'];
					$valueSql = 'insert into `' . $valueTable . '` (`company_id`,`payroll_period_id`,`payroll_employee_row_id`,`employee_id`,`salary_project_id`,`project_name`,`source_type`,`direction`,`calculation_mode`,`linked_module`,`include_earning`,`include_deduction`,`include_net`,`initial_amount`,`final_amount`,`entry_source`,`remark`,`created_at`,`updated_at`) values ' .
						'(' . intval($companyId) . ',' . $periodId . ',' . $rowId . ',' . intval($employee['id']) . ',' . intval($project['id']) . ',"' . addslashes($project['name']) . '","' . addslashes($project['source_type']) . '","' . addslashes($project['direction']) . '","' . addslashes($project['calculation_mode']) . '","' . addslashes($project['linked_module']) . '",' . intval($project['include_earning']) . ',' . intval($project['include_deduction']) . ',' . intval($project['include_net']) . ',' . $this->money($value['amount']) . ',' . $this->money($value['amount']) . ',"excel","",' . $now . ',' . $now . ')';
					$db->execute($valueSql);
				}
			}
			$db->commit();
			return array('period_id' => $periodId, 'employee_count' => $employeeCount, 'earning_total' => $earningTotal, 'deduction_total' => $deductionTotal, 'net_total' => $netTotal);
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastErrors[] = array('row' => 0, 'name' => '', 'mobile' => '', 'reason' => '保存工资表失败：' . $e->getMessage());
			return false;
		}
	}

	protected function cellString($value)
	{
		if (is_object($value) && method_exists($value, 'getPlainText')) {
			$value = $value->getPlainText();
		}
		return trim((string)$value);
	}

	protected function normalizeHeader($value)
	{
		$value = trim((string)$value);
		$value = str_replace(array(' ', "\t", "\r", "\n", '　', '：', ':'), '', $value);
		return strtolower($value);
	}

	protected function normalizeName($value)
	{
		$value = trim((string)$value);
		return str_replace(array(' ', "\t", "\r", "\n", '　'), '', $value);
	}

	protected function normalizeMobile($value)
	{
		return preg_replace('/\D/', '', (string)$value);
	}

	protected function isMobileLike($value)
	{
		return preg_match('/^1\d{10}$/', (string)$value) ? true : false;
	}

	protected function isNameHeader($key)
	{
		return in_array($key, array('姓名', '员工姓名', '人员姓名', '姓名必填'));
	}

	protected function isMobileHeader($key)
	{
		return in_array($key, array('手机号', '手机号码', '手机', '联系电话', '电话'));
	}

	protected function isIgnoredHeader($key)
	{
		return in_array($key, array('部门', '部门名称', '岗位', '职位', '职务', '备注', '说明', '序号'));
	}

	protected function parseAmount($value)
	{
		$value = trim((string)$value);
		if ($value === '') {
			return 0;
		}
		$value = str_replace(array(',', '，', '￥', '元', ' '), '', $value);
		if (!is_numeric($value)) {
			return false;
		}
		return round(floatval($value), 2);
	}

	protected function guessDirection($name)
	{
		if ($this->summaryType($name)) {
			return 'statistic';
		}
		$deductionWords = array('扣', '税', '社保', '公积金', '罚', '借款', '缺勤', '请假', '迟到', '早退', '代扣');
		foreach ($deductionWords as $word) {
			if (strpos($name, $word) !== false) {
				return 'deduction';
			}
		}
		$statisticWords = array('天数', '工时', '基数', '余额');
		foreach ($statisticWords as $word) {
			if (strpos($name, $word) !== false) {
				return 'statistic';
			}
		}
		return 'earning';
	}

	protected function directionName($direction)
	{
		$map = array('earning' => '收入项', 'deduction' => '扣款项', 'statistic' => '统计项');
		return isset($map[$direction]) ? $map[$direction] : $direction;
	}

	protected function summaryType($name)
	{
		if (strpos($name, '实发') !== false || strpos($name, '实付') !== false) {
			return 'net';
		}
		if (strpos($name, '应发合计') !== false || strpos($name, '应发总计') !== false) {
			return 'earning';
		}
		if (strpos($name, '扣款合计') !== false || strpos($name, '扣款总计') !== false || strpos($name, '应扣合计') !== false) {
			return 'deduction';
		}
		return false;
	}

	protected function money($value)
	{
		return sprintf('%.2f', floatval($value));
	}
}
