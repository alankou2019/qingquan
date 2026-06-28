<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
use ScshuxCms\Core\Model\BaseModel;
$model = new BaseModel();
/*@var $db \Phalcon\Db\Adapter\Pdo\Mysql */

/**
 * 更改message结构
 * @var array $tableInits
 */
$tableInits = array(
	"company" => array(//添加公司字段
			'field' => 'company',
			'type' => 'varchar(30)',
	),
	"company_addr" => array(//添加公司地址字段
			'field' => 'company_addr',
			'type' => 'varchar(50)',
	),
	"cooper_service" => array(
			'field' => 'cooper_service',//合作服务
			'type' => 'varchar(20)',
	),
	"covered_area" => array(
			'field' => 'covered_area',//建筑面积
			'type' => 'varchar(20)',
	),
	"inputtime" => array(
			'field' => 'inputtime',//提交预约时间
			'type' => 'int(11)',
	)
);
$fields = array();
$tableObjs = $model->getDB()->query("describe `".$model->getTableName('message')."`");
$tableExtras = $tableObjs->fetchAll();
foreach ($tableExtras as $tableExtra)
{
	$fields[] = $tableExtra['Field'];
}
foreach ($tableInits as $key => $array)
{
	if(!in_array($array['field'], $fields))
	{
		$model->getDB()->execute("ALTER TABLE `".$model->getTableName('message')."` ADD ".$array['field']."  ".$array['type']);
	}
}