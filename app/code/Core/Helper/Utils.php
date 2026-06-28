<?php
/**
 * 全局工具类
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */

namespace ScshuxCms\Core\Helper;

use Phalcon\Di\FactoryDefault;
use ScshuxCms\Dacang\Model\ReportTplModel;
use ScshuxCms\Dacang\Model\PointReportTplModel;

class Utils
{

    /**
     *  短消息函数,可以在某个动作处理后友好的提示信息
     *
     * @param string $msg 消息提示信息
     * @param string $gourl 跳转地址
     * @param int $onlymsg 仅显示信息
     * @param int $limittime 限制时间
     * @return    void
     */
    public static function showMsg($msg, $gourl, $onlymsg = 0, $limittime = 0)
    {
        ob_clean();
        $htmlhead = "<html>\r\n<head>\r\n<title>系统提示信息</title>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no\">\r\n<meta name=\"renderer\" content=\"webkit\">\r\n<meta http-equiv=\"Cache-Control\" content=\"no-siteapp\" />";
        $htmlhead .= "<base target='_self'/>\r\n<style>div{line-height:160%;}</style></head>\r\n<body leftmargin='0' topmargin='0' style='text-align:center; background:#fff'>\r\n<center>\r\n<script>\r\n";
        $htmlfoot = "</script>\r\n</center>\r\n</body>\r\n</html>\r\n";

        $litime = ($limittime == 0 ? 1000 : $limittime);
        $func   = '';

        if ($gourl == '-1') {
            if ($limittime == 0) $litime = 5000;
            $gourl = "javascript:history.go(-1);";
        }

        if ($gourl == '' || $onlymsg == 1) {
            $msg = "<script>alert(\"" . str_replace("\"", "“", $msg) . "\");</script>";
        } else {
            //当网址为:close::objname 时, 关闭父框架的id=objname元素
            if (preg_match('/close::/', $gourl)) {
                $tgobj = trim(preg_replace('/close::/', '', $gourl));
                $gourl = 'javascript:;';
                $func  .= "window.parent.document.getElementById('{$tgobj}').style.display='none';\r\n";
            }

            $func .= "      var pgo=0;
			function JumpUrl(){
			if(pgo==0){ location='$gourl'; pgo=1; }
		}\r\n";
            $rmsg = $func;
            $rmsg .= "document.write(\"<br /><div id='message_box' style='width:450px;padding:0px;border:1px solid #DADADA;margin:0 auto;margin-top:30px;'>";
            $rmsg .= "<div style='padding:6px;color:#fff;font-size:12px;border-bottom:1px solid #DADADA;background:#4560e6';'><b>系统提示信息！</b></div>\");\r\n";
            $rmsg .= "document.write(\"<div style='height:130px;font-size:10pt;background:#ffffff'><br />\");\r\n";
            $rmsg .= "document.write(\"" . str_replace("\"", "“", $msg) . "\");\r\n";
            $rmsg .= "document.write(\"";

            if ($onlymsg == 0) {
                if ($gourl != 'javascript:;' && $gourl != '') {
                    $rmsg .= "<br /><a href='{$gourl}'>如果你的浏览器没反应，请点击这里...</a>";
                    $rmsg .= "<br/></div>\");\r\n";
                    $rmsg .= "setTimeout('JumpUrl()',$litime);";
                } else {
                    $rmsg .= "<br/></div>\");\r\n";
                }
            } else {
                $rmsg .= "<br/><br/></div>\");\r\n";
            }
            $msg = $htmlhead . $rmsg . $htmlfoot;
        }
        echo $msg;
        exit;
    }


    /**
     *  手机浏览器 layer 弹框提示
     *
     * @param string $msg 消息提示信息
     * @param string $gourl 跳转地址
     * @return    void
     */
    public static function showFrontMsg($msg, $gourl)
    {
        ob_clean();
        $html = "<html><head><meta charset='utf-8'><meta name='MobileOptimized' content='750'>";
        $html .= "<script type='text/javascript' src='/skin/frontend/default/bs/lib/jquery/jquery.min.js'></script>";
        $html .= "<script type='text/javascript' src='/skin/frontend/default/bs/lib/layer_mobile/layer.js'></script>";
        $html .= "</head>";
        $html .= "<body>";
        $html .= "</body>";
        if ($gourl) {
            $html .= "<script>layer.open({content: '" . $msg . "',btn: '我知道了',yes:function(index){layer.close(index);window.location.href = '" . $gourl . "' ;}});</script>";
        } else {
            $html .= "<script>layer.open({content: '" . $msg . "',btn: '我知道了'});</script>";
        }

        $html . +"</html>";
        echo $html;
        exit;
    }

