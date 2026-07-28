<?php
/**
 * Payroll audit roles and workflow records.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Salary\Model\PayrollEmployeeRowModel;
use ScshuxCms\Salary\Model\PayrollPeriodModel;

class SalaryPayrollAuditModel extends BaseModel
{
	protected static $_instance = null;

	public function initialize()
	{
		$this->setSource($this->getTableName("salary_payroll_audit_record"));
	}

	public function getRoleTable()
	{
		return $this->getTableName("salary_payroll_audit_role");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new SalaryPayrollAuditModel();
		}
		return self::$_instance;
	}

	public function getReviewerItems($companyId)
	{
		$departmentSql = SalaryEmployeeDepartmentModel::factory()->getDepartmentSql($companyId, 'u');
		$sql = 'select r.reviewer_id,u.name,u.department_id,' . $departmentSql['select'] . ' as departmentname ' .
			'from `' . $this->getRoleTable() . '` r ' .
			'left join `' . $this->getTableName('company_user') . '` u on r.reviewer_id=u.id and r.company_id=u.company_id ' .
			$departmentSql['join'] .
			'where r.company_id=' . intval($companyId) . ' and r.status="active" ' .
			'order by r.sort_order asc,r.id asc';
		return $this->getDB()->query($sql)->fetchAll();
	}

	public function getReviewerIds($companyId)
	{
		$ids = array();
		$items = $this->getReviewerItems($companyId);
		foreach ($items as $item) {
			$ids[] = intval($item['reviewer_id']);
		}
		return $ids;
	}

	public function saveReviewers($companyId, $reviewerIds)
	{
		$companyId = intval($companyId);
		if ($companyId <= 0) {
			return false;
		}
		if (empty($reviewerIds) || !is_array($reviewerIds)) {
			$reviewerIds = array();
		}
		$db = $this->getDB();
		$roleTable = $this->getRoleTable();
		$now = time();
		$db->execute('delete from `' . $roleTable . '` where company_id=' . $companyId);
		$saved = array();
		$sort = 0;
		foreach ($reviewerIds as $reviewerId) {
			$reviewerId = intval($reviewerId);
			if ($reviewerId <= 0 || isset($saved[$reviewerId])) {
				continue;
			}
			$saved[$reviewerId] = 1;
			$sort = $sort + 10;
			$sql = 'insert into `' . $roleTable . '` ' .
				'(`company_id`,`reviewer_id`,`sort_order`,`status`,`created_at`,`updated_at`) values ' .
				'(' . $companyId . ',' . $reviewerId . ',' . $sort . ',"active",' . $now . ',' . $now . ')';
			$db->execute($sql);
		}
		return true;
	}

	public function submitPeriod($companyId, $periodId, $operatorId)
	{
		$companyId = intval($companyId);
		$periodId = intval($periodId);
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($companyId, $periodId);
		if (!$period) {
			$this->_lastError = '工资表不存在';
			return false;
		}
		if (!PayrollPeriodModel::canSubmitAudit($period['status'])) {
			$this->_lastError = '当前状态不能提交审核';
			return false;
		}
		$rows = PayrollEmployeeRowModel::factory()->getRowsByPeriod($companyId, $periodId);
		if (empty($rows)) {
			$this->_lastError = '工资表没有员工工资数据';
			return false;
		}
		$reviewerIds = $this->getReviewerIds($companyId);
		if (empty($reviewerIds)) {
			$this->_lastError = '请先在薪酬管理授权中设置工资表审核人';
			return false;
		}

		$db = $this->getDB();
		$recordTable = $this->getSource();
		$now = time();
		$db->execute('delete from `' . $recordTable . '` where company_id=' . $companyId . ' and payroll_period_id=' . $periodId);
		foreach ($reviewerIds as $reviewerId) {
			$sql = 'insert into `' . $recordTable . '` ' .
				'(`company_id`,`payroll_period_id`,`reviewer_id`,`status`,`created_at`,`updated_at`) values ' .
				'(' . $companyId . ',' . $periodId . ',' . intval($reviewerId) . ',"pending",' . $now . ',' . $now . ')';
			$db->execute($sql);
		}
		PayrollPeriodModel::factory()->markSubmitted($companyId, $periodId, $operatorId);
		return true;
	}

	public function reviewPeriod($companyId, $periodId, $reviewerId, $status, $opinion = '')
	{
		$companyId = intval($companyId);
		$periodId = intval($periodId);
		$reviewerId = intval($reviewerId);
		if (!in_array($status, array('approved', 'rejected'))) {
			$this->_lastError = '审核状态不正确';
			return false;
		}
		$period = PayrollPeriodModel::factory()->getCompanyPeriod($companyId, $periodId);
		if (!$period || $period['status'] != 'submitted') {
			$this->_lastError = '工资表不在审核中';
			return false;
		}
		$record = $this->findFirst('company_id=' . $companyId . ' and payroll_period_id=' . $periodId . ' and reviewer_id=' . $reviewerId);
		if (!$record) {
			$this->_lastError = '审核人不在本次审核名单中';
			return false;
		}

		$now = time();
		$record->saveData(array(
			'status' => $status,
			'opinion' => addslashes($opinion),
			'reviewed_at' => $now,
			'updated_at' => $now,
		));

		if ($status == 'rejected') {
			PayrollPeriodModel::factory()->markRejected($companyId, $periodId, $reviewerId, $opinion);
			return true;
		}

		$pending = $this->findFirst('company_id=' . $companyId . ' and payroll_period_id=' . $periodId . ' and status="pending"');
		if (!$pending) {
			PayrollPeriodModel::factory()->markApproved($companyId, $periodId, $reviewerId);
		}
		return true;
	}

	public function getPeriodAuditMap($companyId, $periodIds)
	{
		$return = array();
		if (empty($periodIds)) {
			return $return;
		}
		$cleanIds = array();
		foreach ($periodIds as $periodId) {
			$periodId = intval($periodId);
			if ($periodId > 0) {
				$cleanIds[] = $periodId;
			}
		}
		if (empty($cleanIds)) {
			return $return;
		}
		$sql = 'select a.*,u.name as reviewer_name from `' . $this->getSource() . '` a ' .
			'left join `' . $this->getTableName('company_user') . '` u on a.reviewer_id=u.id and a.company_id=u.company_id ' .
			'where a.company_id=' . intval($companyId) . ' and a.payroll_period_id in (' . implode(',', $cleanIds) . ') ' .
			'order by a.id asc';
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $item) {
			$periodId = intval($item['payroll_period_id']);
			if (!isset($return[$periodId])) {
				$return[$periodId] = array(
					'items' => array(),
					'pending' => 0,
					'approved' => 0,
					'rejected' => 0,
					'total' => 0,
				);
			}
			$return[$periodId]['items'][] = $item;
			$return[$periodId]['total']++;
			if (isset($return[$periodId][$item['status']])) {
				$return[$periodId][$item['status']]++;
			}
		}
		return $return;
	}

	public static function getRecordStatusName($status)
	{
		$map = array(
			'pending' => '待审核',
			'approved' => '已同意',
			'rejected' => '已驳回',
		);
		return isset($map[$status]) ? $map[$status] : $status;
	}
}
