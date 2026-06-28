<?php
/**
 * 错误监控
 * @param number $errno
 * @param number $errstr
 * @param number $errfile
 * @param number $errline
 * @return multitype:
 */
function customer_error_handler($errno=0 ,$errstr=0 , $errfile=0 ,$errline=0){
	
	if($errno == '8192') return array();
	
	if($errno && $errfile){
		if(true){
			$earr = array();
			$earr['type'] = $errno;
			$earr['message'] = $errstr;
			$earr['file'] = $errfile;
			$earr['line'] = $errline;
		}
	}else{
		$earr = error_get_last();
	}
	if($earr){
		if($earr['type'] == E_NOTICE || $earr['type'] == E_WARNING || $earr['type'] ==8192){return array();}
		$dir = WEBROOT.'/var/error';
		if(!file_exists($dir)){
			@mkdir($dir,0777,true);
		}
		file_put_contents($dir.'/'.date('Y-m-d').'.error', var_export($earr,true).PHP_EOL.PHP_EOL,FILE_APPEND);
	}
	return array();
}

$err = 'customer_error_handler';
register_shutdown_function($err) OR set_error_handler($err,E_ALL); // 同时注册两个函数.


