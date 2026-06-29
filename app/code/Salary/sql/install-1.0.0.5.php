<?php
/**
 * Payroll audit roles and records.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$roleTable = $model->getTableName('salary_payroll_audit_role');
$recordTable = $model->getTableName('salary_payroll_audit_record');

$db->execute("CREATE TABLE IF NOT EXISTS `{$roleTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `reviewer_id` int(10) unsigned NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_salary_payroll_audit_role` (`company_id`,`reviewer_id`),
  KEY `idx_salary_payroll_audit_role` (`company_id`,`status`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->execute("CREATE TABLE IF NOT EXISTS `{$recordTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `payroll_period_id` int(10) unsigned NOT NULL,
  `reviewer_id` int(10) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `opinion` varchar(500) NOT NULL DEFAULT '',
  `reviewed_at` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_salary_payroll_audit_record` (`company_id`,`payroll_period_id`,`reviewer_id`),
  KEY `idx_salary_payroll_audit_record` (`company_id`,`payroll_period_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
