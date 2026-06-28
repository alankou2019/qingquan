<?php
/**
 * 钉钉接口
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Dacang\Helper\DingtalkCrypt;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Dacang\Helper\Dingding;
use ScshuxCms\Common\Model\ConfigModel;
use ScshuxCms\Core\Constants;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Helper\DingdingOapi;
class  OaapiController  extends FrontendBaseController
{
	
	
	public function initialize()
	{

	}
	
	public  function testAction()
	{
	   DingdingOapi::factory()->getDepartment('4');
	}
	
}