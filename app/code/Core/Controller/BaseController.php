<?php
/**
 * 基础控制类
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Core\Controller;

use ScshuxCms\Core\Helper;
/**
 * Phalcon\Di\Injectable
 *
 * This class allows to access services in the services container by just only accessing a public property
 * with the same name of a registered service
 *
 * @property \Phalcon\Mvc\Dispatcher $dispatcher
 * @property \Phalcon\Mvc\Router|\Phalcon\Mvc\RouterInterface $router
 * @property \Phalcon\Mvc\Url|\Phalcon\Mvc\UrlInterface $url
 * @property \Phalcon\Http\Request $request
 * @property \Phalcon\Http\Response $response
 * @property \Phalcon\Http\Response\Cookies|\Phalcon\Http\Response\CookiesInterface $cookies
 * @property \Phalcon\Filter|\Phalcon\FilterInterface $filter
 * @property \Phalcon\Flash\Direct $flash
 * @property \Phalcon\Flash\Session $flashSession
 * @property \Phalcon\Session\Adapter\Files $session
 * @property \Phalcon\Events\Manager|\Phalcon\Events\ManagerInterface $eventsManager
 * @property \Phalcon\Db\AdapterInterface $db
 * @property \Phalcon\Security $security
 * @property \Phalcon\Crypt|\Phalcon\CryptInterface $crypt
 * @property \Phalcon\Tag $tag
 * @property \Phalcon\Escaper|\Phalcon\EscaperInterface $escaper
 * @property \Phalcon\Annotations\Adapter\Memory|\Phalcon\Annotations\Adapter $annotations
 * @property \Phalcon\Mvc\Model\Manager|\Phalcon\Mvc\Model\ManagerInterface $modelsManager
 * @property \Phalcon\Mvc\Model\MetaData\Memory|\Phalcon\Mvc\Model\MetadataInterface $modelsMetadata
 * @property \Phalcon\Mvc\Model\Transaction\Manager|\Phalcon\Mvc\Model\Transaction\ManagerInterface $transactionManager
 * @property \Phalcon\Assets\Manager $assets
 * @property \Phalcon\DI\FactoryDefault $di
 * @property \Phalcon\Session\Bag|\Phalcon\Session\BagInterface $persistent
 * @property \Phalcon\Mvc\View $view
 */
class  BaseController extends \Phalcon\Mvc\Controller
{
	
	/**
	 * 
	 * @return \Phalcon\Mvc\View()
	 */
	public  function  getView()
	{
		return $this->view;
	}
	
	
	/**
	 * 获取帮助器
	 * @return \ScshuxCms\Core\Helper
	 */
	public function  getHelper()
	{
		return $this->getDI()->get('helper');
	}
	
	
	/**
	 * 发送正确请求结果
	 * @param string $data
	 */
	protected  function sendSuccessResult($data = null)
	{
		$result = array(
				'status' => 'y',
				'data' => $data
		);
		$this->sendJson($result);
	}
	
	/**
	 * 发送错误请求结果
	 * @param string $error
	 */
	protected  function sendErrorResult($error='')
	{
		$result = array(
				'status' => 'n',
				'error' => $error
		);
		$this->sendJson($result);
	}
	
	/**
	 * 發送json數據
	 * @param  $data
	 */
	protected  function sendJson($data)
	{
		if (is_array($data) || is_object($data)){
			$data = json_encode($data);
		}
		header('Content-Type:application/json; charset=utf-8');
		header('Content-Length:' . strlen($data));
		echo $data;
		//file_put_contents('send.log', date('Y-m-d H:i:s').':'.$data.PHP_EOL,FILE_APPEND);
		exit();
	}
	
	/**
	 * 重定向
	 * @param string|array $redirect
	 */
	protected  function  redirect($redirect)
	{
		if(is_array($redirect))
		{
			$redirect = Helper::factory()->createUrl($redirect);
		}elseif(strpos($redirect, "http")!==0)
		{
			$redirect =  Helper::factory()->createUrl(array('p'=>$redirect));
		}
		ob_clean();
		header('Location:'.$redirect);
		exit;
	}
	
}