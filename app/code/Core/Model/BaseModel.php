<?php
/**
 * 基础model
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Core\Model;
use Phalcon\Mvc\Model;
use Phalcon\Di\FactoryDefault;
class BaseModel extends  Model
{

	protected  $_lastError='';
	
	
	/**
	 * 获取表名
	 * @param  $tableName
	 * @return 
	 */
	public  function  getTableName($tableName)
	{
		$dbconfig = FactoryDefault::getDefault()->get('config')->database;
		$prefix = '';
		if(isset($dbconfig->prefix)){
			$prefix = $dbconfig->prefix;
		}
		return $prefix.$tableName;
	}
	
	/**
	 * 获取最后一次错误信息
	 * @return string
	 */
	public  function  getLastError()
	{
		return  $this->_lastError;
	}
	
	/**
	 * 获取数据操作
	 * @return \Phalcon\Db\Adapter\Pdo\Mysql
	 */
	public  function  getDB()
	{
		$di        = FactoryDefault::getDefault();
		return $di->get('db');
	}
	
	/**
	 * 根据sql删除数据
	 * @param  $where
	 * @return boolean
	 */
	public function  deleteBySql($where)
	{
		$sql = 'DELETE FROM '. $this->getSource();
		if(!empty($where))
		{
			$sql .= ' WHERE	'.$where;
		}
		return $this->getDB()->execute($sql);
	}
	
	/**
	 * @desc	根据sql条件修改数据
	 * @param	$where		
	 * @param	$data		
	 * @return	boolean		
	 */
	public function updateBySql($where,$data)
	{
		if (!$data || !is_array($data))
		{
			return false;
		}
		$sql = 'update '.$this->getSource().' set ';
		$temp=array();
		foreach ($data as $k=>$v)
		{
			$temp[]='`'.$k.'`='.$v;
		}
		$sql.=implode(',', $temp);
		
		if (!empty($where))
		{
			$sql.=' where '.$where;
		}
		
		return $this->getDB()->execute($sql);
	}
}