    /*
     * 计算星座的函数
     * 输入：月份，日期
     * 输出：星座名称或者错误信息
     */

    public static function getZodiacSign($month, $day)
    {
        // 检查参数有效性
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31)
            return '';
        // 星座名称以及开始日期
        $signs = [
            ["20" => "宝瓶座"],
            ["19" => "双鱼座"],
            ["21" => "白羊座"],
            ["20" => "金牛座"],
            ["21" => "双子座"],
            ["22" => "巨蟹座"],
            ["23" => "狮子座"],
            ["23" => "处女座"],
            ["23" => "天秤座"],
            ["24" => "天蝎座"],
            ["22" => "射手座"],
            ["22" => "摩羯座"],
        ];
        list($sign_start, $sign_name) = each($signs[(int)$month - 1]);
        if ($day < $sign_start) {
            list($sign_start, $sign_name) = each($signs[($month - 2 < 0) ? $month = 11 : $month -= 2]);
        }
        return $sign_name;
    }


    /**
     * 获取ip
     * @return Ambigous <string, unknown>
     */
    public static function getIP()
    {
        if (isset($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]) && !empty($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"])) {
            $ip = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
        } else if (isset($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]) && !empty($HTTP_SERVER_VARS["HTTP_CLIENT_IP"])) {
            $ip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
        } else if (isset($HTTP_SERVER_VARS["REMOTE_ADDR"]) && !empty($HTTP_SERVER_VARS["REMOTE_ADDR"])) {
            $ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
        } else if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "Unknown";
        }
        return $ip;

    }

    /**
     * http post
     * @param string $url
     * @param string|array $data
     */
    public static function httpPost($url, $data, $timeout = 40, $header = '')
    {

        if (is_array($data)) {
            $data = http_build_query($data);
        }

        if (!function_exists("curl_init")) {
            die('undefined function curl_init');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //设置header头
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        /**************測試環境先不驗證ssl準確性**************/
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        /**************測試環境先不驗證ssl準確性**************/
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0');
        $rs = curl_exec($ch);
        curl_close($ch);
        return $rs;
    }

    /**
     * http get
     * @param string $url
     * @param number $timeout
     * @return mixed
     */
    public static function httpGet($url, $timeout = 40)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0');

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        /**************測試環境先不驗證ssl準確性**************/
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        /**************測試環境先不驗證ssl準確性**************/

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }


    /**
     *
     * @desc    postjson
     * @date    2017年5月8日
     */
    public static function postJson($url, $jsonstr)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonstr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonstr),
            ]
        );

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


    /**
     * 汉字转拼音
     * @param  $s
     * @param  $isfirst
     * @return string
     */
    public static function pinyin($s, $isfirst = false)
    {
        static $pinyins;

        $s   = trim($s);
        $len = strlen($s);
        if ($len < 3) return $s;

        if (!isset($pinyins)) {
            $data    = file_get_contents(dirname(__FILE__) . '/../data/pinying.data');
            $a1      = explode('|', $data);
            $pinyins = [];
            foreach ($a1 as $v) {
                $a2              = explode(':', $v);
                $pinyins[$a2[0]] = $a2[1];
            }
        }

        $rs = '';
        for ($i = 0; $i < $len; $i++) {
            $o = ord($s[$i]);
            if ($o < 0x80) {
                if (($o >= 48 && $o <= 57) || ($o >= 97 && $o <= 122)) {
                    $rs .= $s[$i]; // 0-9 a-z
                } else if ($o >= 65 && $o <= 90) {
                    $rs .= strtolower($s[$i]); // A-Z
                } else {
                    $rs .= '_';
                }
            } else {
                $z = $s[$i] . $s[++$i] . $s[++$i];
                if (isset($pinyins[$z])) {
                    $rs .= $isfirst ? $pinyins[$z][0] : $pinyins[$z];
                } else {
                    $rs .= '_';
                }
            }
        }
        return $rs;
    }

    /**
     * 检查文件类型
     *
     * @access      public
     * @param string      filename            文件名
     * @param string      realname            真实文件名
     * @param string      limit_ext_types     允许的文件类型
     * @return      string
     */
    public static function checkFileType($filename, $realname = '', $limit_ext_types = '')
    {
        if ($realname) {
            $extname = strtolower(substr($realname, strrpos($realname, '.') + 1));
        } else {
            $extname = strtolower(substr($filename, strrpos($filename, '.') + 1));
        }

        if ($limit_ext_types && stristr($limit_ext_types, '|' . $extname . '|') === false) {
            return '';
        }

        $str = $format = '';

        $file = @fopen($filename, 'rb');
        if ($file) {
            $str = @fread($file, 0x400); // 读取前 1024 个字节
            @fclose($file);
        } else {
            if (stristr($filename, ROOT_PATH) === false) {
                if ($extname == 'jpg' || $extname == 'jpeg' || $extname == 'gif' || $extname == 'png' || $extname == 'doc' ||
                    $extname == 'xls' || $extname == 'txt' || $extname == 'zip' || $extname == 'rar' || $extname == 'ppt' ||
                    $extname == 'pdf' || $extname == 'rm' || $extname == 'mid' || $extname == 'wav' || $extname == 'bmp' ||
                    $extname == 'swf' || $extname == 'chm' || $extname == 'sql' || $extname == 'cert' || $extname == 'pptx' ||
                    $extname == 'xlsx' || $extname == 'docx') {
                    $format = $extname;
                }
            } else {
                return '';
            }
        }

        if ($format == '' && strlen($str) >= 2) {
            if (substr($str, 0, 4) == 'MThd' && $extname != 'txt') {
                $format = 'mid';
            } else if (substr($str, 0, 4) == 'RIFF' && $extname == 'wav') {
                $format = 'wav';
            } else if (substr($str, 0, 3) == "\xFF\xD8\xFF") {
                $format = 'jpg';
            } else if (substr($str, 0, 4) == 'GIF8' && $extname != 'txt') {
                $format = 'gif';
            } else if (substr($str, 0, 8) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
                $format = 'png';
            } else if (substr($str, 0, 2) == 'BM' && $extname != 'txt') {
                $format = 'bmp';
            } else if ((substr($str, 0, 3) == 'CWS' || substr($str, 0, 3) == 'FWS') && $extname != 'txt') {
                $format = 'swf';
            } else if (substr($str, 0, 4) == "\xD0\xCF\x11\xE0") {   // D0CF11E == DOCFILE == Microsoft Office Document
                if (substr($str, 0x200, 4) == "\xEC\xA5\xC1\x00" || $extname == 'doc') {
                    $format = 'doc';
                } else if (substr($str, 0x200, 2) == "\x09\x08" || $extname == 'xls') {
                    $format = 'xls';
                } else if (substr($str, 0x200, 4) == "\xFD\xFF\xFF\xFF" || $extname == 'ppt') {
                    $format = 'ppt';
                }
            } else if (substr($str, 0, 4) == "PK\x03\x04") {
                if (substr($str, 0x200, 4) == "\xEC\xA5\xC1\x00" || $extname == 'docx') {
                    $format = 'docx';
                } else if (substr($str, 0x200, 2) == "\x09\x08" || $extname == 'xlsx') {
                    $format = 'xlsx';
                } else if (substr($str, 0x200, 4) == "\xFD\xFF\xFF\xFF" || $extname == 'pptx') {
                    $format = 'pptx';
                } else {
                    $format = 'zip';
                }
            } else if (substr($str, 0, 4) == 'Rar!' && $extname != 'txt') {
                $format = 'rar';
            } else if (substr($str, 0, 4) == "\x25PDF") {
                $format = 'pdf';
            } else if (substr($str, 0, 3) == "\x30\x82\x0A") {
                $format = 'cert';
            } else if (substr($str, 0, 4) == 'ITSF' && $extname != 'txt') {
                $format = 'chm';
            } else if (substr($str, 0, 4) == "\x2ERMF") {
                $format = 'rm';
            } else if ($extname == 'sql') {
                $format = 'sql';
            } else if ($extname == 'txt') {
                $format = 'txt';
            }
        }

        if ($limit_ext_types && stristr($limit_ext_types, '|' . $format . '|') === false) {
            $format = '';
        }

        return $format;
    }

    /**
     * 根据file控件的名字上传
     * @param string $fileName
     */
    public static function uploadFile($fileName = '', $dirname = 'images', $fix = '')
    {

        if (!isset($_FILES[$fileName]) || empty($_FILES[$fileName]['tmp_name'])) {
            return '';
        }

        $limit_ext_types = [
            'png', 'gif', 'jpg', 'jpeg', 'bmp', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx',
        ];

        $dir = WEBROOT . '/media/' . $dirname . '/' . date('Y-m-d') . '/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $tempName = $_FILES[$fileName]['tmp_name'];
        $ext      = self::checkFileType($tempName, '', $limit_ext_types);
        if ($ext) {
            $newFile = $dir . md5(microtime(true)) . '.' . ($fix ? $fix : $ext);
            if (move_uploaded_file($tempName, $newFile)) {
                return str_replace(WEBROOT, "", $newFile);
            }
        }
        return '';
    }

    /**
     * 多图上传
     * @param string $fileName
     */
    public static function uploadFiles($fileName = '', $dirname = 'images', $fix = '')
    {

        $limit_ext_types = [
            'png', 'gif', 'jpg', 'jpeg', 'bmp', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx',
        ];
        $fileObj         = CUploadedFile::getInstancesByName($fileName);

        $dir = Yii::getPathOfAlias('webroot') . '/uploads/' . $dirname . '/' . date('Y-m-d') . '/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        if ($fileObj) {
            $fileArr = [];
            foreach ($fileObj as $item) {

                if (self::checkFileType($item->tempName, $item->extensionName, $limit_ext_types)) {
                    $newFile = $dir . md5(microtime(true)) . '.' . ($fix ? $fix : $item->extensionName);
                    if ($item->saveAs($newFile)) {
                        $fileArr[] = str_replace(Yii::getPathOfAlias('webroot'), "", $newFile);
                    }
                }
            }

            return $fileArr;
        }

        return '';
    }


    /**
     * PHP DES 加密程式
     *
     * @param $key 密鑰（八個字元內）
     * @param $encrypt 要加密的明文
     * @return string 密文
     */
    public static function encrypt($encrypt)
    {
        $key = Constants::DES_KEY;
        // 根據 PKCS#7 RFC 5652 Cryptographic Message Syntax (CMS) 修正 Message 加入 Padding
        $block   = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
        $pad     = $block - (strlen($encrypt) % $block);
        $encrypt .= str_repeat(chr($pad), $pad);

        // 不需要設定 IV 進行加密
        $passcrypt = mcrypt_encrypt(MCRYPT_DES, $key, $encrypt, MCRYPT_MODE_ECB);
        $result    = base64_encode($passcrypt);
        return urlencode($result);
    }

    /**
     * PHP DES 解密程式
     *
     * @param $key 密鑰（八個字元內）
     * @param $decrypt 要解密的密文
     * @return string 明文
     */
    public static function decrypt($decrypt)
    {
        $key     = Constants::DES_KEY;
        $decrypt = urldecode($decrypt);

        // 不需要設定 IV
        $str = mcrypt_decrypt(MCRYPT_DES, $key, base64_decode($decrypt), MCRYPT_MODE_ECB);

        // 根據 PKCS#7 RFC 5652 Cryptographic Message Syntax (CMS) 修正 Message 移除 Padding
        $pad = ord($str[strlen($str) - 1]);
        return substr($str, 0, strlen($str) - $pad);
    }

    /**
     * @param string $phone
     * @return string
     * 隐藏手机号的中间部分
     */
    public static function mobileshow($phone)
    {
        $phone = strval($phone);
        if (empty($phone)) {
            return '';
        }
        $phone = substr_replace($phone, '****', 3, 4);
        return $phone;
    }

    /**
     * 验证输入的邮件地址是否合法
     *
     * @access  public
     * @param string $email 需要验证的邮件地址
     *
     * @return bool
     */
    public static function isEmail($email)
    {
        $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
        if (strpos($email, '@') !== false && strpos($email, '.') !== false) {
            if (preg_match($chars, $email)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 检查是否为一个合法的时间格式
     *
     * @access  public
     * @param string $time
     * @return  void
     */
    public static function isTime($time)
    {
        $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';

        return preg_match($pattern, $time);
    }

    /**
     * 验证手机号是否正确
     * @param number $mobile
     * @author honfei
     */
    public static function isMobile($mobile)
    {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }

    /**
     * 是否是数字
     * @param  $num
     * @return boolean
     */
    public static function isNumber($num)
    {
        if (preg_match("/^\d*$/", $num)) {
            return true;
        }
        return false;
    }


    /**
     * 计算时间差
     * @param int $timestamp1 时间戳开始
     * @param int $timestamp2 时间戳结束
     * @return array
     */
    public static function timeDiff($timestamp1, $timestamp2, $needLabel = false)
    {
        if ($timestamp2 <= $timestamp1) {
            if ($needLabel) {
                return '';
            }
            return ['hours' => 0, 'minutes' => 0, 'seconds' => 0];
        }
        $timediff = $timestamp2 - $timestamp1;
        // 时
        $remain = $timediff % 86400;
        $hours  = intval($remain / 3600);

        // 分
        $remain = $timediff % 3600;
        $mins   = intval($remain / 60);
        // 秒
        $secs = $remain % 60;

        $time = ['hours' => $hours, 'minutes' => $mins, 'seconds' => $secs];

        if ($needLabel) {
            $label = '';
            if ($time['hours'] > 0) {
                $label .= $time['hours'] . '小时';
            }
            if ($time['minutes'] > 0) {
                $label .= $time['minutes'] . '分钟';
            }
            if ($time['seconds'] > 0) {
                $label .= $time['seconds'] . '秒';
            }
            return $label;
        }
        return $time;
    }

    /**
     * 生成二维码
     * @param  $value
     * @param  $dir
     * @param  $size
     * @return mixed
     */
    public static function makeQrcode($value, $size = 6, $dir = 'qrcode')
    {
        $webdir = dirname(__FILE__) . '/../';
        $key    = md5($value . '_' . $size);

        $dirKey         = substr($key, 0, 2);
        $dir            = WEBROOT . '/media/' . $dir . '/' . $dirKey . '/';
        $qrcodeFullPath = $dir . $key . '.png';
        if (!file_exists($qrcodeFullPath)) {

            include_once WEBROOT . '/lib/phpqrcode/phpqrcode.php';

            if (!file_exists(dirname($qrcodeFullPath))) {
                mkdir(dirname($qrcodeFullPath), 0777, true);
            }

            $errorCorrectionLevel = 'L';//容错级别
            //$matrixPointSize = 6;//生成图片大小
            //生成二维码图片
            //4 246X246  3 234X234  5 258x258 6 270X270 2 222X222
            \QRcode::png($value, $qrcodeFullPath, $errorCorrectionLevel, $size, 5);
        }
        return str_replace(WEBROOT, '', $qrcodeFullPath);
    }


    /**
     * 生成二维码流
     * @param  $value
     * @param  $dir
     * @param  $size
     * @return mixed
     */
    public static function makeHttpQrcode($value, $size = 6)
    {
        include_once WEBROOT . '/lib/phpqrcode/phpqrcode.php';
        ob_clean();
        header('Content-Type: image/png');
        header('Content-Disposition: inline; filename="qrcode.png"');
        $errorCorrectionLevel = 'L';//容错级别
        \QRcode::png($value, false, $errorCorrectionLevel, $size, 5, 0);
    }


    /**
     *
     * @desc    生成随机字符串
     * @date    2017年5月8日
     */
    public static function createRandStr($len = 10)
    {
        $return = '';

        $str    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $strlen = strlen($str) - 1;
        for ($i = 0; $i < $len; $i++) {
            $randnum = rand(0, $strlen);
            $return  .= $str[$randnum];
        }

        return $return;
    }


    /**
     *
     * @desc    读取excel
     * @param    $file    绝对路径
     * @pram    $array  一个字段值与excel栏目对应的数组
     * @return    $array();
     * @date    2017年5月12日
     */
    public static function readExcel($file, $array)
    {

        $return = [];
        if (!$file || !$array || !is_array($array)) {
            return $return;
        }

        //不能删除
        $phpexcel = FactoryDefault::getDefault()->get('phpexcel');

        $objPHPExcel  = \PHPExcel_IOFactory::load($file);
        $currentSheet = $objPHPExcel->getSheet(0);
        $allColumn    = $currentSheet->getHighestColumn();
        $allRow       = $currentSheet->getHighestRow();

        //循环每一行数据  添加到array
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $arr = [];
            foreach ($array as $field => $position) {
                $arr[$field] = htmlspecialchars($currentSheet->getCell($position . $currentRow)->getValue());
            }
            $return[] = $arr;
        }

        return $return;

    }


    /**
     * @desc    计算报表总分
     * @param    $items
     * @return
     */
    public static function totalScore($items)
    {
        $return = 0;
        if (!$items) {
            return $return;
        }

        foreach ($items as $item) {
            $point  = self::workScode($item);
            $return += $point;
        }

        $return = sprintf("%.2f", $return);
        return $return;
    }


    /**
     * @desc    KPI考评 计算总分数
     * @param
     * @return
     */
    public static function workScode($item)
    {
        if (!$item) {
            return 0;
        }
        $item = (object)$item;
        switch ($item->type) {
            case 1:
                //百分制
                $point = ($item->quota_total * $item->quota_value * $item->report_point) / 10000;
                break;
            case 2:
                //十分制
                $point = ($item->quota_total * $item->quota_value * $item->report_point) / 1000;
                break;
            case 3:
                //权重制
                $point = ($item->quota_total * $item->report_point) / 100;
                break;
            case 4:
                //加减分
                $point = floatval($item->report_point);
                if (self::isMinusScoreItem($item)) {
                    $point = -abs($point);
                }
                break;
            case 5:
                //五分制
                $point = ($item->quota_total * $item->quota_value * $item->report_point) / 500;
        }
        return $point;
    }

    /**
     * @desc    判断加减分指标是否为减分项
     * @param   object|array $item
     * @return  bool
     */
    public static function isMinusScoreItem($item)
    {
        if (!$item) {
            return false;
        }
        $item = (object)$item;
        $name = '';
        foreach (array('qname', 'quota_name', 'name') as $field) {
            if (isset($item->{$field}) && $item->{$field} !== '') {
                $name = $item->{$field};
                break;
            }
        }
        return strpos($name, '减分') !== false || strpos($name, '扣分') !== false;
    }


    /**
     * @desc    积分考评 计算总分数
     * 分人权重制 计算方式为分数累加 不能超过指标的权权重
     * @param
     * @return
     */
    public static function PointTotalScore($items)
    {
        $return = 0;
        if (!$items) {
            return $return;
        }

        //循环所有指标   不是权重制的 直接累计相加算总分
        //是权重制  则按照指标累加得分  然后在循环判断  指标所得的总分是否超过指标的权重
        $arr = [];
        foreach ($items as $item) {
            $point = $item->point;
            if ($item->type == 3) {
                $arr[$item['quota_id']]['hasPoint']   += $point;
                $arr[$item['quota_id']]['quotaValue'] = $item['quota_value'];
            } else {
                $return += $point;
            }
        }

        if (!empty($arr)) {
            foreach ($arr as $value) {
                $point  = $value['hasPoint'] > $value['quotaValue'] ? $value['quotaValue'] : $value['hasPoint'];
                $return += $point;
            }
        }
        $return = sprintf("%.2f", $return);
        return $return;
    }


    /**
     * @desc    计算当前已经生成的模版数(积分+KPI)
     * @param
     * @return
     */
    public static function getTplNum($companyId)
    {
        $companyId = intval($companyId);
        $where     = 'company_id = ' . $companyId;
        $db        = FactoryDefault::getDefault()->get('db');
        //kpi
        $kpiSql = 'select count(*) as num from ' . ReportTplModel::factory()->getSource() . '  where ' . $where;
        //积分
        $pointSql = 'select count(*) as num from ' . PointReportTplModel::factory()->getSource() . ' where ' . $where;
        $res1     = $db->query($kpiSql)->fetch();
        $res2     = $db->query($pointSql)->fetch();


        return $res1['num'] + $res2['num'];
    }


    /**
     * @desc    格式化树
     * @param
     * @return
     */
    public static function formatTree($departList)
    {

        foreach ($departList as $key => $val) {

            $val->path = $key > 0 ? ($val->dingding_parent_id . '_' . $val->dingding_id) : $val->dingding_id;
            if ($key == 0) {
                $val->dingding_id;
            } else {
                if ($val->level > $departList[$key - 1]->level) {
                    $val->path = $departList[$key - 1]->path . '_' . $val->dingding_id;
                } else {
                    $val->path = $val->dingding_parent_id . '_' . $val->dingding_id;
                }
            }
        }

        return $departList;
    }
}
