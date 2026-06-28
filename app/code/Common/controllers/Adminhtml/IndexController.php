<?php
/**
 * 后台管理界面
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Common\Model\AreaModel;
class IndexController extends  AdminBaseController
{

	/**
	 * 系统主页
	 */
	public  function  indexAction()
	{

	}


	/**
	 * 仪表盘
	 */
	public  function dashboardAction()
	{

	}

	/**
	 * 地区数据
	 */
	public function  areaAction()
	{
		$id = isset($_REQUEST['id'])?intval($_REQUEST['id']):0;
		$areas = AreaModel::find('parent_id='.$id);
		$result = array();
		foreach ($areas as $area)
		{
			$result[$area->id] = $area;
		}
		$this->sendJson($result);
	}

}