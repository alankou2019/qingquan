<?php
/**
 * Miniapp tenant identity fields.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$companyTable = $model->getTableName('company');

$definitions = array(
	'company_code' => "varchar(16) DEFAULT NULL AFTER `app_platform`",
	'miniapp_admin_name' => "varchar(64) NOT NULL DEFAULT '' AFTER `company_code`",
	'miniapp_admin_mobile' => "varchar(20) NOT NULL DEFAULT '' AFTER `miniapp_admin_name`",
	'miniapp_admin_user_id' => "int(10) unsigned NOT NULL DEFAULT '0' AFTER `miniapp_admin_mobile`",
	'miniapp_admin_employee_id' => "int(10) unsigned NOT NULL DEFAULT '0' AFTER `miniapp_admin_user_id`",
	'registration_source' => "varchar(20) NOT NULL DEFAULT 'admin' AFTER `miniapp_admin_employee_id`",
	'miniapp_sync_status' => "varchar(20) NOT NULL DEFAULT 'pending' AFTER `registration_source`",
	'miniapp_sync_error' => "varchar(255) NOT NULL DEFAULT '' AFTER `miniapp_sync_status`",
	'miniapp_synced_at' => "int(10) unsigned NOT NULL DEFAULT '0' AFTER `miniapp_sync_error`"
);
foreach ($definitions as $column => $definition) {
	$columns = $db->query("SHOW COLUMNS FROM `{$companyTable}` LIKE '{$column}'")->fetchAll();
	if (empty($columns)) {
		$db->execute("ALTER TABLE `{$companyTable}` ADD `{$column}` {$definition}");
	}
}

$indexes = $db->query("SHOW INDEX FROM `{$companyTable}` WHERE Key_name='uk_company_code'")->fetchAll();
if (empty($indexes)) {
	$db->execute("ALTER TABLE `{$companyTable}` ADD UNIQUE KEY `uk_company_code` (`company_code`)");
}

$indexes = $db->query("SHOW INDEX FROM `{$companyTable}` WHERE Key_name='idx_miniapp_admin_mobile'")->fetchAll();
if (empty($indexes)) {
	$db->execute("ALTER TABLE `{$companyTable}` ADD KEY `idx_miniapp_admin_mobile` (`miniapp_admin_mobile`)");
}
$registrationTable = $model->getTableName('miniapp_registration_application');
$db->execute("CREATE TABLE IF NOT EXISTS `{$registrationTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(120) NOT NULL DEFAULT '',
  `industry` varchar(120) NOT NULL DEFAULT '',
  `contact_name` varchar(64) NOT NULL DEFAULT '',
  `admin_mobile` varchar(20) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `person_limit` int(10) unsigned NOT NULL DEFAULT '20',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `review_remark` varchar(255) NOT NULL DEFAULT '',
  `company_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  `reviewed_at` int(10) unsigned NOT NULL DEFAULT '0',
  `reviewed_by` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_miniapp_registration_status` (`status`,`created_at`),
  KEY `idx_miniapp_registration_mobile` (`admin_mobile`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");