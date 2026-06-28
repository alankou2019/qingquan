<?php
/**
 * 前端控制类
* @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
* @author kaiping.jiang <kaiping.jiang@scshux.com>
*/
namespace  ScshuxCms\Core\Controller;
use ScshuxCms\Core\Seo;
use ScshuxCms\Common\Helper\PageHelper;
use ScshuxCms\Core\Helper;
use ScshuxCms\User\Model\UserModel;
class  FrontendBaseController  extends BaseController
{
	public $companyId ;
    public $companyName;
	/**
	 * 初始化
	 */
	public function initialize()
	{
		$config =  $this->getHelper()->getConfig();
		$this->getView()->setVar('_config',$config);
		$this->getView()->setVar('_user', UserModel::factory()->getUser());
		$this->setSeo();
		$this->getCompanyId() ;
	}

	/**
	 * 设置网站seo
	 * @param string $title
	 * @param string $keywords
	 * @param string $description
	 */
	public function  setSeo($title='',$keywords='',$description='')
	{
		if(empty($title)){
			$title =  $this->getHelper()->getConfig('site_title');
		}else{
			$title .=  '_'.$this->getHelper()->getConfig('site_name');
		}

		if(empty($keywords)){
			$keywords =  $this->getHelper()->getConfig('keywords');
		}else{
			$keywords .=  '_'.$this->getHelper()->getConfig('keywords');
		}

		if(empty($description)){
			$description =  $this->getHelper()->getConfig('description');
		}else{
			$description .=  '_'.$this->getHelper()->getConfig('description');
		}

		Seo::set($title,$keywords,$description);
	}

	
	/**
	 * 获取用户所属公司id
	 */
	protected function getCompanyId()
	{
		$compantId = Helper::factory()->getSession()->get('_user')->company_id;
		if($compantId)
		{
			$this->companyId = $compantId ;
            $this->companyName = Helper::factory()->getSession()->get('_user')->company_name;
		}
		else
		{
			$this->redirect('login/index') ;
		}
	}
	
}