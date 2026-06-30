<?php
/**
 * Salary operation logs without salary amount details.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Model\BaseModel;

class SalaryOperationLogModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("salary_operation_log");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new SalaryOperationLogModel();
		}
		return self::$_instance;
	}

	public function ensureTable()
	{
		$table = $this->getSource();
		$sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `company_id` int(10) unsigned NOT NULL,
		  `operator_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `action_code` varchar(50) NOT NULL DEFAULT '',
		  `object_type` varchar(50) NOT NULL DEFAULT '',
		  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `payroll_month` char(7) NOT NULL DEFAULT '',
		  `summary` varchar(500) NOT NULL DEFAULT '',
		  `ip` varchar(45) NOT NULL DEFAULT '',
		  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  KEY `idx_salary_log_company_time` (`company_id`,`created_at`),
		  KEY `idx_salary_log_action_month` (`company_id`,`action_code`,`payroll_month`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8";
		return $this->getDB()->execute($sql);
	}

	public function addLog($companyId, $operatorId, $actionCode, $objectType = '', $objectId = 0, $payrollMonth = '', $summary = '', $ip = '')
	{
		$companyId = intval($companyId);
		if ($companyId <= 0 || trim($actionCode) == '') {
			return false;
		}
		$this->ensureTable();
		if ($ip == '') {
			$ip = Utils::getIP();
		}
		$summary = $this->cleanSummary($summary);
		$item = new SalaryOperationLogModel();
		return $item->save(array(
			'company_id' => $companyId,
			'operator_id' => intval($operatorId),
			'action_code' => substr(trim($actionCode), 0, 50),
			'object_type' => substr(trim($objectType), 0, 50),
			'object_id' => intval($objectId),
			'payroll_month' => preg_match('/^\d{4}\-\d{2}$/', $payrollMonth) ? $payrollMonth : '',
			'summary' => $summary,
			'ip' => substr(trim($ip), 0, 45),
			'created_at' => time(),
		));
	}

	public function getCompanyLogs($companyId, $filters = array(), $page = 1, $pageSize = 30)
	{
		$this->ensureTable();
		$where = $this->buildWhere($companyId, $filters, 'l');
		$page = max(1, intval($page));
		$pageSize = max(10, min(100, intval($pageSize)));
		$offset = ($page - 1) * $pageSize;
		$userTable = $this->getTableName('company_user');
		$sql = 'select l.*,u.name as operator_name from `' . $this->getSource() . '` l ' .
			'left join `' . $userTable . '` u on l.operator_id=u.id and l.company_id=u.company_id ' .
			'where ' . $where . ' order by l.created_at desc,l.id desc limit ' . $offset . ',' . $pageSize;
		$items = $this->getDB()->query($sql)->fetchAll();
		$labels = self::getActionLabels();
		foreach ($items as $key => $item) {
			$items[$key]['action_name'] = isset($labels[$item['action_code']]) ? $labels[$item['action_code']] : $item['action_code'];
			$items[$key]['created_time'] = empty($item['created_at']) ? '-' : date('Y-m-d H:i', intval($item['created_at']));
		}
		return $items;
	}

	public function getCompanyLogCount($companyId, $filters = array())
	{
		$this->ensureTable();
		$where = $this->buildWhere($companyId, $filters);
		$sql = 'select count(*) as num from `' . $this->getSource() . '` where ' . $where;
		$row = $this->getDB()->query($sql)->fetch();
		return $row ? intval($row['num']) : 0;
	}

	protected function buildWhere($companyId, $filters, $alias = '')
	{
		$prefix = $alias == '' ? '' : $alias . '.';
		$where = $prefix . 'company_id=' . intval($companyId);
		if (!empty($filters['action_code'])) {
			$where .= ' and ' . $prefix . 'action_code="' . addslashes(trim($filters['action_code'])) . '"';
		}
		if (!empty($filters['payroll_month']) && preg_match('/^\d{4}\-\d{2}$/', $filters['payroll_month'])) {
			$where .= ' and ' . $prefix . 'payroll_month="' . addslashes(trim($filters['payroll_month'])) . '"';
		}
		return $where;
	}

	protected function cleanSummary($summary)
	{
		$summary = trim(strip_tags($summary));
		$summary = preg_replace('/[\r\n\t]+/', ' ', $summary);
		return substr($summary, 0, 500);
	}

	public static function getActionLabels()
	{
		return array(
			'project_template_save' => '保存通用工资项目',
			'project_custom_save' => '保存自定义工资项目',
			'project_delete' => '停用工资项目',
			'initial_salary_save' => '保存初始工资表',
			'initial_salary_import' => '导入初始工资表',
			'payroll_generate' => '生成工资表',
			'payroll_save' => '保存工资表',
			'payroll_import' => '导入工资表',
			'payroll_submit_review' => '提交工资表审核',
			'payroll_review' => '处理工资表审核',
			'payslip_publish' => '发放工资条',
			'payslip_export' => '导出工资条确认结果',
			'salary_report_export' => '导出薪酬报表',
			'mobile_subordinate_salary_view' => '手机端查看下属薪酬',
			'payroll_archive' => '归档工资表',
			'payroll_restore' => '恢复归档工资表',
			'salary_auth_audit_reviewer_save' => '保存工资表审核人',
			'salary_auth_scope_save' => '保存薪酬查询授权',
		);
	}
}
