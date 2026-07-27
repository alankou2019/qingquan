<?php
/**
 * Freeze the salary project definition used by each generated payroll period.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$periodTable = $model->getTableName('payroll_periods');

$columns = $db->query("SHOW COLUMNS FROM `{$periodTable}` LIKE 'project_snapshot'")->fetchAll();
if (empty($columns)) {
	$db->execute("ALTER TABLE `{$periodTable}` ADD `project_snapshot` mediumtext AFTER `source_name`");
}
