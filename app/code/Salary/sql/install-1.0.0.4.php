<?php
/**
 * Payroll period source fields.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$periodTable = $model->getTableName('payroll_periods');

$columns = $db->query("SHOW COLUMNS FROM `{$periodTable}` LIKE 'source_type'")->fetchAll();
if (empty($columns)) {
	$db->execute("ALTER TABLE `{$periodTable}` ADD `source_type` varchar(20) NOT NULL DEFAULT 'system' AFTER `payroll_month`");
}

$columns = $db->query("SHOW COLUMNS FROM `{$periodTable}` LIKE 'source_name'")->fetchAll();
if (empty($columns)) {
	$db->execute("ALTER TABLE `{$periodTable}` ADD `source_name` varchar(120) NOT NULL DEFAULT '' AFTER `source_type`");
}
