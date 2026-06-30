<?php
/**
 * Salary low-sensitive operation logs.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$logTable = $model->getTableName('salary_operation_log');

$db->execute("CREATE TABLE IF NOT EXISTS `{$logTable}` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
