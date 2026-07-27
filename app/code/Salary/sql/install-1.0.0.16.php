<?php
/**
 * Store multiple department, position and employee selections for a commission project.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$projectTable = $model->getTableName('salary_commission_projects');
$column = $db->query("SHOW COLUMNS FROM `{$projectTable}` LIKE 'scope_config'")->fetch();
if (!$column) {
	$db->execute("ALTER TABLE `{$projectTable}` ADD `scope_config` text NOT NULL AFTER `scope_label`");
}
