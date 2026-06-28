<?php
/**
 * 支付方式表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Payment\Model;
use ScshuxCms\Core\Model\BaseModel;
class PaymentModel extends BaseModel
{

	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("payment");
	}

	/**
	 * 返回实例
	 * @return \ScshuxCms\Payment\Model\PaymentModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new PaymentModel();
		}
		return self::$_instance;
	}
}