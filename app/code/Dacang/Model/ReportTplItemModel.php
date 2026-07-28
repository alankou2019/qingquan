<?php
/**
 * 报表模版具体指标
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
class  ReportTplItemModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("report_tpl_item"));
	}
	
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\DepartmentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new ReportTplItemModel();
		}
		return self::$_instance;
	}
	
}