<?php
/**
 * Company commission project settings.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class CommissionProjectModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName('salary_commission_projects');
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
		return array('all' => '全公司默认', 'employee' => '指定员工', 'department' => '指定部门', 'position' => '指定岗位');
	}

	public static function getStatusLabels()
	{
		return array('active' => '启用', 'inactive' => '停用');
	}

	public function getCompanyProjects($companyId)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' and deleted_at=0 order by priority desc,id desc';
		return $this->formatItems($this->getDB()->query($sql)->fetchAll());
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
		$scopeType = isset($postData['scope_type']) ? trim($postData['scope_type']) : 'all';
		$scopeValue = isset($postData['scope_value']) ? trim($postData['scope_value']) : '';
		$scopeLabel = isset($postData['scope_label']) ? trim($postData['scope_label']) : '';
		$status = isset($postData['status']) ? trim($postData['status']) : 'active';

		if ($companyId <= 0 || $name == '') {
			$this->_lastError = '请填写提成项目名称';
			return false;
		}
		$metrics = self::getMetricLabels();
		$modes = self::getModeLabels();
		$scopes = self::getScopeLabels();
		$statuses = self::getStatusLabels();
		if (!isset($metrics[$metricType]) || !isset($modes[$mode]) || !isset($scopes[$scopeType]) || !isset($statuses[$status])) {
			$this->_lastError = '提成项目参数不正确';
			return false;
		}
		if ($metricType == 'custom' && $metricName == '') {
			$this->_lastError = '自定义提成项目请填写业绩口径';
			return false;
		}
		if ($scopeType != 'all' && ($scopeValue == '' || $scopeLabel == '')) {
			$this->_lastError = '请选择提成项目适用范围';
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
			'rule_detail' => isset($postData['rule_detail']) ? substr(trim($postData['rule_detail']), 0, 5000) : '',
			'scope_type' => $scopeType,
			'scope_value' => $scopeType == 'all' ? '' : substr($scopeValue, 0, 255),
			'scope_label' => $scopeType == 'all' ? '全公司默认' : substr($scopeLabel, 0, 255),
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
			return $item->save($data);
		}

		$data['created_by'] = intval($operatorId);
		$data['created_at'] = $now;
		$item = new CommissionProjectModel();
		return $item->save($data);
	}

	public function deleteProject($companyId, $projectId)
	{
		$item = self::factory()->findFirst('id=' . intval($projectId) . ' and company_id=' . intval($companyId) . ' and deleted_at=0');
		if (!$item) {
			$this->_lastError = '提成项目不存在';
			return false;
		}
		return $item->save(array('status' => 'inactive', 'deleted_at' => time(), 'updated_at' => time()));
	}

	protected function formatItems($items)
	{
		$metrics = self::getMetricLabels();
		$modes = self::getModeLabels();
		$scopes = self::getScopeLabels();
		$statuses = self::getStatusLabels();
		foreach ($items as $key => $item) {
			$item['metric_label'] = isset($metrics[$item['metric_type']]) ? $metrics[$item['metric_type']] : $item['metric_type'];
			$item['mode_label'] = isset($modes[$item['commission_mode']]) ? $modes[$item['commission_mode']] : $item['commission_mode'];
			$item['scope_type_label'] = isset($scopes[$item['scope_type']]) ? $scopes[$item['scope_type']] : $item['scope_type'];
			$item['status_label'] = isset($statuses[$item['status']]) ? $statuses[$item['status']] : $item['status'];
			$items[$key] = $item;
		}
		return $items;
	}
}
