<?php
/**
 * Payroll archive snapshots.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$archiveTable = $model->getTableName('salary_payroll_archives');

$db->execute("CREATE TABLE IF NOT EXISTS `{$archiveTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `payroll_period_id` int(10) unsigned NOT NULL,
  `payroll_month` char(7) NOT NULL,
  `source_label` varchar(120) NOT NULL DEFAULT '',
  `employee_count` int(10) unsigned NOT NULL DEFAULT '0',
  `earning_total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `deduction_total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `net_total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `snapshot_data` mediumtext,
  `archived_by` int(10) unsigned NOT NULL DEFAULT '0',
  `archived_at` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_salary_payroll_archive` (`company_id`,`payroll_month`,`archived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
