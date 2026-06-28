<?php
/**
 *
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Core;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Helper\Time;
use ScshuxCms\Common\Model\ConfigModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Payment\Model\PaymentModel;
use ScshuxCms\Core\Helper\Dding;
use ScshuxCms\Dacang\Model\PointReportItemDetailModel;
class Helper
{
	private $_config = null;
	
	/**
	 * 返回实例
	 * @return \ScshuxCms\Core\Helper
	 */
	public static  function factory()
	{
		return FactoryDefault::getDefault()->get('helper');
	}
	
	/**
	 * 获取session操作
	 * @return \Phalcon\Session\Adapter\Files
	 */
	public  function getSession()
	{
		return FactoryDefault::getDefault()->get('session');
	}

	/**
	 * 创建url
	 * @param  $args
	 */
	public  function createUrl($args)
	{
		$config = FactoryDefault::getDefault()->get('config');
		$url = '/';
		$controllerGroup = array();
		foreach ($config->controller_group as $group)
		{
			$controllerGroup[$group->namespace] = $group->url;
		}

		$defaultControllerNameSpace = $config->default_controller_namespace;

		if(isset($args['m']) && !empty($args['m'])){
			if(!empty($controllerGroup[$args['m']]) && !$defaultControllerNameSpace!=$args['m'])
			{
				$url .= $controllerGroup[$args['m']].'/';
			}
			unset($args['m']);
		}else{
			$currentControllerGroup = $config->currentControllerGroup;
			if($currentControllerGroup!=$defaultControllerNameSpace)
			{
				$url .= $controllerGroup[$currentControllerGroup].'/';
			}
		}
		if(isset($args['p']) && !empty($args['p'])){
			$url .=$args['p'];
			unset($args['p']);
		}
		
		$isFullUrl = false;
		if(isset($args['_f']) && !empty($args['_f']))
		{
		   $isFullUrl = true;
		}
		unset($args['_f']);
		
		if(!empty($args)){
			$url .='?';
			foreach ($args as $key=>$value)
			{
				$url .=$key.'='.$value.'&';
			}
			$url = rtrim($url,'&');
		}
		
		if($isFullUrl)
		{
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
			$url = "$protocol$_SERVER[HTTP_HOST]".$url;
		}
		
		return $url;
	}

	/**
	 * 获取时间操作对象
	 * @return \ScshuxCms\Core\Helper\Time
	 */
	public function  getTime()
	{
		return new Time();
	}

	/**
	 * 格式化输出
	 * @param  $num
	 */
	public  function  formatNum4($num)
	{
		$str = '';
		for($i=0;$i<=strlen($num);$i++)
		{
			if($i%4==0 && $i>0){
				 $str.=' ';
			}
			$str .= substr($num, $i,1);
		}
		return $str;
	}

	/**
	 * 获取完整图片路径
	 * @param  $pic
	 */
	public  function getFullPic($pic)
	{
		if(empty($pic)) return '';
		return 'http://'.$_SERVER['HTTP_HOST'].$pic;
	}


	/*
	 * 格式化时间
	 */
	public  function formatDateTime($time,$format='Y-m-d H:i:s')
	{
		if(empty($time)) return '';
		return $this->getTime()->localDate($format,$time);
	}
	
	

	/**
	 * 字符截取
	 * @param  $str
	 * @param number $start
	 * @param number $length
	 * @param string $encoding
	 * @return string
	 */
	public  function  substr($str,$start=0,$length=0, $encoding='utf-8')
	{
		$str = strip_tags($str);
		if(mb_strlen($str,$encoding)>$length)
		{
			$str =  mb_substr($str, $start,$length,$encoding);
			$str .='...';
		}
		return $str;
	}

	/**
	 * 分割
	 * @param  $delimiter
	 * @param  $string
	 */
	public  function  explode($delimiter, $string)
	{
		 return explode($delimiter, $string);
	}


	/**
	 * 统计子串在字符串中出现的次数
	 * @param string $str
	 * @param string $needle
	 */
	public function substrCount($str,$needle)
	{
		return substr_count($str, $needle);
	}

	/**
	 * 把字符串重复指定的次数。
	 * @param string $str
	 * @param number $count
	 */
	public function strRepeat($str,$count)
	{
		return str_repeat($str, $count);
	}
	
	/**
	 * 计算某个经纬度的周围某段距离的正方形的四个点
	 * @param  radius 地球半径 平均6371km
	 * @param  lng float 经度
	 * @param  lat float 纬度
	 * @param  distance float 该点所在圆的半径，该圆与此正方形内切，默认值为1千米
	 * @return array 正方形的四个点的经纬度坐标
	 */
	public function getSquarePoint($lng, $lat, $distance = 1, $radius = 6371)
	{
		$dlng = 2 * asin(sin($distance / (2 * $radius)) / cos(deg2rad($lat)));
		$dlng = rad2deg($dlng);
	
		$dlat = $distance / $radius;
		$dlat = rad2deg($dlat);
	
		return array(
				'leftTop' => array(
						'lat' => $lat + $dlat,
						'lng' => $lng - $dlng
				),
				'rightTop' => array(
						'lat' => $lat + $dlat,
						'lng' => $lng + $dlng
				),
				'leftBottom' => array(
						'lat' => $lat - $dlat,
						'lng' => $lng - $dlng
				),
				'rightBottom' => array(
						'lat' => $lat - $dlat,
						'lng' => $lng + $dlng
				)
		);
	}
	
	/**
	 * @desc 根据两点间的经纬度计算距离
	 * @param float $lat 纬度值
	 * @param float $lng 经度值
	 */
	public function getDistance($lat1, $lng1, $lat2, $lng2)
	{
		$earthRadius = 6367100; //approximate radius of earth in meters
		$lat1 = ($lat1 * pi() ) / 180;
		$lng1 = ($lng1 * pi() ) / 180;
		$lat2 = ($lat2 * pi() ) / 180;
		$lng2 = ($lng2 * pi() ) / 180;
		$calcLongitude = $lng2 - $lng1;
		$calcLatitude = $lat2 - $lat1;
		$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
		$stepTwo = 2 * asin(min(1, sqrt($stepOne)));
		$calculatedDistance = $earthRadius * $stepTwo;
		return round($calculatedDistance);
	}
	
	/**
	 * 格式化距离
	 * @param  $distance
	 */
	public  function  formatDistance($distance)
	{
		$distanceLabel = '未知';
		if($distance<1000)
		{
			$distanceLabel = $distance.'m';
		}else{
			$distanceLabel = round($distance/1000,2).'km';
		}
		return $distanceLabel;
	}

	/**
	 * 通过ip获取数据
	 */
	public  function getLocationByIP($ip='')
	{
		
		if(empty($ip))$ip = Utils::getIP();
		$url = 'http://api.map.baidu.com/location/ip?ak=SA17jxvggMy28pLBrffpMm8NWtvhhGqF&coor=bd09ll&ip='.$ip;
		$result = Utils::httpGet($url);
		$result = json_decode($result);
		if($result->status == 0)
		{
			return array(
				'city' => $result->content->address_detail->city,
				'province' => $result->content->address_detail->province,
				'street' => $result->content->address_detail->street,
				'street_number' => $result->content->address_detail->street_number,
				'address' => $result->content->address,
				'longitude' => $result->content->point->x,
				'latitude' => $result->content->point->y
			);
		}
		return array();
	}


	/**
	 * 获取系统配置
	 */
	public function getConfig($key='')
	{
		if($this->_config==null)
		{
			$keyName = Constants::CACHE_SYSTEM_CONFIG;
			$configObj = $this->getCache()->get($keyName);
	
			if(empty($configObj)){
	
				$configs = ConfigModel::find();
				$configObj = new \stdClass();
				foreach ($configs as $config)
				{
					$configObj->{$config->code} = $config->value;
				}
				$this->getCache()->save($keyName,$configObj);
			}
			$this->_config = $configObj;
		}
		
		if(!empty($key)){
			return $this->_config->{$key};
		}
		return $this->_config ;

	}

	/**
	 * 获取缓存
	 * @return \Phalcon\Cache\Backend\File
	 */
	public function  getCache()
	{
		return FactoryDefault::getDefault()->get('cache');
	}

	/**
	 * @brief 获取客户端ip地址
	 * @return string 客户端的ip地址
	 */
	public function getIp()
	{
	    $realip = NULL;
	    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	    {
	    	$ipArray = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	    	foreach($ipArray as $rs)
	    	{
	    		$rs = trim($rs);
	    		if($rs != 'unknown')
	    		{
	    			$realip = $rs;
	    			break;
	    		}
	    	}
	    }
	    else if(isset($_SERVER['HTTP_CLIENT_IP']))
	    {
	    	$realip = $_SERVER['HTTP_CLIENT_IP'];
	    }
	    else
	    {
	    	$realip = $_SERVER['REMOTE_ADDR'];
	    }

	    preg_match("/[\d\.]{7,15}/", $realip, $match);
	    $realip = isset($match[0]) ? $match[0] : false;
	    return $realip;
	}

	/**
	 * json_decode
	 * @param  $json
	 * @param  $bool
	 */
	public function jsonDecode($json,$bool=true)
	{
		return json_decode($json,true);
	}
	

	/**
	 * 汉字转拼音
	 * @param  $s
	 * @param  $isfirst
	 * @return string
	 */
	public  function pinyin($s, $isfirst = false)
	{
		return Utils::pinyin($s,$isfirst);
	}
	
	/*
	 * 计算星座的函数
	 * 输入：月份，日期
	 * 输出：星座名称或者错误信息
	 */
	public  function  getZodiacSign($month, $day)
	{
		// 检查参数有效性
		if ($month < 1 || $month > 12 || $day < 1 || $day > 31)
			return '';
		// 星座名称以及开始日期
		$signs = array(
				array( "20" => "宝瓶座"),
				array( "19" => "双鱼座"),
				array( "21" => "白羊座"),
				array( "20" => "金牛座"),
				array( "21" => "双子座"),
				array( "22" => "巨蟹座"),
				array( "23" => "狮子座"),
				array( "23" => "处女座"),
				array( "23" => "天秤座"),
				array( "24" => "天蝎座"),
				array( "22" => "射手座"),
				array( "22" => "摩羯座")
		);
		list($sign_start, $sign_name) = each($signs[(int)$month-1]);
		if ($day < $sign_start){
			list($sign_start, $sign_name) = each($signs[($month -2 < 0) ? $month = 11: $month -= 2]);
		}
		return $sign_name;
	}
	
	
	/**
	 * 获取可用支付方式
	 */
	public function  getPayments()
	{
		$payments = array();
		
		$items = PaymentModel::factory()->find(array('status'=>1,'order'=>'sort desc,id desc'));
		foreach ($items as $item)
		{
			$payments[] = array(
					'payment_id' => $item->id,
					'payment_name'=> $item->name,
					'payment_ico' => $this->getFullPic($item->logo)
			);
		}
		
		return $payments;
	}
	
	
	/**
	 * 
	 * @desc 返回随机数 
	 * @date 2017年4月24日
	 */
	public function suiji()
	{
		return  md5(time().rand()) ;
	}
	
	
	/**
	 *
	 |+----------------------------------------
	 | 导出csv文件
	 | @param array $dataList    数据
	 | @param number $total_page 总页数
	 | @param string $newName    新文件名
	 | @param string $type    类型，1,2,3
	 |+----------------------------------------
	 */
	public function exportCsv($dataList,$total_page,$newName,$type)
	{
		$page = intval($_POST['page']);
		$page = $page <=1 ? 1 :$page;
		
		$key      = trim($_POST['key']);
		$filename = WEBROOT.'/var/temp/'.$key.'.csv';
		
		//判断目录是否存在  不存在则新建一个
		if(!is_dir(dirname($filename)))
		{
			mkdir(dirname($filename),'0777');
			chmod(dirname($filename),'0777');
		}

		
		//如果第一次运行已经存在，则删除
		if(file_exists($filename) && $page == 1){
			unlink($filename);
			 
			//重新生成文件名
			$key      = trim(Helper::factory()->getUniqueKey());
			$filename = WEBROOT.'/var/temp/'.$key.'.csv';
		}
		$stop = 'n';
		$info = '';
	
		if($dataList){
			if($page >= $total_page){
				$page = $total_page;
				$stop = 'y';
				$info = '导出数据成功';
			}else{
				$page += 1;
			}
			 
			//写数据
			$back = Helper::factory()->createCsvFile($filename,$dataList,$type);
			if($back === false){
				$this->sendErrorResult('导出数据失败，请稍后再试！');
			}
		}else{
			$stop = 'y';
			$info = '未查询到数据';
		}
		 
		$data = array(
				'info' => $info,
				'stop' => $stop,
				'page' => $page,
				'file' => $filename,
				'progress' => floor($page/$total_page * 100),
				'type' => 'csv',
				'name' => $newName,
				'key'  => $key
		);
		 
		return $data;
	}
	/**
	 *
	 |+----------------------------------------
	 | 导出数据为csv文件
	 | @param string $filename  文件名
	 | @param array $data       需要导出的数据
	 | @param string $type    类型，1,2,3
	 | @return boolean
	 |+----------------------------------------
	 */
	public function createCsvFile($filename,$data,$type)
	{
		if(empty($data)){
			return false;
		}
		$fp = fopen($filename, 'a');
		$header = $this->setcsvHeader() ;
		 
		//设置头部
		if(filesize($filename) == 0){
			$curr = isset($header[$type])?$header[$type]['header']:array();
			 
			if(empty($curr)){
				return false;
			}
			fwrite($fp,join(',',$curr)."\r\n");
		}
		 
		$currFiedls = $header[$type]['fields'];
		//获取字段
		if(empty($currFiedls)){
			return false;
		}
		 
		$currData = explode(',', $currFiedls);
		 
		$isobj = true ;
		foreach($data as $key=>$item){
			//判断是对象还是数组
			if(is_array($item))
			{
				$isobj = false ;
			}
			$warr = array();
			foreach($currData as $field){
	
				$field = trim($field);
	
				if($isobj)
				{
					$warr[] = iconv('UTF-8', 'GB2312',$item->{$field});
				}
				else
				{
					$warr[] = iconv('UTF-8', 'GB2312',$item[$field]);
				}
			}
			
			fwrite($fp,join(',',$warr)."\r\n");
		}
		fclose($fp);
		 
		return true;
	}
	
	
	
	public function setcsvHeader()
	{
		$header = array(
				'1' => array(
						'header' => array(
								iconv('UTF-8','GB2312', '被考核人姓名'),
								iconv('UTF-8','GB2312', '考核表名称'),
								iconv('UTF-8','GB2312', '部门'),
								iconv('UTF-8','GB2312', '总分数'),
								iconv('UTF-8','GB2312', '具体指标得分'),
						),
						'fields'  => 'uname,reportname,dname,totalpoint,quotapoint',
						'notice'   => '月度考核表'
				),
				'2' => array(
						'header' => array(
								iconv('UTF-8','GB2312', '被考核人姓名'),
								iconv('UTF-8','GB2312', '考核表名称'),
								iconv('UTF-8','GB2312', '部门'),
								iconv('UTF-8','GB2312', '指标名称'),
								iconv('UTF-8','GB2312', '指标类型'),
								iconv('UTF-8','GB2312', '评分人'),
								iconv('UTF-8','GB2312', '评分时间'),
								iconv('UTF-8','GB2312', '得分'),
						),
						'fields'  => 'username,reportname,dname,quotaname,quotaType,reportusername,reporttime,reportpoint',
						'notice'   => '月度考核表'
				),
				'3' => array(
						'header' => array(
								iconv('UTF-8','GB2312', '被考核人姓名'),
								iconv('UTF-8','GB2312', '考核表名称'),
								iconv('UTF-8','GB2312', '部门'),
								iconv('UTF-8','GB2312', '总分数'),
						),
						'fields'  => 'uname,reportname,dname,totalpoint',
						'notice'   => '积分考评表'
				),
				'4' => array(
						'header' => array(
								iconv('UTF-8','GB2312', '被考核人姓名'),
								iconv('UTF-8','GB2312', '考核表名称'),
								iconv('UTF-8','GB2312', '指标名称'),
								iconv('UTF-8','GB2312', '指标类型'),
								iconv('UTF-8','GB2312', '评分人'),
								iconv('UTF-8','GB2312', '评分时间'),
								iconv('UTF-8','GB2312', '得分'),
								iconv('UTF-8','GB2312', '缘由'),
						),
						'fields'  => 'reportusername,reportname,quotaname,quotatypename,username,created_at,point,reason',
						'notice'   => '积分考评表详细评分'
				),
		);
	
	
		return $header ;
	}
	
	
	/**
	 *
	 |+----------------------------------------
	 | 根据时间获取唯一Key
	 | @return mixed
	 |+----------------------------------------
	 */
	public function getUniqueKey()
	{
		return sprintf('%20s0',str_replace(array('.',' '), '', microtime()));
	}
	
	/**
	 *
	 * @desc  
	 * @param $str
	 * @date  2017年4月19日
	 */
	public function del0($str)
	{
		
		if($str==false)
		{
			return  ;
		}
	
		return floatval($str);
	}
	
	
	/**
	 * 
	 * @desc	判断字符串是否存在
	 * @param	$str	
	 * @param	$hasstr	
	 * @return	bool		
	 * @date	2017年5月10日
	 */
	public function checkStrpos($str,$hasstr)
	{
		if($str == 0)
		{
			return true ;
		}
		$pos = strpos($str, $hasstr) ;
		return  $pos ;
	}
	
	
	public function getDdJsApiconfig()
	{
		return Dding::factory()->getConfig() ;
	}
	
	
	/**
	 * @desc	返回不需要计算总权重的指标类型
	 * @param
	 * @return
	 */
	public function getExtratype()
	{
		return 4;
	}
	
	
	/**
	 * @desc	设置颜色
	 * @param			
	 * @return			
	 */
	public function setColor()
	{
		$args=func_get_args();
		$report_id=$args[0];
		$quota_id =$args[1];
		$reportuserids=$args[2];
		$reportusername=$args[3];
		
		$return='';
		$reportuseridsArr=explode(',', $reportuserids);
		$reportusernameArr=explode(',', $reportusername);
		foreach ($reportuseridsArr as $key=>$userid)
		{
			$color='red';
			$isExists=PointReportItemDetailModel::findFirst('report_id='.$report_id.' and quota_id='.$quota_id.' and user_id='.$userid);
			if ($isExists)
			{
				$color='blue';
			}
			$return.='<span class="statustext '.$color.'">'.$reportusernameArr[$key].'</span>&nbsp;';
		}
		
		return $return;
	}
	
	
}