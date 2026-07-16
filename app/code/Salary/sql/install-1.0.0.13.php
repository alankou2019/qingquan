<?php
/**
 * Company salary project defaults and editable common project state.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$projectTable = $model->getTableName('salary_projects');

$columns = $db->query("SHOW COLUMNS FROM `{$projectTable}` LIKE 'default_number'")->fetchAll();
if (empty($columns)) {
	$db->execute("ALTER TABLE `{$projectTable}` ADD `default_number` decimal(14,2) NOT NULL DEFAULT '0.00' AFTER `formula_text`");
}

$columns = $db->query("SHOW COLUMNS FROM `{$projectTable}` LIKE 'default_text'")->fetchAll();
if (empty($columns)) {
	$db->execute("ALTER TABLE `{$projectTable}` ADD `default_text` varchar(500) NOT NULL DEFAULT '' AFTER `default_number`");
}

// Older versions used deleted_at when a common project was merely unchecked.
$db->execute("UPDATE `{$projectTable}` SET `status`='inactive',`deleted_at`=0 WHERE `template_id` IS NOT NULL AND `template_id`>0 AND `deleted_at`>0");
