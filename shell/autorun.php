<?php 
/**
 * @desc 自动执行考核    每天凌晨3点运行
 * crontab 0 3 * * * /usr/bin/php /var/www/html/dingding/v1/shell/autorun.php > /dev/null
 * 
 */

if(PHP_SAPI!="cli")
{
	exit;
}


set_time_limit(0);
date_default_timezone_set('PRC');
error_reporting(E_ALL && ~E_NOTICE && ~E_WARNING);
$nowday = date('d',time()); //获取当前是哪一天

$prefix='scsx_';	//数据库表前缀
$dsn = "mysql:host=127.0.0.1;dbname=dingding;port=3306";
$db = new PDO($dsn, 'root', '12`12`1`');
$db->exec('set charset "utf8"');

$sql = 'select *  from '.$prefix.'auto_run where run_date='.$nowday;
$result = $db->query($sql);
$items = $result->fetchAll(PDO::FETCH_ASSOC);
if (empty($items))
{
	exit();
}

$idsArr=array();
foreach ($items as $item)
{	
	$idsArr[]=$item['report_Id'];
}

if ($idsArr)
{
	//修改考评表 执行状态
	$ids=implode(',', $idsArr);
	$where='id in('.$ids.')';
	$sql = 'update '.$prefix.'report set ispoint=1 where '.$where;
	$db->exec($sql);
}




function pp($arr)
{
	echo '<pre>';
	print_r($arr);
	exit();
}
