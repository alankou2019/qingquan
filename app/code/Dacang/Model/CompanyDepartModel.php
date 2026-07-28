<?php
/**
 * 公司部门管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
class CompanyDepartModel extends BaseModel
{

	protected static  $_instance=null;

	public function initialize()
	{
		$this->setSource($this->getTableName("company_department"));
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\CompanyModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new CompanyDepartModel();
		}
		return self::$_instance;
	}
	
	
	/**
	 * @desc	部门自增id转换成钉钉部门id
	 * @param			
	 * @return			
	 */
	public static function idAdpter($id)
	{
		if (!$id)
		{
			return '';
		}
		if (is_numeric($id))
		{
			$item=self::factory()->findFirst(intval($id));
			return $item->dingding_id;
		}
		else 
		{
			$return=array();
			$items=self::factory()->find('id in('.trim($id).')');
			if ($items)
			{
				foreach ($items as $item)
				{
					$return[]=$item->dingding_id;
				}
				$return=  implode(',', $return);
			}
			return $return;
		}
	}
	
	
	
	/**
	 * @desc	钉钉部门id转换为系统自增id
	 * @param
	 * @return
	 */
	public static function adpterId($id)
	{
		if (!$id)
		{
			return '';
		}
		if (is_numeric($id))
		{
			$item=self::factory()->findFirst('dingding_id='.intval($id));
			return $item->id;
		}
		else
		{
			$return=array();
			$items=self::factory()->find('dingding_id in('.trim($id).')');
			if ($items)
			{
				foreach ($items as $item)
				{
					$return[]=$item->id;
				}
				$return=  implode(',', $return);
			}
			return $return;
		}
	}
	
	
	
	/**
	 * @desc	获取部门自增Id
	 * @param	$userId		
	 * @return			
	 */
	public static function getDepartId($userId)
	{
		if (!$userId)
		{
			return 'error';
		}
		$userId=intval($userId);
		$where ='u.id='.$userId;
		$columns ='d.id';
		
		$item=self::factory()->getModelsManager()->createBuilder()->columns($columns)
			->addFrom('ScshuxCms\Dacang\Model\CompanyUserModel','u')
			->leftJoin('ScshuxCms\Dacang\Model\CompanyDepartModel','u.company_id=d.company_id and u.department_id=d.dingding_id','d')
			->where($where)->getQuery()->execute()->toArray();
		if ($item)
		{
			return current($item)['id'];
		}
		return 'error';
	}
	
}