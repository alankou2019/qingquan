<?php
/**
 * Retire obsolete common salary projects and refresh default labels.
 */
use ScshuxCms\Core\Model\BaseModel;

$model = new BaseModel();
$db = $model->getDB();
$templateTable = $model->getTableName('salary_project_templates');
$projectTable = $model->getTableName('salary_projects');
$now = time();

$db->execute("UPDATE `{$templateTable}` SET `status`='inactive',`updated_at`={$now} " .
	"WHERE `code` IN ('fixed_allowance','fixed_deduction') OR `code` LIKE 'temp_default_%' OR `name` LIKE 'TEMP_COMMON_%'");

$renames = array(
	'commission' => array('提成', '提成奖'),
	'bonus' => array('奖金', '绩效奖'),
	'leave_deduction' => array('请假扣款', '缺勤扣款'),
	'individual_tax' => array('个税', '个人所得税'),
);

foreach ($renames as $code => $names) {
	$template = $db->query("SELECT `id` FROM `{$templateTable}` WHERE `code`='" . addslashes($code) . "' LIMIT 1")->fetch();
	if (!$template) {
		continue;
	}
	$templateId = intval($template['id']);
	$db->execute("UPDATE `{$templateTable}` SET `name`='" . addslashes($names[1]) . "',`updated_at`={$now} WHERE `id`={$templateId}");
	$db->execute("UPDATE `{$projectTable}` SET `name`='" . addslashes($names[1]) . "',`updated_at`={$now} " .
		"WHERE `template_id`={$templateId} AND `name`='" . addslashes($names[0]) . "'");
}
