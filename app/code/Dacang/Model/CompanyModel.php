<?php
/**
 * 公司管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Dacang\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Dding;
class CompanyModel extends BaseModel
{

	protected static  $_instance=null;

	public function initialize()
	{
		$this->setSource($this->getTableName("company"));
	}


	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Dacang\Model\CompanyModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new CompanyModel();
		}
		return self::$_instance;
	}
	
	
	
	/**
	 * 
	 * @desc	获取当前登录用户的cropaccesstoken
	 * @param	$companyId		
	 * @return			
	 * @date	2017年5月10日
	 */
	public function getCropAccessToken($companyId)
	{
		if(!$companyId)
		{
			return false ;
		}
		$companyinfo = CompanyModel::findFirst($companyId);
		if(!$companyinfo)
		{
			$this->sendErrorResult('错误') ;
		}
		
		$corpid = $companyinfo->corpid ;
		$corpsecret = $companyinfo->corpsecret ;
			
		$accesstoken = Dding::factory()->getCorpAccessToken($corpid,$corpsecret);
		
		return $accesstoken ;
	}
}