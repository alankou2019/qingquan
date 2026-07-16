<?php
/**
 * Company salary projects.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class SalaryProjectModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("salary_projects");
	}

	/**
	 * @return \ScshuxCms\Salary\Model\SalaryProjectModel
	 */
	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new SalaryProjectModel();
		}
		return self::$_instance;
	}

	public static function getSourceTypeLabels()
	{
		return array(
			'fixed' => '数字项',
			'number' => '数字项',
			'text' => '文本项',
			'calculated' => '核算项',
		);
	}

	public static function getSourceTypeOptions()
	{
		return array(
			'number' => '数字项',
			'text' => '文本项',
			'calculated' => '核算项',
		);
	}

	public static function getDirectionLabels()
	{
		return array(
			'earning' => '应发类',
			'deduction' => '应扣类',
			'statistic' => '统计类',
			'data' => '数据类',
			'note' => '说明类',
		);
	}

	public static function getCalculationModeLabels()
	{
		return array(
			'number' => '数字',
			'manual' => '数字',
			'fixed' => '固定金额',
			'text' => '文本',
			'formula' => '核算公式',
			'module' => '模块带入',
		);
	}

	public static function getStatusLabels()
	{
		return array(
			'active' => '启用',
			'inactive' => '停用',
		);
	}

	public static function label($labels, $key)
	{
		return isset($labels[$key]) ? $labels[$key] : $key;
	}

	public static function getFixedSummaryProjects()
	{
		return array(
			array(
				'code' => 'earning_total',
				'name' => '应发总额',
				'direction_label' => '统计类',
				'source_type_label' => '系统固定项',
				'calculation_mode_label' => '所有应发类项目合计',
				'formula_text' => '应发类项目合计',
			),
			array(
				'code' => 'deduction_total',
				'name' => '应扣总额',
				'direction_label' => '统计类',
				'source_type_label' => '系统固定项',
				'calculation_mode_label' => '所有应扣类项目合计',
				'formula_text' => '应扣类项目合计',
			),
			array(
				'code' => 'net_total',
				'name' => '实发总额',
				'direction_label' => '统计类',
				'source_type_label' => '系统固定项',
				'calculation_mode_label' => '应发总额 - 应扣总额',
				'formula_text' => '应发总额 - 应扣总额',
			),
		);
	}

	public static function groupPayrollProjects($projects)
	{
		$groups = array(
			'earning' => array(),
			'deduction' => array(),
			'other' => array(),
		);
		foreach ($projects as $project) {
			if ($project['status'] != 'active' || intval($project['deleted_at']) > 0) {
				continue;
			}
			if ($project['direction'] == 'earning') {
				$groups['earning'][] = $project;
			} elseif ($project['direction'] == 'deduction') {
				$groups['deduction'][] = $project;
			} else {
				$groups['other'][] = $project;
			}
		}
		return $groups;
	}

	public function getCompanyProjects($companyId)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and deleted_at=0 order by sort_order asc,id asc';
		$items = $this->getDB()->query($sql)->fetchAll();
		return $this->formatProjectItems($items);
	}

	public function getCompanyTemplateProjectMap($companyId, $includeDeleted = false)
	{
		$return = array();
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and template_id is not null and template_id>0';
		if (!$includeDeleted) {
			$sql .= ' and deleted_at=0';
		}
		$sql .= ' order by id asc';
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $item) {
			$return[intval($item['template_id'])] = $item;
		}
		return $return;
	}

	public function saveTemplateSelection($companyId, $templateIds)
	{
		$companyId = intval($companyId);
		if ($companyId <= 0) {
			$this->_lastError = '企业不存在';
			return false;
		}
		if (empty($templateIds) || !is_array($templateIds)) {
			$templateIds = array();
		}

		$selected = array();
		foreach ($templateIds as $templateId) {
			$templateId = intval($templateId);
			if ($templateId > 0) {
				$selected[$templateId] = 1;
			}
		}

		$db = $this->getDB();
		$projectTable = $this->getSource();
		$templateTable = $this->getTableName('salary_project_templates');
		$templates = $db->query('select * from `' . $templateTable . '` where status="active" order by sort_order asc,id asc')->fetchAll();
		$now = time();

		if (empty($selected)) {
			$db->execute('update `' . $projectTable . '` set status="inactive",updated_at=' . $now .
				' where company_id=' . $companyId . ' and template_id is not null and template_id>0 and deleted_at=0');
		} else {
			$db->execute('update `' . $projectTable . '` set status="inactive",updated_at=' . $now .
				' where company_id=' . $companyId . ' and template_id is not null and template_id>0 and deleted_at=0 and template_id not in (' . implode(',', array_keys($selected)) . ')');
		}

		foreach ($templates as $template) {
			$templateId = intval($template['id']);
			$project = self::factory()->findFirst('company_id=' . $companyId . ' and template_id=' . $templateId);
			if (isset($selected[$templateId])) {
				if ($project) {
					// Keep the company's edited name, type, formula and defaults.
					$project->save(array('status' => 'active', 'deleted_at' => 0, 'updated_at' => $now));
				} else {
					$data = $this->buildTemplateProjectData($companyId, $template, $now);
					$data['status'] = 'active';
					$data['created_at'] = $now;
					$model = new SalaryProjectModel();
					$model->save($data);
				}
			}
		}
		return true;
	}

	protected function buildTemplateProjectData($companyId, $template, $now)
	{
		return array(
			'company_id' => intval($companyId),
			'template_id' => intval($template['id']),
			'name' => $template['name'],
			'source_type' => self::normalizeSourceType($template['source_type']),
			'direction' => $template['direction'],
			'calculation_mode' => $template['calculation_mode'],
			'linked_module' => $template['linked_module'],
			'formula_text' => '',
			'default_number' => '0.00',
			'default_text' => '',
			'include_earning' => intval($template['include_earning']),
			'include_deduction' => intval($template['include_deduction']),
			'include_net' => intval($template['include_net']),
			'sort_order' => intval($template['sort_order']),
			'status' => 'inactive',
			'deleted_at' => 0,
			'updated_at' => intval($now),
		);
	}

	public function saveCustomProject($companyId, $postData)
	{
		$companyId = intval($companyId);
		$id = isset($postData['id']) ? intval($postData['id']) : 0;
		$templateId = isset($postData['template_id']) ? intval($postData['template_id']) : 0;
		$item = false;
		if ($id > 0) {
			$item = self::factory()->findFirst('id=' . $id . ' and company_id=' . $companyId . ' and deleted_at=0');
			if (!$item) {
				$this->_lastError = '工资项目不存在';
				return false;
			}
			$templateId = intval($item->template_id);
		} elseif ($templateId > 0) {
			$template = $this->getDB()->query('select * from `' . $this->getTableName('salary_project_templates') . '` where id=' . $templateId . ' and status="active" limit 1')->fetch();
			if (!$template) {
				$this->_lastError = '通用工资项目不存在';
				return false;
			}
			$item = self::factory()->findFirst('company_id=' . $companyId . ' and template_id=' . $templateId);
			if ($item) {
				$id = intval($item->id);
			}
		}
		$name = isset($postData['name']) ? trim($postData['name']) : '';
		if ($companyId <= 0 || $name == '') {
			$this->_lastError = '请填写工资项目名称';
			return false;
		}

		$sourceTypes = self::getSourceTypeLabels();
		$directions = self::getDirectionLabels();
		$calculationModes = self::getCalculationModeLabels();
		$statusLabels = self::getStatusLabels();
		$direction = isset($postData['direction']) ? trim($postData['direction']) : 'earning';
		$sourceType = self::normalizeSourceType(isset($postData['source_type']) ? trim($postData['source_type']) : 'number');
		$calculationMode = self::defaultCalculationMode($sourceType, isset($postData['calculation_mode']) ? trim($postData['calculation_mode']) : '');
		$status = isset($postData['status']) ? trim($postData['status']) : 'active';
		if (!isset($directions[$direction]) || !isset($sourceTypes[$sourceType]) || !isset($calculationModes[$calculationMode]) || !isset($statusLabels[$status])) {
			$this->_lastError = '工资项目参数不正确';
			return false;
		}
		$includeFlags = self::getIncludeFlagsByDirection($direction, $sourceType);

		$where = 'company_id=' . $companyId . ' and name="' . addslashes($name) . '" and deleted_at=0';
		if ($id > 0) {
			$where .= ' and id!=' . $id;
		}
		$duplicate = self::factory()->findFirst($where);
		if ($duplicate) {
			$this->_lastError = '同一企业下已经存在相同工资项目';
			return false;
		}

		$now = time();
		$defaultNumber = isset($postData['default_number']) ? $this->formatDefaultNumber($postData['default_number']) : '0.00';
		$defaultText = isset($postData['default_text']) ? trim((string)$postData['default_text']) : '';
		if (function_exists('mb_substr')) {
			$defaultText = mb_substr($defaultText, 0, 500, 'UTF-8');
		} else {
			$defaultText = substr($defaultText, 0, 500);
		}
		if ($sourceType != 'number') {
			$defaultNumber = '0.00';
		}
		if ($sourceType != 'text') {
			$defaultText = '';
		}
		$data = array(
			'company_id' => $companyId,
			'template_id' => $templateId > 0 ? $templateId : null,
			'name' => $name,
			'source_type' => $sourceType,
			'direction' => $direction,
			'calculation_mode' => $calculationMode,
			'linked_module' => isset($postData['linked_module']) ? trim($postData['linked_module']) : 'none',
			'formula_text' => $sourceType == 'calculated' && isset($postData['formula_text']) ? trim($postData['formula_text']) : '',
			'default_number' => $defaultNumber,
			'default_text' => $defaultText,
			'include_earning' => $includeFlags['include_earning'],
			'include_deduction' => $includeFlags['include_deduction'],
			'include_net' => $includeFlags['include_net'],
			'sort_order' => isset($postData['sort_order']) ? intval($postData['sort_order']) : 0,
			'status' => $status,
			'deleted_at' => 0,
			'updated_at' => $now,
		);

		if ($item) {
			return $item->save($data);
		}

		$data['created_at'] = $now;
		$model = new SalaryProjectModel();
		return $model->save($data);
	}

	protected function formatDefaultNumber($value)
	{
		$value = str_replace(array(',', '，', '￥', '元', ' '), '', (string)$value);
		if (!is_numeric($value)) {
			$value = 0;
		}
		return sprintf('%.2f', round(floatval($value), 2));
	}

	public static function normalizeSourceType($sourceType)
	{
		$sourceType = trim((string)$sourceType);
		if ($sourceType == 'fixed' || $sourceType == 'manual') {
			return 'number';
		}
		if (!in_array($sourceType, array('number', 'text', 'calculated'))) {
			return 'number';
		}
		return $sourceType;
	}

	public static function defaultCalculationMode($sourceType, $currentMode = '')
	{
		if ($sourceType == 'text') {
			return 'text';
		}
		if ($sourceType == 'calculated') {
			return 'formula';
		}
		if (in_array($currentMode, array('module', 'formula'))) {
			return $currentMode;
		}
		return 'number';
	}

	public static function getIncludeFlagsByDirection($direction, $sourceType = 'number')
	{
		if ($sourceType == 'text') {
			return array('include_earning' => 0, 'include_deduction' => 0, 'include_net' => 0);
		}
		if ($direction == 'earning') {
			return array('include_earning' => 1, 'include_deduction' => 0, 'include_net' => 1);
		}
		if ($direction == 'deduction') {
			return array('include_earning' => 0, 'include_deduction' => 1, 'include_net' => 1);
		}
		return array('include_earning' => 0, 'include_deduction' => 0, 'include_net' => 0);
	}

	public static function isTextProject($project)
	{
		$sourceType = isset($project['source_type']) ? self::normalizeSourceType($project['source_type']) : 'number';
		$direction = isset($project['direction']) ? $project['direction'] : '';
		$calculationMode = isset($project['calculation_mode']) ? $project['calculation_mode'] : '';
		return $sourceType == 'text' || $direction == 'note' || $calculationMode == 'text';
	}

	public static function isFormulaProject($project)
	{
		return isset($project['calculation_mode']) && $project['calculation_mode'] == 'formula';
	}

	public function deleteCompanyProject($companyId, $projectId)
	{
		$item = self::factory()->findFirst('id=' . intval($projectId) . ' and company_id=' . intval($companyId) . ' and deleted_at=0');
		if (!$item) {
			$this->_lastError = '工资项目不存在';
			return false;
		}
		return $item->save(array(
			'status' => 'inactive',
			'deleted_at' => time(),
			'updated_at' => time(),
		));
	}

	public function deleteCompanyTemplateProject($companyId, $templateId)
	{
		$companyId = intval($companyId);
		$templateId = intval($templateId);
		$template = $this->getDB()->query('select * from `' . $this->getTableName('salary_project_templates') . '` where id=' . $templateId . ' and status="active" limit 1')->fetch();
		if ($companyId <= 0 || !$template) {
			$this->_lastError = '通用工资项目不存在';
			return false;
		}
		$now = time();
		$item = self::factory()->findFirst('company_id=' . $companyId . ' and template_id=' . $templateId);
		if ($item) {
			return $item->save(array('status' => 'inactive', 'deleted_at' => $now, 'updated_at' => $now));
		}
		$data = $this->buildTemplateProjectData($companyId, $template, $now);
		$data['deleted_at'] = $now;
		$data['created_at'] = $now;
		$model = new SalaryProjectModel();
		return $model->save($data);
	}

	public function disableCompanyProject($companyId, $projectId)
	{
		$item = self::factory()->findFirst('id=' . intval($projectId) . ' and company_id=' . intval($companyId) . ' and deleted_at=0');
		if (!$item) {
			$this->_lastError = '工资项目不存在';
			return false;
		}
		return $item->save(array(
			'status' => 'inactive',
			'updated_at' => time(),
		));
	}

	public function formatProjectItems($items)
	{
		$sourceTypes = self::getSourceTypeLabels();
		$directions = self::getDirectionLabels();
		$calculationModes = self::getCalculationModeLabels();
		$statusLabels = self::getStatusLabels();
		foreach ($items as $key => $item) {
			if (!isset($item['default_number'])) {
				$item['default_number'] = '0.00';
			}
			if (!isset($item['default_text'])) {
				$item['default_text'] = '';
			}
			$sourceType = self::normalizeSourceType($item['source_type']);
			$item['source_type_label'] = self::label($sourceTypes, $item['source_type']);
			$item['direction_label'] = self::label($directions, $item['direction']);
			$item['calculation_mode_label'] = self::label($calculationModes, $item['calculation_mode']);
			if ($sourceType == 'text') {
				$item['calculation_mode_label'] = '文本';
			} elseif ($item['calculation_mode'] == 'formula') {
				$item['calculation_mode_label'] = '核算公式';
			} elseif (!in_array($item['calculation_mode'], array('module'))) {
				$item['calculation_mode_label'] = '数字';
			}
			$item['source_type_option'] = $sourceType;
			$item['is_text_project'] = self::isTextProject($item) ? 1 : 0;
			$item['is_formula_project'] = self::isFormulaProject($item) ? 1 : 0;
			$item['status_label'] = self::label($statusLabels, $item['status']);
			$item['project_kind'] = empty($item['template_id']) ? 'custom' : 'common';
			$item['project_kind_label'] = empty($item['template_id']) ? '自定义项目' : '通用项目';
			$items[$key] = $item;
		}
		return $items;
	}
}
