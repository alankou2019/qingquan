<?php
/**
 * 留言表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Message\Model;
use ScshuxCms\Core\Model\BaseModel;
class MessageModel extends BaseModel
{

	protected static  $_instance=null;

	public function initialize()
	{
		$this->setSource($this->getTableName("message"));
	}

	/**
	 * 返回实例
	 * @return \ScshuxCms\Message\Model\MessageModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new MessageModel();
		}
		return self::$_instance;
	}



}