<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
use ScshuxCms\Core\Model\BaseModel;
$model = new BaseModel();
/*@var $db \Phalcon\Db\Adapter\Pdo\Mysql */
$model->getDB()->execute("INSERT INTO `".$model->getTableName('config')."` VALUES ('80', 'corpid', '企业ID', '1', '', null, '4', '1', '钉钉的企业id');");
$model->getDB()->execute("INSERT INTO `".$model->getTableName('config')."` VALUES ('81', 'corpsecret', '凭证密钥', '1', '', null, '4', '1', '企业应用的凭证密钥');");