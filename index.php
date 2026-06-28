<?php
/**
 * 系统入口文件
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */

if(version_compare(PHP_VERSION,'5.3.0','le')){
	exit('Please install more than php5.30 version!');
}

if(!extension_loaded('phalcon')) {
	exit('Please install phalcon!');	
}

define('WEBROOT', str_replace('\\', '/', dirname(__FILE__)));
define('APPROOT', str_replace('\\', '/', dirname(__FILE__).'/app'));
include_once APPROOT.'/ext/debug.inc.php';
include_once APPROOT.'/Bootstrap.php';



$bootstrap = new ScshuxCms\Bootstrap();
$bootstrap->run();
