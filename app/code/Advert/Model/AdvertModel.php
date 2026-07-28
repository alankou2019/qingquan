<?php
/**
 * 广告
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Advert\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper;
class AdvertModel extends BaseModel
{
	protected static  $_instance=null;
	
	public function initialize()
	{
		$this->setSource($this->getTableName("ad"));
	}
	
	/**
	 * 通过广告位标识获取有效广告
	 * @param  $code
	 */
	public  function loadAdsByPositionCode($code,$limit = 1000)
	{
		$nowtime = Helper::factory()->getTime()->gmtime(); 
		$where = "p.key_code='{$code}' and a.start_time<={$nowtime} and a.end_time>={$nowtime}";
		$items = AdvertModel::factory()->getModelsManager()->createBuilder()
		->addFrom('\ScshuxCms\Advert\Model\AdvertModel','a')
		->leftJoin('\ScshuxCms\Advert\Model\AdvertPositionModel','p.id=a.position_id','p')
		->columns('a.id,a.name,a.link,a.type,a.content,p.name as position_name,a.position_id')
		->andWhere($where)
		->orderBy(['a.start_time desc','a.id desc'])
		->limit($limit)
		->getQuery()
		->execute();
		return $items;
	}
	
	/**
	 * 返回操作实例
	 * @return \ScshuxCms\Advert\Model\AdvertModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new AdvertModel();
		}
		return self::$_instance;
	}
	

}
