<?php
/**
 * Company commission project settings.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class CommissionProjectModel extends BaseModel
{
	protected static $_instance = null;

	public function initialize()
	{
		$this->setSource($this->getTableName('salary_commission_projects'));
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new CommissionProjectModel();
		}
		return self::$_instance;
	}

	public static function getMetricLabels()
	{
		return array('sales_amount' => '销售额', 'product_count' => '产品数', 'gross_profit' => '毛利', 'custom' => '自定义');
	}

	public static function getModeLabels()
	{
		return array('simple' => '简单提成', 'ladder' => '阶梯提成', 'over_ladder' => '超额阶梯提成');
	}

	public static function getScopeLabels()
	{
		return array('all' => '全公司默认', 'employee' => '指定员工', 'department' => '指定部门', 'position' => '指定岗位', 'multi' => '多选范围');
	}

	public static function getStatusLabels()
	{
		return array('active' => '启用', 'inactive' => '停用');
	}

	public static function getRateTypeLabels()
	{
		return array('percent' => '按比例', 'fixed' => '按固定金额');
	}

	public function getCompanyProjects($companyId)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' and deleted_at=0 order by priority desc,id desc';
		return $this->formatItems($this->getDB()->query($sql)->fetchAll());
	}

	public function getCompanyProject($companyId, $projectId)
	{
		$sql = 'select * from `' . $this->getSource() . '` where id=' . intval($projectId) . ' and company_id=' . intval($companyId) . ' and deleted_at=0 limit 1';
		$item = $this->getDB()->query($sql)->fetch();
		if (!$item) {
			return false;
		}
		$items = $this->formatItems(array($item));
		return $items[0];
	}

	public function getScopeOptions($companyId)
	{
		$companyId = intval($companyId);
		$userTable = $this->getTableName('company_user');
		$departmentTable = $this->getTableName('company_department');
		$employees = $this->getDB()->query('select id,name from `' . $userTable . '` where company_id=' . $companyId . ' order by id desc limit 500')->fetchAll();
		$departments = $this->getDB()->query('select id,name from `' . $departmentTable . '` where company_id=' . $companyId . ' order by id asc limit 500')->fetchAll();
		return array('employees' => $employees, 'departments' => $departments);
	}

	public function saveProject($companyId, $postData, $operatorId = 0)
	{
		$companyId = intval($companyId);
		$id = isset($postData['id']) ? intval($postData['id']) : 0;
		$name = isset($postData['name']) ? trim($postData['name']) : '';
		$metricType = isset($postData['metric_type']) ? trim($postData['metric_type']) : 'sales_amount';
		$metricName = isset($postData['metric_name']) ? trim($postData['metric_name']) : '';
		$mode = isset($postData['commission_mode']) ? trim($postData['commission_mode']) : 'simple';
		$statusProvided = isset($postData['status']);
		$status = $statusProvided ? trim($postData['status']) : 'active';
		$rateType = isset($postData['rate_type']) ? trim($postData['rate_type']) : 'percent';
		$rateValue = isset($postData['rate_value']) ? round(floatval($postData['rate_value']), 4) : 0;

		if ($companyId <= 0 || $name == '') {
			$this->_lastError = '请填写提成项目名称';
			return false;
		}
		$metrics = self::getMetricLabels();
		$modes = self::getModeLabels();
		$statuses = self::getStatusLabels();
		if (!isset($metrics[$metricType]) || !isset($modes[$mode]) || !isset($statuses[$status])) {
			$this->_lastError = '提成项目参数不正确';
			return false;
		}
		if ($metricType == 'custom' && $metricName == '') {
			$this->_lastError = '自定义提成项目请填写业绩口径';
			return false;
		}
		$scopeData = $this->buildScopeData($companyId, $postData);
		if (!$scopeData) {
			return false;
		}
		$rateTypes = self::getRateTypeLabels();
		if (!isset($rateTypes[$rateType])) {
			$rateType = 'percent';
		}
		$tierConfig = $this->buildTierConfig($postData);
		if (in_array($mode, array('ladder', 'over_ladder')) && empty($tierConfig)) {
			$this->_lastError = '阶梯提成请至少填写一档规则';
			return false;
		}

		$duplicateWhere = 'company_id=' . $companyId . ' and name="' . addslashes($name) . '" and deleted_at=0';
		if ($id > 0) {
			$duplicateWhere .= ' and id!=' . $id;
		}
		if (self::factory()->findFirst($duplicateWhere)) {
			$this->_lastError = '同一企业下已经存在相同提成项目';
			return false;
		}

		$now = time();
		$data = array(
			'company_id' => $companyId,
			'name' => substr($name, 0, 80),
			'metric_type' => $metricType,
			'metric_name' => $metricType == 'custom' ? substr($metricName, 0, 80) : $metrics[$metricType],
			'commission_mode' => $mode,
			'threshold_value' => isset($postData['threshold_value']) ? round(floatval($postData['threshold_value']), 2) : 0,
			'rate_type' => $rateType,
			'rate_value' => $rateValue,
			'tier_config' => json_encode($tierConfig, JSON_UNESCAPED_UNICODE),
			'rule_detail' => isset($postData['rule_detail']) ? substr(trim($postData['rule_detail']), 0, 5000) : '',
			'scope_type' => $scopeData['type'],
			'scope_value' => substr($scopeData['value'], 0, 255),
			'scope_label' => substr($scopeData['label'], 0, 255),
			'scope_config' => json_encode($scopeData['config'], JSON_UNESCAPED_UNICODE),
			'priority' => isset($postData['priority']) ? intval($postData['priority']) : 0,
			'status' => $status,
			'updated_at' => $now,
			'deleted_at' => 0,
		);

		if ($id > 0) {
			$item = self::factory()->findFirst('id=' . $id . ' and company_id=' . $companyId . ' and deleted_at=0');
			if (!$item) {
				$this->_lastError = '提成项目不存在';
				return false;
			}
			if (!$statusProvided) {
				$data['status'] = $item->status;
			}
			return $item->saveData($data);
		}

		$data['created_by'] = intval($operatorId);
		$data['created_at'] = $now;
		$item = new CommissionProjectModel();
		return $item->saveData($data);
	}

	/**
	 * Update calculation fields only. Project name, scope and status remain unchanged.
	 */
	public function saveCalculationRule($companyId, $projectId, $postData)
	{
		$companyId = intval($companyId);
		$projectId = intval($projectId);
		$project = $this->getCompanyProject($companyId, $projectId);
		if (!$project) {
			$this->_lastError = '提成项目不存在';
			return false;
		}

		$data = array('updated_at' => time());
		if ($project['commission_mode'] == 'simple') {
			$rateTypes = self::getRateTypeLabels();
			$rateType = isset($postData['rate_type']) ? trim($postData['rate_type']) : '';
			$rateValue = isset($postData['rate_value']) ? trim($postData['rate_value']) : '';
			if (!isset($rateTypes[$rateType]) || $rateValue === '' || !is_numeric($rateValue) || floatval($rateValue) < 0) {
				$this->_lastError = '请填写正确的提成比例或固定金额';
				return false;
			}
			$data['rate_type'] = $rateType;
			$data['rate_value'] = round(floatval($rateValue), 4);
			$data['tier_config'] = '[]';
		} else {
			if (!$this->validateTierRuleData($postData)) {
				return false;
			}
			$tierConfig = $this->buildTierConfig($postData);
			if (empty($tierConfig)) {
				$this->_lastError = '阶梯提成请至少填写一档规则';
				return false;
			}
			$data['tier_config'] = json_encode($tierConfig, JSON_UNESCAPED_UNICODE);
		}

		$item = self::factory()->findFirst('id=' . $projectId . ' and company_id=' . $companyId . ' and deleted_at=0');
		if (!$item || !$item->saveData($data)) {
			$this->_lastError = '提成规则保存失败，请稍后重试';
			return false;
		}
		return $this->getCompanyProject($companyId, $projectId);
	}

	public function deleteProject($companyId, $projectId)
	{
		$item = self::factory()->findFirst('id=' . intval($projectId) . ' and company_id=' . intval($companyId) . ' and deleted_at=0');
		if (!$item) {
			$this->_lastError = '提成项目不存在';
			return false;
		}
		return $item->saveData(array('status' => 'inactive', 'deleted_at' => time(), 'updated_at' => time()));
	}

	public function updateProjectStatus($companyId, $projectId, $status)
	{
		$statuses = self::getStatusLabels();
		$status = trim($status);
		if (!isset($statuses[$status])) {
			$this->_lastError = '提成项目状态不正确';
			return false;
		}
		$projectId = intval($projectId);
		$companyId = intval($companyId);
		$exists = $this->getDB()->query('select id from `' . $this->getSource() . '` where id=' . $projectId . ' and company_id=' . $companyId . ' and deleted_at=0 limit 1')->fetch();
		if (!$exists) {
			$this->_lastError = '提成项目不存在';
			return false;
		}
		$result = $this->getDB()->execute('update `' . $this->getSource() . '` set status="' . addslashes($status) . '",updated_at=' . time() . ' where id=' . $projectId . ' and company_id=' . $companyId . ' and deleted_at=0');
		if (!$result) {
			$this->_lastError = '提成项目状态更新失败，请稍后重试';
			return false;
		}
		return true;
	}

	protected function formatItems($items)
	{
		$metrics = self::getMetricLabels();
		$modes = self::getModeLabels();
		$scopes = self::getScopeLabels();
		$statuses = self::getStatusLabels();
		$rateTypes = self::getRateTypeLabels();
		foreach ($items as $key => $item) {
			$item['metric_label'] = isset($metrics[$item['metric_type']]) ? $metrics[$item['metric_type']] : $item['metric_type'];
			$item['mode_label'] = isset($modes[$item['commission_mode']]) ? $modes[$item['commission_mode']] : $item['commission_mode'];
			$item['scope_type_label'] = isset($scopes[$item['scope_type']]) ? $scopes[$item['scope_type']] : $item['scope_type'];
			$item['status_label'] = isset($statuses[$item['status']]) ? $statuses[$item['status']] : $item['status'];
			$item['rate_type'] = isset($item['rate_type']) ? $item['rate_type'] : 'percent';
			$item['rate_value'] = isset($item['rate_value']) ? $item['rate_value'] : '0.0000';
			$item['rate_type_label'] = isset($rateTypes[$item['rate_type']]) ? $rateTypes[$item['rate_type']] : $item['rate_type'];
			$item['tier_items'] = $this->decodeTierConfig(isset($item['tier_config']) ? $item['tier_config'] : '');
			$item['scope_config_items'] = $this->decodeScopeConfig(isset($item['scope_config']) ? $item['scope_config'] : '', $item);
			$item['scope_department_ids'] = $this->buildScopeSelectionMap($item['scope_config_items']['departments'], 'id');
			$item['scope_position_values'] = $this->buildScopeSelectionMap($item['scope_config_items']['positions'], 'value');
			$item['scope_employee_ids'] = $this->buildScopeSelectionMap($item['scope_config_items']['employees'], 'id');
			$item['scope_is_all'] = !empty($item['scope_config_items']['all']) ? 1 : 0;
			$item['scope_summary'] = $this->buildScopeSummary($item['scope_config_items']);
			$item['rule_summary'] = $this->buildRuleSummary($item);
			$items[$key] = $item;
		}
		return $items;
	}

	public function calculateAmount($project, $inputValue)
	{
		$value = floatval($this->formatMoney($inputValue));
		if ($value <= 0) {
			return '0.00';
		}
		$mode = isset($project['commission_mode']) ? $project['commission_mode'] : 'simple';
		if ($mode == 'simple') {
			$rateType = isset($project['rate_type']) ? $project['rate_type'] : 'percent';
			$rateValue = isset($project['rate_value']) ? floatval($project['rate_value']) : 0;
			if ($rateType == 'fixed') {
				return $this->formatMoney($value * $rateValue);
			}
			return $this->formatMoney($value * $rateValue / 100);
		}
		$tiers = isset($project['tier_items']) ? $project['tier_items'] : $this->decodeTierConfig(isset($project['tier_config']) ? $project['tier_config'] : '');
		if ($mode == 'over_ladder') {
			return $this->formatMoney($this->calculateOverLadder($value, $tiers));
		}
		return $this->formatMoney($this->calculateWholeLadder($value, $tiers));
	}

	public function buildRuleSnapshot($project)
	{
		return json_encode(array(
			'id' => intval($project['id']),
			'name' => $project['name'],
			'mode' => $project['commission_mode'],
			'rate_type' => isset($project['rate_type']) ? $project['rate_type'] : 'percent',
			'rate_value' => isset($project['rate_value']) ? $project['rate_value'] : '0',
			'tiers' => isset($project['tier_items']) ? $project['tier_items'] : $this->decodeTierConfig(isset($project['tier_config']) ? $project['tier_config'] : ''),
			'rule_detail' => isset($project['rule_detail']) ? $project['rule_detail'] : '',
		), JSON_UNESCAPED_UNICODE);
	}

	protected function buildScopeData($companyId, $postData)
	{
		if (!isset($postData['scope_form_mode']) || $postData['scope_form_mode'] != 'multi') {
			$type = isset($postData['scope_type']) ? trim($postData['scope_type']) : 'all';
			$value = isset($postData['scope_value']) ? trim($postData['scope_value']) : '';
			$label = isset($postData['scope_label']) ? trim($postData['scope_label']) : '';
			if ($type == 'all') {
				return array('type' => 'all', 'value' => '', 'label' => '全公司默认', 'config' => $this->emptyScopeConfig(true));
			}
			if (!in_array($type, array('employee', 'department', 'position')) || $value === '' || $label === '') {
				$this->_lastError = '请选择提成项目适用范围';
				return false;
			}
			$config = $this->emptyScopeConfig(false);
			$key = $type == 'employee' ? 'employees' : ($type == 'department' ? 'departments' : 'positions');
			$config[$key][] = $type == 'position' ? array('value' => $value, 'label' => $label) : array('id' => intval($value), 'label' => $label);
			return array('type' => $type, 'value' => $value, 'label' => $label, 'config' => $config);
		}
		if (!empty($postData['scope_all'])) {
			return array('type' => 'all', 'value' => '', 'label' => '全公司默认', 'config' => $this->emptyScopeConfig(true));
		}
		$options = $this->getScopeOptions($companyId);
		$maps = array('departments' => array(), 'positions' => array(), 'employees' => array());
		foreach ($options['departments'] as $item) { $maps['departments'][(string)intval($item['id'])] = $item['name']; }
		foreach ($options['positions'] as $item) { $maps['positions'][(string)$item['value']] = $item['name']; }
		foreach ($options['employees'] as $item) { $maps['employees'][(string)intval($item['id'])] = $item['name']; }
		$config = $this->emptyScopeConfig(false);
		foreach (array('departments' => 'scope_departments', 'positions' => 'scope_positions', 'employees' => 'scope_employees') as $key => $field) {
			$values = isset($postData[$field]) && is_array($postData[$field]) ? array_unique($postData[$field]) : array();
			foreach ($values as $value) {
				$value = trim((string)$value);
				if ($value === '' || !isset($maps[$key][$value])) { continue; }
				$config[$key][] = $key == 'positions' ? array('value' => $value, 'label' => $maps[$key][$value]) : array('id' => intval($value), 'label' => $maps[$key][$value]);
			}
		}
		$total = count($config['departments']) + count($config['positions']) + count($config['employees']);
		if ($total <= 0) {
			$this->_lastError = '请至少选择一个部门、岗位或人员；如适用全公司请选择“全部”';
			return false;
		}
		if ($total == 1) {
			if (!empty($config['departments'])) { $item = $config['departments'][0]; return array('type' => 'department', 'value' => (string)$item['id'], 'label' => $item['label'], 'config' => $config); }
			if (!empty($config['positions'])) { $item = $config['positions'][0]; return array('type' => 'position', 'value' => $item['value'], 'label' => $item['label'], 'config' => $config); }
			$item = $config['employees'][0]; return array('type' => 'employee', 'value' => (string)$item['id'], 'label' => $item['label'], 'config' => $config);
		}
		return array('type' => 'multi', 'value' => '', 'label' => $this->buildScopeSummary($config), 'config' => $config);
	}

	protected function emptyScopeConfig($all)
	{
		return array('all' => $all ? 1 : 0, 'departments' => array(), 'positions' => array(), 'employees' => array());
	}

	protected function decodeScopeConfig($value, $project)
	{
		$config = json_decode((string)$value, true);
		if (is_array($config) && isset($config['departments']) && isset($config['positions']) && isset($config['employees'])) {
			$config['all'] = !empty($config['all']) ? 1 : 0;
			return $config;
		}
		$type = isset($project['scope_type']) ? $project['scope_type'] : 'all';
		$config = $this->emptyScopeConfig($type == 'all');
		$value = trim(isset($project['scope_value']) ? $project['scope_value'] : '');
		$label = trim(isset($project['scope_label']) ? $project['scope_label'] : '');
		if ($type == 'department' && $value !== '') { $config['departments'][] = array('id' => intval($value), 'label' => $label); }
		elseif ($type == 'position' && $value !== '') { $config['positions'][] = array('value' => $value, 'label' => $label); }
		elseif ($type == 'employee' && $value !== '') { $config['employees'][] = array('id' => intval($value), 'label' => $label); }
		return $config;
	}

	protected function buildScopeSelectionMap($items, $field)
	{
		$return = array(); foreach ($items as $item) { if (isset($item[$field])) { $return[(string)$item[$field]] = 1; } } return $return;
	}

	protected function buildScopeSummary($config)
	{
		if (!empty($config['all'])) { return '全公司默认'; }
		$parts = array();
		foreach (array('departments' => '部门', 'positions' => '岗位', 'employees' => '人员') as $key => $label) {
			$names = array(); foreach ($config[$key] as $item) { if (!empty($item['label'])) { $names[] = $item['label']; } }
			if (!empty($names)) { $parts[] = $label . '：' . implode('、', $names); }
		}
		return implode('；', $parts);
	}

	protected function buildTierConfig($postData)
	{
		$return = array();
		$mins = isset($postData['tier_min']) && is_array($postData['tier_min']) ? $postData['tier_min'] : array();
		$maxs = isset($postData['tier_max']) && is_array($postData['tier_max']) ? $postData['tier_max'] : array();
		$rates = isset($postData['tier_rate']) && is_array($postData['tier_rate']) ? $postData['tier_rate'] : array();
		for ($i = 0; $i < 6; $i++) {
			$min = isset($mins[$i]) ? trim($mins[$i]) : '';
			$max = isset($maxs[$i]) ? trim($maxs[$i]) : '';
			$rate = isset($rates[$i]) ? trim($rates[$i]) : '';
			if ($min === '' && $max === '' && $rate === '') {
				continue;
			}
			$return[] = array(
				'min' => $min === '' ? 0 : round(floatval($min), 2),
				'max' => $max === '' ? 0 : round(floatval($max), 2),
				'rate' => $rate === '' ? 0 : round(floatval($rate), 4),
			);
		}
		usort($return, function ($a, $b) {
			if ($a['min'] == $b['min']) {
				return 0;
			}
			return $a['min'] < $b['min'] ? -1 : 1;
		});
		return $return;
	}

	protected function validateTierRuleData($postData)
	{
		$mins = isset($postData['tier_min']) && is_array($postData['tier_min']) ? $postData['tier_min'] : array();
		$maxs = isset($postData['tier_max']) && is_array($postData['tier_max']) ? $postData['tier_max'] : array();
		$rates = isset($postData['tier_rate']) && is_array($postData['tier_rate']) ? $postData['tier_rate'] : array();
		for ($i = 0; $i < 6; $i++) {
			$min = isset($mins[$i]) ? trim($mins[$i]) : '';
			$max = isset($maxs[$i]) ? trim($maxs[$i]) : '';
			$rate = isset($rates[$i]) ? trim($rates[$i]) : '';
			if ($min === '' && $max === '' && $rate === '') {
				continue;
			}
			if (($min !== '' && !is_numeric($min)) || ($max !== '' && !is_numeric($max)) || $rate === '' || !is_numeric($rate)) {
				$this->_lastError = '阶梯规则中的业绩值和提成比例必须为数字';
				return false;
			}
			$minValue = $min === '' ? 0 : floatval($min);
			$maxValue = $max === '' ? 0 : floatval($max);
			if ($minValue < 0 || $maxValue < 0 || floatval($rate) < 0 || ($maxValue > 0 && $maxValue <= $minValue)) {
				$this->_lastError = '阶梯规则数值不能为负数，封顶值应大于起始值';
				return false;
			}
		}
		return true;
	}

	protected function decodeTierConfig($value)
	{
		$items = json_decode((string)$value, true);
		return is_array($items) ? $items : array();
	}

	protected function buildRuleSummary($project)
	{
		if ($project['commission_mode'] == 'simple') {
			$value = floatval(isset($project['rate_value']) ? $project['rate_value'] : 0);
			return $project['rate_type'] == 'fixed' ? '每单位' . $this->formatMoney($value) . '元' : $value . '%';
		}
		$parts = array();
		foreach ($project['tier_items'] as $tier) {
			$max = empty($tier['max']) ? '以上' : $this->formatMoney($tier['max']);
			$parts[] = $this->formatMoney($tier['min']) . '-' . $max . ':' . floatval($tier['rate']) . '%';
		}
		return implode('；', $parts);
	}

	protected function calculateWholeLadder($value, $tiers)
	{
		$rate = 0;
		foreach ($tiers as $tier) {
			$min = floatval($tier['min']);
			$max = floatval($tier['max']);
			if ($value >= $min && ($max <= 0 || $value <= $max)) {
				$rate = floatval($tier['rate']);
			}
		}
		return $value * $rate / 100;
	}

	protected function calculateOverLadder($value, $tiers)
	{
		$total = 0;
		foreach ($tiers as $tier) {
			$min = floatval($tier['min']);
			$max = floatval($tier['max']);
			if ($value <= $min) {
				continue;
			}
			$upper = $max > 0 ? min($value, $max) : $value;
			if ($upper <= $min) {
				continue;
			}
			$total += ($upper - $min) * floatval($tier['rate']) / 100;
		}
		return $total;
	}

	protected function formatMoney($value)
	{
		$value = str_replace(array(',', '￥', '元', ' '), '', (string)$value);
		if (!is_numeric($value)) {
			$value = 0;
		}
		return sprintf('%.2f', round(floatval($value), 2));
	}
}
