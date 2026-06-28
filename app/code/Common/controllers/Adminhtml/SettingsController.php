<?php
/**
 * 系统配置
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Common\Model\ConfigModel;
use ScshuxCms\Core\Helper;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Constants;
class SettingsController extends AdminBaseController
{
	
	/**
	 * 系统配置
	 */
	public  function  configAction()
	{
		
		if($this->request->isPost())
		{
			
			$postData = $_POST;
			$backUrl = $this->getHelper()->createUrl(array('p'=>'settings/config'));
			
			//处理文件上传
			foreach ($_FILES as $fileName=>$file)
			{
				if($file['tmp_name'])
				{
					$filePath =Utils::uploadFile($fileName,'system');
					if($filePath){
						 $postData[$fileName] = $filePath;
					}
				}
			}
			
			//更改数据
			foreach ($postData as $key=>$value)
			{
				$configModel = ConfigModel::findFirst("code='{$key}'");
				if($configModel){
					$configModel->save(array('value'=>$value));
				}
			}
			$keyName = Constants::CACHE_SYSTEM_CONFIG;
			$this->getHelper()->getCache()->delete($keyName);
			Utils::showMsg("更改成功!", $backUrl);
		}
		
		//获取分类组和分类
		$configGroups = ConfigModel::factory()->getConfigGroup();
		$configs = ConfigModel::factory()->find(array(
				'order' =>'sort desc,id asc'
		));
		$formatConfigGroups = array();
		
		//组合分类
		foreach ($configs as $config)
		{
			$groupId = intval($config->group);
			if($groupId){
				$formatConfigGroups[$groupId]['items'][] = $config;
				$formatConfigGroups[$groupId]['name'] = $configGroups[$groupId];
				$formatConfigGroups[$groupId]['id'] = $groupId;
			}
		}
		ksort($formatConfigGroups);
		$this->view->setVar('configGroups', $formatConfigGroups);
		
	}
	
}