<?php
/**
 * Commission calculation tables.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();

$projectTable = $model->getTableName('salary_commission_projects');
$periodTable = $model->getTableName('salary_commission_periods');
$rowTable = $model->getTableName('salary_commission_rows');
$valueTable = $model->getTableName('salary_commission_item_values');

$db->execute("CREATE TABLE IF NOT EXISTS `{$projectTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `name` varchar(80) NOT NULL,
  `metric_type` varchar(30) NOT NULL DEFAULT 'sales_amount',
  `metric_name` varchar(80) NOT NULL,
  `commission_mode` varchar(30) NOT NULL DEFAULT 'simple',
  `threshold_value` decimal(14,2) NOT NULL DEFAULT '0.00',
  `rate_type` varchar(20) NOT NULL DEFAULT 'percent',
  `rate_value` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `tier_config` text NOT NULL,
  `rule_detail` text NOT NULL,
  `scope_type` varchar(30) NOT NULL DEFAULT 'all',
  `scope_value` varchar(255) NOT NULL DEFAULT '',
  `scope_label` varchar(255) NOT NULL DEFAULT '',
  `priority` int(11) NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_commission_project_company` (`company_id`,`status`,`deleted_at`),
  KEY `idx_commission_project_scope` (`company_id`,`scope_type`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$columns = array(
	'rate_type' => "ALTER TABLE `{$projectTable}` ADD `rate_type` varchar(20) NOT NULL DEFAULT 'percent' AFTER `threshold_value`",
	'rate_value' => "ALTER TABLE `{$projectTable}` ADD `rate_value` decimal(12,4) NOT NULL DEFAULT '0.0000' AFTER `rate_type`",
	'tier_config' => "ALTER TABLE `{$projectTable}` ADD `tier_config` text NOT NULL AFTER `rate_value`",
);
foreach ($columns as $column => $sql) {
	$item = $db->query("SHOW COLUMNS FROM `{$projectTable}` LIKE '" . addslashes($column) . "'")->fetch();
	if (!$item) {
		$db->execute($sql);
	}
}

$db->execute("CREATE TABLE IF NOT EXISTS `{$periodTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `commission_month` char(7) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `employee_count` int(10) unsigned NOT NULL DEFAULT '0',
  `matched_count` int(10) unsigned NOT NULL DEFAULT '0',
  `total_amount` decimal(16,2) NOT NULL DEFAULT '0.00',
  `generated_by` int(10) unsigned NOT NULL DEFAULT '0',
  `generated_at` int(10) unsigned NOT NULL DEFAULT '0',
  `calculated_at` int(10) unsigned DEFAULT NULL,
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_salary_commission_month` (`company_id`,`commission_month`),
  KEY `idx_salary_commission_status` (`company_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->execute("CREATE TABLE IF NOT EXISTS `{$rowTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `commission_period_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `employee_name` varchar(80) NOT NULL,
  `employee_no` varchar(50) NOT NULL DEFAULT '',
  `department_id` varchar(50) NOT NULL DEFAULT '',
  `department_name` varchar(100) NOT NULL DEFAULT '',
  `position_name` varchar(100) NOT NULL DEFAULT '',
  `matched_project_count` int(10) unsigned NOT NULL DEFAULT '0',
  `total_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_commission_period_employee` (`commission_period_id`,`employee_id`),
  KEY `idx_commission_rows_company` (`company_id`,`commission_period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->execute("CREATE TABLE IF NOT EXISTS `{$valueTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `commission_period_id` int(10) unsigned NOT NULL,
  `commission_row_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `commission_project_id` int(10) unsigned NOT NULL,
  `project_name` varchar(80) NOT NULL,
  `metric_type` varchar(30) NOT NULL DEFAULT '',
  `metric_name` varchar(80) NOT NULL DEFAULT '',
  `commission_mode` varchar(30) NOT NULL DEFAULT '',
  `scope_type` varchar(30) NOT NULL DEFAULT '',
  `scope_value` varchar(255) NOT NULL DEFAULT '',
  `scope_label` varchar(255) NOT NULL DEFAULT '',
  `input_value` decimal(14,2) NOT NULL DEFAULT '0.00',
  `commission_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `rule_snapshot` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_commission_row_project` (`commission_row_id`,`commission_project_id`),
  KEY `idx_commission_values_period` (`company_id`,`commission_period_id`),
  KEY `idx_commission_values_employee` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
