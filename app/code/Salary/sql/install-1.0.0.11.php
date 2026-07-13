<?php
/**
 * Commission archive snapshots.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$archiveTable = $model->getTableName('salary_commission_archives');

$db->execute("CREATE TABLE IF NOT EXISTS `{$archiveTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `commission_period_id` int(10) unsigned NOT NULL,
  `commission_month` char(7) NOT NULL,
  `employee_count` int(10) unsigned NOT NULL DEFAULT '0',
  `matched_count` int(10) unsigned NOT NULL DEFAULT '0',
  `total_amount` decimal(16,2) NOT NULL DEFAULT '0.00',
  `snapshot_data` mediumtext,
  `archived_by` int(10) unsigned NOT NULL DEFAULT '0',
  `archived_at` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted_by` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted_at` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_commission_archive_company` (`company_id`,`commission_month`,`deleted_at`),
  KEY `idx_commission_archive_period` (`company_id`,`commission_period_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
