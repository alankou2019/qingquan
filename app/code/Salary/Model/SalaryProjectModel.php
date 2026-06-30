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
			'fixed' => '固定项目',
			'calculated' => '核算项目',
		);
	}

	public static function getDirectionLabels()
	{
		return array(
			'earning' => '收入项',
			'deduction' => '扣款项',
			'statistic' => '统计项',
		);
	}

	public static function getCalculationModeLabels()
	{
		return array(
			'number' => '数字',
			'manual' => '数字',
			'fixed' => '固定金额',
			'formula' => '公式计算',
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

	public function getCompanyProjects($companyId)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and deleted_at=0 order by sort_order asc,id asc';
		$items = $this->getDB()->query($sql)->fetchAll();
		return $this->formatProjectItems($items);
	}

	public function getCompanyTemplateProjectMap($companyId)
	{
		$return = array();
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and template_id is not null and template_id>0 and deleted_at=0';
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
			$db->execute('update `' . $projectTable . '` set status="inactive",deleted_at=' . $now . ',updated_at=' . $now .
				' where company_id=' . $companyId . ' and template_id is not null and template_id>0 and deleted_at=0');
		} else {
			$db->execute('update `' . $projectTable . '` set status="inactive",deleted_at=' . $now . ',updated_at=' . $now .
				' where company_id=' . $companyId . ' and template_id is not null and template_id>0 and deleted_at=0 and template_id not in (' . implode(',', array_keys($selected)) . ')');
		}

		foreach ($templates as $template) {
			$templateId = intval($template['id']);
			$project = self::factory()->findFirst('company_id=' . $companyId . ' and template_id=' . $templateId);
			if (isset($selected[$templateId])) {
				$data = array(
					'company_id' => $companyId,
					'template_id' => $templateId,
					'name' => $template['name'],
					'source_type' => $template['source_type'],
					'direction' => $template['direction'],
					'calculation_mode' => $template['calculation_mode'],
					'linked_module' => $template['linked_module'],
					'formula_text' => '',
					'include_earning' => intval($template['include_earning']),
					'include_deduction' => intval($template['include_deduction']),
					'include_net' => intval($template['include_net']),
					'sort_order' => intval($template['sort_order']),
					'status' => 'active',
					'deleted_at' => 0,
					'updated_at' => $now,
				);
				if ($project) {
					$project->save($data);
				} else {
					$data['created_at'] = $now;
					$model = new SalaryProjectModel();
					$model->save($data);
				}
			}
		}
		return true;
	}

	public function saveCustomProject($companyId, $postData)
	{
		$companyId = intval($companyId);
		$id = isset($postData['id']) ? intval($postData['id']) : 0;
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
		$sourceType = isset($postData['source_type']) ? trim($postData['source_type']) : 'calculated';
		$calculationMode = isset($postData['calculation_mode']) ? trim($postData['calculation_mode']) : 'number';
		$status = isset($postData['status']) ? trim($postData['status']) : 'active';
		if (!isset($directions[$direction]) || !isset($sourceTypes[$sourceType]) || !isset($calculationModes[$calculationMode]) || !isset($statusLabels[$status])) {
			$this->_lastError = '工资项目参数不正确';
			return false;
		}

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
		$data = array(
			'company_id' => $companyId,
			'template_id' => null,
			'name' => $name,
			'source_type' => $sourceType,
			'direction' => $direction,
			'calculation_mode' => $calculationMode,
			'linked_module' => isset($postData['linked_module']) ? trim($postData['linked_module']) : 'none',
			'formula_text' => isset($postData['formula_text']) ? trim($postData['formula_text']) : '',
			'include_earning' => isset($postData['include_earning']) ? intval($postData['include_earning']) : 0,
			'include_deduction' => isset($postData['include_deduction']) ? intval($postData['include_deduction']) : 0,
			'include_net' => isset($postData['include_net']) ? intval($postData['include_net']) : 1,
			'sort_order' => isset($postData['sort_order']) ? intval($postData['sort_order']) : 0,
			'status' => $status,
			'deleted_at' => 0,
			'updated_at' => $now,
		);

		if ($id > 0) {
			$item = self::factory()->findFirst('id=' . $id . ' and company_id=' . $companyId . ' and deleted_at=0');
			if (!$item) {
				$this->_lastError = '工资项目不存在';
				return false;
			}
			return $item->save($data);
		}

		$data['created_at'] = $now;
		$model = new SalaryProjectModel();
		return $model->save($data);
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

	public function formatProjectItems($items)
	{
		$sourceTypes = self::getSourceTypeLabels();
		$directions = self::getDirectionLabels();
		$calculationModes = self::getCalculationModeLabels();
		$statusLabels = self::getStatusLabels();
		foreach ($items as $key => $item) {
			$item['source_type_label'] = self::label($sourceTypes, $item['source_type']);
			$item['direction_label'] = self::label($directions, $item['direction']);
			$item['calculation_mode_label'] = self::label($calculationModes, $item['calculation_mode']);
			if (!in_array($item['calculation_mode'], array('formula', 'module'))) {
				$item['calculation_mode_label'] = '数字';
			}
			$item['status_label'] = self::label($statusLabels, $item['status']);
			$item['project_kind'] = empty($item['template_id']) ? 'custom' : 'common';
			$item['project_kind_label'] = empty($item['template_id']) ? '自定义项目' : '通用项目';
			$items[$key] = $item;
		}
		return $items;
	}
}
