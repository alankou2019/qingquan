<?php
/**
 * Commission project settings.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$projectTable = $model->getTableName('salary_commission_projects');

$db->execute("CREATE TABLE IF NOT EXISTS `{$projectTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `name` varchar(80) NOT NULL,
  `metric_type` varchar(30) NOT NULL DEFAULT 'sales_amount',
  `metric_name` varchar(80) NOT NULL DEFAULT '',
  `commission_mode` varchar(30) NOT NULL DEFAULT 'simple',
  `threshold_value` decimal(14,2) NOT NULL DEFAULT '0.00',
  `rule_detail` text,
  `scope_type` varchar(20) NOT NULL DEFAULT 'all',
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
