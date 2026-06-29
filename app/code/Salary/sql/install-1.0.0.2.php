<?php
/**
 * Company module authorization table.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$authTable = $model->getTableName('company_module_auth');

$db->execute("CREATE TABLE IF NOT EXISTS `{$authTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `module_code` varchar(50) NOT NULL DEFAULT '',
  `feature_code` varchar(50) NOT NULL DEFAULT '',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_by` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_company_module_feature` (`company_id`,`module_code`,`feature_code`),
  KEY `idx_module_enabled` (`module_code`,`feature_code`,`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
