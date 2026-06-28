<?php
/**
 * 系统公共函数
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */

/**
 * Custom error handler
 *
 * @param integer $errno
 * @param string $errstr
 * @param string $errfile
 * @param integer $errline
 */
function coreErrorHandler($errno, $errstr, $errfile, $errline){
	if (strpos($errstr, 'DateTimeZone::__construct')!==false) {
		// there's no way to distinguish between caught system exceptions and warnings
		return false;
	}

	$errno = $errno & error_reporting();
	if ($errno == 0) {
		return false;
	}
	if (!defined('E_STRICT')) {
		define('E_STRICT', 2048);
	}
	if (!defined('E_RECOVERABLE_ERROR')) {
		define('E_RECOVERABLE_ERROR', 4096);
	}
	if (!defined('E_DEPRECATED')) {
		define('E_DEPRECATED', 8192);
	}

	// PEAR specific message handling
	if (stripos($errfile.$errstr, 'pear') !== false) {
		// ignore strict and deprecated notices
		if (($errno == E_STRICT) || ($errno == E_DEPRECATED)) {
			return true;
		}
		// ignore attempts to read system files when open_basedir is set
		if ($errno == E_WARNING && stripos($errstr, 'open_basedir') !== false) {
			return true;
		}
	}

	$errorMessage = '';

	switch($errno){
		case E_ERROR:
			$errorMessage .= "Error";
			break;
		case E_WARNING:
			$errorMessage .= "Warning";
			break;
		case E_PARSE:
			$errorMessage .= "Parse Error";
			break;
		case E_NOTICE:
			$errorMessage .= "Notice";
			break;
		case E_CORE_ERROR:
			$errorMessage .= "Core Error";
			break;
		case E_CORE_WARNING:
			$errorMessage .= "Core Warning";
			break;
		case E_COMPILE_ERROR:
			$errorMessage .= "Compile Error";
			break;
		case E_COMPILE_WARNING:
			$errorMessage .= "Compile Warning";
			break;
		case E_USER_ERROR:
			$errorMessage .= "User Error";
			break;
		case E_USER_WARNING:
			$errorMessage .= "User Warning";
			break;
		case E_USER_NOTICE:
			$errorMessage .= "User Notice";
			break;
		case E_STRICT:
			$errorMessage .= "Strict Notice";
			break;
		case E_RECOVERABLE_ERROR:
			$errorMessage .= "Recoverable Error";
			break;
		case E_DEPRECATED:
			$errorMessage .= "Deprecated functionality";
			break;
		default:
			$errorMessage .= "Unknown error ($errno)";
			break;
	}

	$errorMessage .= ": {$errstr}  in {$errfile} on line {$errline}";
}


/**
 * 验证输入的邮件地址是否合法
 *
 * @access  public
 * @param   string      $email      需要验证的邮件地址
 *
 * @return bool
 */
function isEmail($user_email)
{
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
	if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false)
	{
		if (preg_match($chars, $user_email))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

/**
 * 检查是否为一个合法的时间格式
 *
 * @access  public
 * @param   string  $time
 * @return  void
 */
function isTime($time)
{
	$pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';

	return preg_match($pattern, $time);
}

/**
 * 转换xml文件到数组
 * @param  $xmlfile
 */
function  xmlFileToArr($xmlfile)
{
	if(file_exists($xmlfile)){
		$dom = new DOMDocument();
		$dom->loadXML(file_get_contents($xmlfile));
		return xmlNodeToArr($dom->documentElement);
	}
	return  false;
}

/**
 * xml到数据组
 * @param  $xml
 */
function  xmlToArr($xml)
{
	$dom = new DOMDocument();
	$dom->loadXML($xml);
	return xmlNodeToArr($dom->documentElement);
}

/**
 * 转换xml dom到一个数组
 * @param  $node
 */
function  xmlNodeToArr($node)
{
	$array = false;
	if ($node->hasChildNodes()) {
		if ($node->childNodes->length == 1) {
			$array = xmlNodeToArr($node->firstChild);
		} else {
			foreach ($node->childNodes as $childNode) {
				if ($childNode->nodeType != XML_TEXT_NODE && $childNode->nodeType != XML_COMMENT_NODE) {
					$array[$childNode->nodeName] = xmlNodeToArr($childNode);
				}
			}
		}
	} else {
		return $node->nodeValue;
	}
	return $array;
}

/**
 * 打印数组
 * @param $arr
 */
function dump($arr)
{
	echo '<pre>';
	print_r($arr);
	die;
}

/**
 * 数组 转 对象
 *
 * @param array $arr 数组
 * @return object
 */
function arrayToObject($arr)
{
    if (gettype($arr) != 'array')
    {
        return;
    }
    foreach ($arr as $k => $v)
    {
        if (gettype($v) == 'array' || getType($v) == 'object')
        {
            $arr[$k] = (object)arrayToObject($v);
        }
    }

    return (object)$arr;
}

/**
 * 对象 转 数组
 *
 * @param object $obj 对象
 * @return array
 */
function objectToArray($obj)
{
    $obj = (array)$obj;
    foreach ($obj as $k => $v)
    {
        if (gettype($v) == 'resource')
        {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array')
        {
            $obj[$k] = (array)objectToArray($v);
        }
    }

    return $obj;
}

/**
 *
 * 无限极分类一维数组
 * @param $cate
 * @param $delimiter
 * @param $pid
 * @param $level
 */
function toLevel($cate, $delimiter = '——', $pid = 0, $level = 0) {
	$arr = array();
	foreach ($cate as $v) {
		if ($v['parent_id'] == $pid) {
			$v['level'] = $level + 1;
			$v['delimiter'] = str_repeat($delimiter, $level);
			$arr[] = $v;
			$arr = array_merge($arr, toLevel($cate, $delimiter, $v['id'], $v['level']));
		}
	}

	return $arr;
}

/**
 * 无限极分类组成多维数组
 * @param $cate
 * @param $name
 * @param $pid
 */
function toLayer($cate, $name = 'child', $pid = 0){
	$arr = array();
	foreach ($cate as $v) {
		if ($v['parent_id'] == $pid) {
			$v[$name] = toLayer($cate, $name, $v['id']);
			$arr[] = $v;
		}
	}

	return $arr;
}

function pp($arr,$die=true)
{
	echo '<pre>' ;
	print_r($arr) ;
	if ($die)
	{
		exit ;
	}
}