<?php
/**
 * Archived monthly commission snapshots.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class CommissionArchiveModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName('salary_commission_archives');
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new CommissionArchiveModel();
		}
		return self::$_instance;
	}

	public function archivePeriod($companyId, $periodId, $operatorId)
	{
		$this->purgeExpiredDeletedArchives($companyId);
		$periodModel = CommissionPeriodModel::factory();
		$period = $periodModel->getCompanyPeriod($companyId, $periodId);
		if (!$period) {
			$this->_lastError = 'Commission calculation sheet does not exist';
			return false;
		}
		if (!in_array($period['status'], array('draft', 'calculated'))) {
			$this->_lastError = 'Only a calculated commission sheet can be archived';
			return false;
		}
		$existing = $this->getActiveArchiveByPeriod($companyId, $periodId);
		if ($existing) {
			$this->_lastError = 'This commission sheet has already been archived';
			return false;
		}
		$rows = $periodModel->getCommissionMatrix($companyId, $periodId);
		if (empty($rows)) {
			$this->_lastError = 'The commission sheet has no employee data to archive';
			return false;
		}
		$now = time();
		$sql = 'insert into `' . $this->getSource() . '` (`company_id`,`commission_period_id`,`commission_month`,`employee_count`,`matched_count`,`total_amount`,`snapshot_data`,`archived_by`,`archived_at`,`created_at`) values ' .
			'(' . intval($companyId) . ',' . intval($periodId) . ',"' . addslashes($period['commission_month']) . '",' . intval($period['employee_count']) . ',' . intval($period['matched_count']) . ',' . $this->money($period['total_amount']) . ',"' . addslashes(serialize($rows)) . '",' . intval($operatorId) . ',' . $now . ',' . $now . ')';
		$db = $this->getDB();
		$db->begin();
		try {
			$db->execute($sql);
			$archiveId = intval($db->lastInsertId());
			if ($archiveId <= 0) {
				throw new \Exception('Cannot create archive record');
			}
			$db->execute('update `' . $periodModel->getSource() . '` set status="archived",updated_at=' . $now . ' where id=' . intval($periodId) . ' and company_id=' . intval($companyId));
			$db->commit();
			return $archiveId;
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = 'Archive commission sheet failed: ' . $e->getMessage();
			return false;
		}
	}

	public function getCompanyArchives($companyId, $filter = array(), $limit = 60)
	{
		$this->purgeExpiredDeletedArchives($companyId);
		$where = ' where company_id=' . intval($companyId) . ' and deleted_at=0';
		if (!empty($filter['commission_month'])) {
			$where .= ' and commission_month="' . addslashes($filter['commission_month']) . '"';
		}
		if (!empty($filter['department_name'])) {
			$where .= ' and snapshot_data like "%' . addslashes($filter['department_name']) . '%"';
		}
		if (!empty($filter['employee_name'])) {
			$where .= ' and snapshot_data like "%' . addslashes($filter['employee_name']) . '%"';
		}
		$sql = 'select * from `' . $this->getSource() . '`' . $where . ' order by commission_month desc,id desc limit ' . intval($limit);
		$items = $this->getDB()->query($sql)->fetchAll();
		foreach ($items as $key => $item) {
			$items[$key]['archived_time'] = empty($item['archived_at']) ? '-' : date('Y-m-d H:i', intval($item['archived_at']));
		}
		return $items;
	}

	public function getArchive($companyId, $archiveId, $includeDeleted = false)
	{
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' and id=' . intval($archiveId);
		if (!$includeDeleted) {
			$sql .= ' and deleted_at=0';
		}
		$sql .= ' limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	public function getActiveArchiveByMonth($companyId, $commissionMonth)
	{
		if (!preg_match('/^\d{4}\-\d{2}$/', $commissionMonth)) {
			return false;
		}
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and commission_month="' . addslashes($commissionMonth) . '" and deleted_at=0 order by id desc limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	public function getArchiveRows($companyId, $archiveId)
	{
		$archive = $this->getArchive($companyId, $archiveId);
		if (!$archive) {
			return array();
		}
		$rows = @unserialize($archive['snapshot_data']);
		return is_array($rows) ? $rows : array();
	}

	public function restoreToCalculation($companyId, $archiveId, $operatorId)
	{
		$archive = $this->getArchive($companyId, $archiveId);
		if (!$archive) {
			$this->_lastError = 'Commission archive record does not exist';
			return false;
		}
		$period = CommissionPeriodModel::factory()->getCompanyPeriod($companyId, intval($archive['commission_period_id']));
		if (!$period || $period['status'] != 'archived') {
			$this->_lastError = 'The archived commission sheet cannot be restored';
			return false;
		}
		$db = $this->getDB();
		$db->begin();
		try {
			$db->execute('update `' . CommissionPeriodModel::factory()->getSource() . '` set status="calculated",updated_at=' . time() . ' where id=' . intval($period['id']) . ' and company_id=' . intval($companyId));
			$db->execute('delete from `' . $this->getSource() . '` where id=' . intval($archiveId) . ' and company_id=' . intval($companyId));
			$db->commit();
			return intval($period['id']);
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = 'Restore commission archive failed: ' . $e->getMessage();
			return false;
		}
	}

	public function deleteArchive($companyId, $archiveId, $operatorId)
	{
		$archive = $this->getArchive($companyId, $archiveId);
		if (!$archive) {
			$this->_lastError = 'Commission archive record does not exist';
			return false;
		}
		$db = $this->getDB();
		$now = time();
		$db->begin();
		try {
			$db->execute('update `' . $this->getSource() . '` set deleted_by=' . intval($operatorId) . ',deleted_at=' . $now . ' where id=' . intval($archiveId) . ' and company_id=' . intval($companyId));
			$db->execute('update `' . CommissionPeriodModel::factory()->getSource() . '` set status="deleted",updated_at=' . $now . ' where id=' . intval($archive['commission_period_id']) . ' and company_id=' . intval($companyId) . ' and status="archived"');
			$db->commit();
			return true;
		} catch (\Exception $e) {
			$db->rollback();
			$this->_lastError = 'Delete commission archive failed: ' . $e->getMessage();
			return false;
		}
	}

	public function purgeExpiredDeletedArchives($companyId)
	{
		$expiredAt = time() - 180 * 86400;
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' and deleted_at>0 and deleted_at<' . intval($expiredAt);
		$items = $this->getDB()->query($sql)->fetchAll();
		$periodModel = CommissionPeriodModel::factory();
		$rowTable = $this->getTableName('salary_commission_rows');
		$valueTable = $this->getTableName('salary_commission_item_values');
		foreach ($items as $item) {
			$period = $periodModel->getCompanyPeriod($companyId, intval($item['commission_period_id']));
			if ($period && $period['status'] == 'deleted') {
				$this->getDB()->execute('delete from `' . $valueTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($period['id']));
				$this->getDB()->execute('delete from `' . $rowTable . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($period['id']));
				$this->getDB()->execute('delete from `' . $periodModel->getSource() . '` where company_id=' . intval($companyId) . ' and id=' . intval($period['id']) . ' and status="deleted"');
			}
			$this->getDB()->execute('delete from `' . $this->getSource() . '` where id=' . intval($item['id']) . ' and company_id=' . intval($companyId));
		}
	}

	protected function getActiveArchiveByPeriod($companyId, $periodId)
	{
		$sql = 'select id from `' . $this->getSource() . '` where company_id=' . intval($companyId) . ' and commission_period_id=' . intval($periodId) . ' and deleted_at=0 limit 1';
		return $this->getDB()->query($sql)->fetch();
	}

	protected function money($value)
	{
		return sprintf('%.2f', round(floatval($value), 2));
	}
}
