<?php
/**
 * Salary module tables and menu.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();

$templateTable = $model->getTableName('salary_project_templates');
$projectTable = $model->getTableName('salary_projects');
$structureTable = $model->getTableName('employee_salary_structures');
$structureValueTable = $model->getTableName('employee_salary_structure_values');
$periodTable = $model->getTableName('payroll_periods');
$rowTable = $model->getTableName('payroll_employee_rows');
$itemValueTable = $model->getTableName('payroll_item_values');
$slipTable = $model->getTableName('payroll_slips');

$db->execute("CREATE TABLE IF NOT EXISTS `{$templateTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(80) NOT NULL,
  `source_type` varchar(20) NOT NULL DEFAULT 'number',
  `direction` varchar(20) NOT NULL DEFAULT 'earning',
  `calculation_mode` varchar(20) NOT NULL DEFAULT 'manual',
  `linked_module` varchar(30) NOT NULL DEFAULT 'none',
  `include_earning` tinyint(1) NOT NULL DEFAULT '0',
  `include_deduction` tinyint(1) NOT NULL DEFAULT '0',
  `include_net` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_salary_template_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->execute("CREATE TABLE IF NOT EXISTS `{$projectTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(80) NOT NULL,
  `source_type` varchar(20) NOT NULL DEFAULT 'number',
  `direction` varchar(20) NOT NULL DEFAULT 'earning',
  `calculation_mode` varchar(20) NOT NULL DEFAULT 'manual',
  `linked_module` varchar(30) NOT NULL DEFAULT 'none',
  `formula_text` varchar(500) NOT NULL DEFAULT '',
  `include_earning` tinyint(1) NOT NULL DEFAULT '0',
  `include_deduction` tinyint(1) NOT NULL DEFAULT '0',
  `include_net` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_salary_projects_company` (`company_id`,`status`,`deleted_at`),
  KEY `idx_salary_projects_template` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->execute("CREATE TABLE IF NOT EXISTS `{$structureTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `version_no` int(10) unsigned NOT NULL,
  `effective_date` date NOT NULL,
  `expires_at` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_employee_salary_version` (`company_id`,`employee_id`,`version_no`),
  KEY `idx_employee_salary_active` (`company_id`,`employee_id`,`status`,`effective_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->execute("CREATE TABLE IF NOT EXISTS `{$structureValueTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `structure_id` int(10) unsigned NOT NULL,
  `salary_project_id` int(10) unsigned NOT NULL,
  `default_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `text_value` varchar(500) NOT NULL DEFAULT '',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_structure_project` (`structure_id`,`salary_project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->execute("CREATE TABLE IF NOT EXISTS `{$periodTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `payroll_month` char(7) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `employee_count` int(10) unsigned NOT NULL DEFAULT '0',
  `earning_total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `deduction_total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `net_total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `generated_by` int(10) unsigned NOT NULL DEFAULT '0',
  `submitted_by` int(10) unsigned DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `rejected_by` int(10) unsigned DEFAULT NULL,
  `published_by` int(10) unsigned DEFAULT NULL,
  `archived_by` int(10) unsigned DEFAULT NULL,
  `generated_at` int(10) unsigned NOT NULL DEFAULT '0',
  `calculated_at` int(10) unsigned DEFAULT NULL,
  `submitted_at` int(10) unsigned DEFAULT NULL,
  `approved_at` int(10) unsigned DEFAULT NULL,
  `rejected_at` int(10) unsigned DEFAULT NULL,
  `published_at` int(10) unsigned DEFAULT NULL,
  `archived_at` int(10) unsigned DEFAULT NULL,
  `rejected_reason` varchar(500) NOT NULL DEFAULT '',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payroll_company_month` (`company_id`,`payroll_month`),
  KEY `idx_payroll_period_status` (`company_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->execute("CREATE TABLE IF NOT EXISTS `{$rowTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `payroll_period_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `employee_name` varchar(80) NOT NULL,
  `employee_no` varchar(50) NOT NULL DEFAULT '',
  `department_name` varchar(100) NOT NULL DEFAULT '',
  `position_name` varchar(100) NOT NULL DEFAULT '',
  `salary_structure_id` int(10) unsigned DEFAULT NULL,
  `earning_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `deduction_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payroll_period_employee` (`payroll_period_id`,`employee_id`),
  KEY `idx_payroll_rows_company` (`company_id`,`payroll_period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->execute("CREATE TABLE IF NOT EXISTS `{$itemValueTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `payroll_period_id` int(10) unsigned NOT NULL,
  `payroll_employee_row_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `salary_project_id` int(10) unsigned NOT NULL,
  `project_name` varchar(80) NOT NULL,
  `source_type` varchar(20) NOT NULL,
  `direction` varchar(20) NOT NULL,
  `calculation_mode` varchar(20) NOT NULL DEFAULT 'manual',
  `linked_module` varchar(30) NOT NULL DEFAULT 'none',
  `include_earning` tinyint(1) NOT NULL DEFAULT '0',
  `include_deduction` tinyint(1) NOT NULL DEFAULT '0',
  `include_net` tinyint(1) NOT NULL DEFAULT '1',
  `initial_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `final_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `text_value` varchar(500) NOT NULL DEFAULT '',
  `entry_source` varchar(20) NOT NULL DEFAULT 'generated',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payroll_row_project` (`payroll_employee_row_id`,`salary_project_id`),
  KEY `idx_payroll_values_period` (`company_id`,`payroll_period_id`),
  KEY `idx_payroll_values_employee` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->execute("CREATE TABLE IF NOT EXISTS `{$slipTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `payroll_period_id` int(10) unsigned NOT NULL,
  `payroll_employee_row_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `version_no` int(10) unsigned NOT NULL DEFAULT '1',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `published_at` int(10) unsigned NOT NULL DEFAULT '0',
  `viewed_at` int(10) unsigned DEFAULT NULL,
  `confirmed_at` int(10) unsigned DEFAULT NULL,
  `confirmed_ip` varchar(64) NOT NULL DEFAULT '',
  `confirmed_user_agent` varchar(255) NOT NULL DEFAULT '',
  `disputed_at` int(10) unsigned DEFAULT NULL,
  `dispute_reason` varchar(500) NOT NULL DEFAULT '',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payslip_period_employee` (`payroll_period_id`,`employee_id`),
  KEY `idx_payslip_company_period` (`company_id`,`payroll_period_id`),
  KEY `idx_payslip_employee` (`employee_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$now = time();
$templates = array(
	array('base_salary', '基本工资', 'number', 'earning', 'number', 'none', 1, 0, 1, 10),
	array('position_salary', '岗位工资', 'number', 'earning', 'number', 'none', 1, 0, 1, 20),
	array('fixed_allowance', '固定补贴', 'number', 'earning', 'number', 'none', 1, 0, 1, 30),
	array('fixed_deduction', '固定扣款', 'number', 'deduction', 'number', 'none', 0, 1, 1, 40),
	array('performance_salary', '绩效工资', 'calculated', 'earning', 'module', 'performance', 1, 0, 1, 50),
	array('commission', '提成', 'calculated', 'earning', 'module', 'commission', 1, 0, 1, 60),
	array('bonus', '奖金', 'number', 'earning', 'number', 'none', 1, 0, 1, 70),
	array('overtime_pay', '加班费', 'number', 'earning', 'number', 'none', 1, 0, 1, 80),
	array('leave_deduction', '请假扣款', 'number', 'deduction', 'number', 'none', 0, 1, 1, 90),
	array('social_security', '社保个人部分', 'number', 'deduction', 'number', 'none', 0, 1, 1, 100),
	array('housing_fund', '公积金个人部分', 'number', 'deduction', 'number', 'none', 0, 1, 1, 110),
	array('individual_tax', '个税', 'number', 'deduction', 'number', 'none', 0, 1, 1, 120),
);

foreach ($templates as $template) {
	$sql = "INSERT INTO `{$templateTable}` (`code`,`name`,`source_type`,`direction`,`calculation_mode`,`linked_module`,`include_earning`,`include_deduction`,`include_net`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES " .
		"('" . addslashes($template[0]) . "','" . addslashes($template[1]) . "','" . $template[2] . "','" . $template[3] . "','" . $template[4] . "','" . $template[5] . "'," . intval($template[6]) . "," . intval($template[7]) . "," . intval($template[8]) . "," . intval($template[9]) . ",'active'," . $now . "," . $now . ") " .
		"ON DUPLICATE KEY UPDATE `name`=VALUES(`name`),`source_type`=VALUES(`source_type`),`direction`=VALUES(`direction`),`calculation_mode`=VALUES(`calculation_mode`),`linked_module`=VALUES(`linked_module`),`include_earning`=VALUES(`include_earning`),`include_deduction`=VALUES(`include_deduction`),`include_net`=VALUES(`include_net`),`sort_order`=VALUES(`sort_order`),`status`='active',`updated_at`=" . $now;
	$db->execute($sql);
}

$menuTable = $model->getTableName('menus');
$parent = $db->query("SELECT `id` FROM `{$menuTable}` WHERE `name`='薪酬管理' LIMIT 1")->fetch();
if (empty($parent)) {
	$db->execute("INSERT INTO `{$menuTable}` (`name`,`parent_id`,`new_window`,`sort`,`link`) VALUES ('薪酬管理',0,0,80,'')");
	$parentId = $db->lastInsertId();
} else {
	$parentId = intval($parent['id']);
}

$projectMenu = $db->query("SELECT `id` FROM `{$menuTable}` WHERE `link`='/admin/salaryproject/index' LIMIT 1")->fetch();
if (empty($projectMenu)) {
	$db->execute("INSERT INTO `{$menuTable}` (`name`,`parent_id`,`new_window`,`sort`,`link`) VALUES ('工资项目'," . $parentId . ",0,10,'/admin/salaryproject/index')");
}
