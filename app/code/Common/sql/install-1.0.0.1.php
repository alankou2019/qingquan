<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
use ScshuxCms\Core\Model\BaseModel;
$model = new BaseModel();
/*@var $db \Phalcon\Db\Adapter\Pdo\Mysql */
$model->getDB()->execute("INSERT INTO `".$model->getTableName('config')."` VALUES ('49', 'site_name', '网站名称', '1', '成都植欣园艺有限公司', null, '1', '1', '网站名称,设定后尽量不要去修改，修改后会影响SEO效果');");
$model->getDB()->execute("INSERT INTO `".$model->getTableName('config')."` VALUES ('50', 'system_name', '管理系统名称', '1', '四川蜀讯科技有限公司网站管理系统', null, '2', '1', '设置管理系统名称');");