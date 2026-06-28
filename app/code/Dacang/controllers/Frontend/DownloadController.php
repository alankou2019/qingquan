<?php
/**
 * 下载
*/
namespace ScshuxCms\Frontend\Controller;
use ScshuxCms\Core\Controller\FrontendBaseController;
class DownloadController extends FrontendBaseController
{
	/**
	 *
	 * desc 下载
	 * @date 2017年4月21日
	 */
	public function listAction()
	{
		$key = trim($_REQUEST['key']);
		$type= trim($_REQUEST['type']);
		$name= trim($_REQUEST['name']);
		
		if(in_array($type, array('csv'))){
			$filename = WEBROOT.'/var/temp/'.$key.'.'.$type;
			
			if(file_exists($filename)){
				$content = file_get_contents($filename);
		
				//文件类型
				$content_type = 'application/octet-stream';
				if(isset($contentTypeArr[$type])){
					$content_type = $contentTypeArr[$type];
				}
		
				header("Content-Type:".$content_type);
				header("Content-Disposition:attachment;filename=".$name.'.'.$type);
				header("Accept-ranges:bytes");
				header("Accept-Length:".filesize($filename));
				echo $content;
			}
		}
		
		exit;
	}
}