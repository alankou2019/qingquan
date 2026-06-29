<?php
/**
 * Monthly payroll periods.
 */
namespace ScshuxCms\Salary\Model;

use ScshuxCms\Core\Model\BaseModel;

class PayrollPeriodModel extends BaseModel
{
	protected static $_instance = null;

	public function getSource()
	{
		return $this->getTableName("payroll_periods");
	}

	public static function factory()
	{
		if (self::$_instance == null) {
			self::$_instance = new PayrollPeriodModel();
		}
		return self::$_instance;
	}

	public function getCompanyPeriods($companyId, $limit = 36)
	{
		$db = $this->getDB();
		$periodTable = $this->getSource();
		$rowTable = $this->getTableName('payroll_employee_rows');
		$slipTable = $this->getTableName('payroll_slips');
		$sql = 'select p.*,count(distinct r.id) as row_count,count(distinct s.id) as slip_count,' .
			'ifnull(sum(case when s.status="published" then 1 else 0 end),0) as published_count ' .
			'from `' . $periodTable . '` p ' .
			'left join `' . $rowTable . '` r on p.id=r.payroll_period_id and p.company_id=r.company_id ' .
			'left join `' . $slipTable . '` s on p.id=s.payroll_period_id and p.company_id=s.company_id ' .
			'where p.company_id=' . intval($companyId) . ' ' .
			'group by p.id order by p.payroll_month desc,p.id desc limit ' . intval($limit);
		return $db->query($sql)->fetchAll();
	}

	public function getCompanyPeriod($companyId, $periodId)
	{
		$db = $this->getDB();
		$sql = 'select * from `' . $this->getSource() . '` where company_id=' . intval($companyId) .
			' and id=' . intval($periodId) . ' limit 1';
		return $db->query($sql)->fetch();
	}

	public function markPublished($companyId, $periodId, $operatorId)
	{
		$now = time();
		$sql = 'update `' . $this->getSource() . '` set status="published",published_by=' . intval($operatorId) .
			',published_at=' . $now . ',updated_at=' . $now .
			' where company_id=' . intval($companyId) . ' and id=' . intval($periodId);
		return $this->getDB()->execute($sql);
	}

	public static function getStatusName($status)
	{
		$map = array(
			'draft' => '草稿',
			'calculated' => '已核算',
			'submitted' => '已提交',
			'approved' => '已审批',
			'rejected' => '已驳回',
			'archived' => '已归档',
			'published' => '已发工资条',
		);
		return isset($map[$status]) ? $map[$status] : $status;
	}

	public static function getSourceName($sourceType, $sourceName = '')
	{
		if ($sourceName !== '') {
			return $sourceName;
		}
		$map = array(
			'system' => '系统生成',
			'excel' => 'Excel导入',
			'manual' => '手工维护',
		);
		return isset($map[$sourceType]) ? $map[$sourceType] : '月工资表';
	}

	public static function canPublishPayslip($status)
	{
		return in_array($status, array('archived', 'approved'));
	}
}
