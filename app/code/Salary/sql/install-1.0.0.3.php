<?php
/**
 * Salary access permissions and company app platform.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$companyTable = $model->getTableName('company');
$salaryViewRoleTable = $model->getTableName('salary_view_role');

$columns = $db->query("SHOW COLUMNS FROM `{$companyTable}` LIKE 'app_platform'")->fetchAll();
if (empty($columns)) {
	$db->execute("ALTER TABLE `{$companyTable}` ADD `app_platform` varchar(20) NOT NULL DEFAULT 'dingding' AFTER `industry`");
}

$db->execute("CREATE TABLE IF NOT EXISTS `{$salaryViewRoleTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `scope_type` varchar(20) NOT NULL DEFAULT 'department',
  `target_id` int(10) unsigned NOT NULL,
  `can_view_detail` tinyint(1) NOT NULL DEFAULT '1',
  `can_export` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_salary_view_role` (`company_id`,`user_id`,`scope_type`,`target_id`),
  KEY `idx_salary_view_role_user` (`company_id`,`user_id`,`scope_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
