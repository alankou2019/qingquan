<?php
/**
 * Salary project category/property upgrade.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();

$templateTable = $model->getTableName('salary_project_templates');
$projectTable = $model->getTableName('salary_projects');
$structureValueTable = $model->getTableName('employee_salary_structure_values');
$itemValueTable = $model->getTableName('payroll_item_values');

$columns = $db->query("SHOW COLUMNS FROM `{$structureValueTable}` LIKE 'text_value'")->fetchAll();
if (empty($columns)) {
	$db->execute("ALTER TABLE `{$structureValueTable}` ADD `text_value` varchar(500) NOT NULL DEFAULT '' AFTER `default_amount`");
}

$columns = $db->query("SHOW COLUMNS FROM `{$itemValueTable}` LIKE 'text_value'")->fetchAll();
if (empty($columns)) {
	$db->execute("ALTER TABLE `{$itemValueTable}` ADD `text_value` varchar(500) NOT NULL DEFAULT '' AFTER `final_amount`");
}

$db->execute("UPDATE `{$templateTable}` SET `source_type`='number' WHERE `source_type`='fixed'");
$db->execute("UPDATE `{$projectTable}` SET `source_type`='number' WHERE `source_type`='fixed'");
$db->execute("UPDATE `{$itemValueTable}` SET `source_type`='number' WHERE `source_type`='fixed'");
$db->execute("UPDATE `{$templateTable}` SET `calculation_mode`='number' WHERE `calculation_mode` in ('fixed','manual')");
$db->execute("UPDATE `{$projectTable}` SET `calculation_mode`='number' WHERE `calculation_mode` in ('fixed','manual')");
$db->execute("UPDATE `{$itemValueTable}` SET `calculation_mode`='number' WHERE `calculation_mode` in ('fixed','manual')");
$db->execute("UPDATE `{$templateTable}` SET `source_type`='number' WHERE `source_type`='calculated' AND `calculation_mode`='number'");
$db->execute("UPDATE `{$projectTable}` SET `source_type`='number' WHERE `source_type`='calculated' AND `calculation_mode`='number'");
$db->execute("UPDATE `{$itemValueTable}` SET `source_type`='number' WHERE `source_type`='calculated' AND `calculation_mode`='number'");
