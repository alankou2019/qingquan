<?php
/**
 * 系统公告
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Cms\Model;
use ScshuxCms\Core\Model\BaseModel;
class AnnouncementModel extends BaseModel
{

	protected static  $_instance=null;

	public function initialize()
	{
		$this->setSource($this->getTableName("announcement"));
	}

	/**
	 * (non-PHPdoc)
	 * @see \Phalcon\Mvc\Model::save()
	 */
	public  function  save($data = null, $whiteList = null)
	{
		$isInsert = false;
		if(empty($data['id']) && empty($this->id))
		{
			$isInsert = true;
		}

		$result =  parent::save($data,$whiteList);
		if($result && $isInsert)
		{
			$sql = 'INSERT  '.$this->getTableName('announcement_log').'(user_id,announcement_id) SELECT user_id,'.$this->id.' from '.$this->getTableName('user');
			$this->getDB()->execute($sql);
		}
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Phalcon\Mvc\Model::delete()
	 */
	public  function  delete()
	{
		$result = parent::delete();
		if($result)
		{
			 $sql = 'delete from '.$this->getTableName('announcement_log').' where announcement_id='.$this->id;
			 $this->getDB()->execute($sql);
		}
	}

	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Cms\Model\AnnouncementModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new AnnouncementModel();
		}
		return self::$_instance;
	}

}
