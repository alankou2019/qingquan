<?php
/**
 * 管理员日志
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Common\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
class AdminLogModel extends BaseModel
{
	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("admin_log");
	}
	
	
	/**
	 * 操作日志
	 * @param  $content
	 * @param string $admin_id
	 * @param string $ip
	 */
	public  function  addLog($content,$admin_id='',$ip='')
	{
		
		if(empty($content)) return  false;
		
		if(empty($ip)){$ip = Utils::getIP();}
		
		if(empty($admin_id)){$admin_id = '';}
		
		return  $this->save(array(
				'content' => $content,
				'admin_id' => $admin_id,
				'created' => Helper::factory()->getTime()->gmtime(),
				'ip' => $ip
		));
	}


	/**
	 * 返回实例
	 * @return \ScshuxCms\Common\Model\AdminLogModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new AdminLogModel();
		}
		return self::$_instance;
	}



}