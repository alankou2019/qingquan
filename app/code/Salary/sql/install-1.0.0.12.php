<?php
/**
 * Commission income estimate records.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$table = $model->getTableName('salary_commission_estimates');

$db->execute("CREATE TABLE IF NOT EXISTS `{$table}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `employee_name` varchar(80) NOT NULL,
  `department_name` varchar(100) NOT NULL DEFAULT '',
  `position_name` varchar(100) NOT NULL DEFAULT '',
  `base_salary` decimal(14,2) NOT NULL DEFAULT '0.00',
  `low_commission` decimal(14,2) NOT NULL DEFAULT '0.00',
  `mid_commission` decimal(14,2) NOT NULL DEFAULT '0.00',
  `high_commission` decimal(14,2) NOT NULL DEFAULT '0.00',
  `low_income` decimal(14,2) NOT NULL DEFAULT '0.00',
  `mid_income` decimal(14,2) NOT NULL DEFAULT '0.00',
  `high_income` decimal(14,2) NOT NULL DEFAULT '0.00',
  `snapshot_data` mediumtext NOT NULL,
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted_by` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_commission_estimate_company` (`company_id`,`deleted_at`,`created_at`),
  KEY `idx_commission_estimate_employee` (`company_id`,`employee_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
