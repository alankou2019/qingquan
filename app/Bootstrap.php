<?php
/**
 * 系统内核引导类
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace ScshuxCms;

use ScshuxCms\Core\Router;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use ScshuxCms\Common\Model\ModuleModel;
use ScshuxCms\Core\Helper;
use ScshuxCms\Core\Seo;
include_once APPROOT.'/code/Core/functions.php';

class  Bootstrap
{

	const DEFAULT_TIMEZONE = 'UTC';

	/**
	 * 模式容器
	 * @var \Phalcon\DI\FactoryDefault
	 */
	private $di = '';

	/**
	 * 应用实例
	 * @var \Phalcon\Mvc\Application
	 */
	private  $application = '';

	/**
	 * 启用的moduls
	 * @var array
	 */
	private  $runModulesCodePool = array();



	/**
	 * 初始化
	 */
	public  function __construct()
	{
		//加载全局函数
		require_once APPROOT . '/code/Core/functions.php';

		$this->di = new \Phalcon\DI\FactoryDefault();

		//加载系统配置
		require_once APPROOT . '/code/Core/Config.php';
		$config = \ScshuxCms\Core\Config::get();
		$this->di->set('config', $config);
		$applicationEnv = isset($config->application_env)?$config->application_env:'development';
		define('APPLICATION_ENV', $applicationEnv);
		
	}

	/**
	 * 运行系统
	 */
	public  function run()
	{
		
		$this->initEnvironment();

		//加载模块
		$this->initModules();

		//加载控制器
		$this->initRouting();

		$this->initView();

		$this->initSession();

		$this->initDb();

		$this->initCache();
		
		$this->initModules();
		
		$this->installModules();
		
		$this->SetPhpExcel() ;
		try {

			$this->application = new \Phalcon\Mvc\Application($this->di);
			$this->initDispatcher();
			$response = $this->application->handle(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
			echo Seo::handle($response->getContent());//seo设置
			
		} catch (\Exception $e) {
			echo $e->getMessage();
		}

	}

	/**
	 * 初始化视图
	 */
	private function initView()
	{

		$config = $this->di->get('config');

		$mainViewPath = '../';
		$view = new \Phalcon\Mvc\View();
		$view->setDI($this->di);
		$view->setMainView($mainViewPath . '/Layouts/main');
		$view->setPartialsDir($mainViewPath . '/Partials/');
		//初始化系统全局助手
		$this->di->set('helper', new \ScshuxCms\Core\Helper());

		$compiledPath = WEBROOT .'/var/compile/';
		if(!file_exists($compiledPath)){
			mkdir($compiledPath,0777,true);
		}

		$volt = new \Phalcon\Mvc\View\Engine\Volt($view,$this->di);
		$volt->setOptions(["compiledPath"=>$compiledPath]);
		$volt->getCompiler()->addFunction('helper', function(){
			return '$this->helper';
		});
		$phtml = new \Phalcon\Mvc\View\Engine\Php($view, $this->di);
		$viewEngines = [
				".volt"  => $volt,
				".phtml" => $phtml,
		];
		$view->registerEngines($viewEngines);
		$this->di->set('view', $view);
		return $view;

	}

	/**
	 * 初始化数据库
	 * 注意 phalcon在链接不上数据库的时候    会提示语法错误
	 */
	private  function  initDb()
	{
		$di = $this->di;
		$this->di->set('db', function()use ($di){
			 $config = $di->get('config');
			 return new \Phalcon\Db\Adapter\Pdo\Mysql([
					"host"     => $config->database->host,
					"username" => $config->database->username,
					"password" => $config->database->password,
					"dbname"   => $config->database->dbname,
					"charset"  => $config->database->charset,
			 		"port"     => $config->database->port,
			 		"prefix"   => $config->database->prefix,
			]);

		});
	}

	/**
	 * 初始化会话
	 * @return
	 */
	private  function  initSession()
	{
		$di = $this->di;
		$this->di->setShared('session', function ()use ($di){

			$config = $di->get('config');
			if (class_exists('\Phalcon\Session\Manager')) {
				return \ScshuxCms\Core\Session\SessionFactory::create($config);
			}

			if($config->session->adapter == 'Files'){
				$seesionPath = WEBROOT.'/var/session/';
				if(!file_exists($seesionPath)){
					mkdir($seesionPath,0777,true);
				}
				session_save_path($seesionPath);
			}

			if(isset($config->session->options->name)){
				session_name($config->session->options->name);
			}

			$adapterClass = 'Phalcon\Session\Adapter\\' . $config->session->adapter;
			$options =array();
			foreach ($config->session->options as $key=>$value)
			{
				$options[$key] = $value;
			}
			$session = new $adapterClass($options);
			$session->start();
			return $session;

		});
	}

	/**
	 * 初始化缓存
	 */
	private function initCache()
	{
		$config = $this->di->get('config');
		if (class_exists('\Phalcon\Cache\Cache')) {
			$this->di->set('cache', function() use ($config) {
				return \ScshuxCms\Core\Cache\CacheFactory::create($config);
			});
			return;
		}

		$this->di->set('cache', function() use ($config){
			
			if (!isset($config->cache->lifetime)){
				$config->cache->lifetime = 3600;
			}

			//默认缓存时间为一天
			$frontCache = new \Phalcon\Cache\Frontend\Data(array(
					"lifetime" => $config->cache->lifetime
			));

			if ($config->cache->adapter == 'File'){
				$cachePath = WEBROOT.'/var/cache/';
				if(!file_exists($cachePath)){
					mkdir($cachePath,0777,true);
				}
				$config->session->options->cacheDir = $cachePath;
			}
			$adapterClass = '\Phalcon\Cache\Backend\\' . $config->cache->adapter;
			$options =array();
			foreach ($config->session->options as $key=>$value)
			{
				$options[$key] = $value;
			}
			$cache = new $adapterClass($frontCache,$options);
			
			//开发模式不用缓存数据
			if(APPLICATION_ENV == 'development')
			{
				//$cache->flush();
			}
			
			return $cache;

		});

	}


	/**
	 * 加载model
	 */
	private  function  initModules()
	{

		 $config = $this->di->get('config');
		 $moduleConfigDir = APPROOT.'/etc/modules/';
		 $dirHandle = opendir($moduleConfigDir);
		 $configData = array();
		 while (($file=readdir($dirHandle))!=false)
		 {
	 		if(strpos($file, '.xml'))
	 		{
	 			$fullPath = $moduleConfigDir .$file;
	 			$configData = array_merge_recursive($configData,xmlFileToArr($fullPath));
	 		}
		 }
		 closedir($dirHandle);
		 $autoLoadNameSpaces = array();

		 $modules = array();
		 foreach ($configData['modules'] as $moule=>$moduleConfig)
		 {
		 	 if($moduleConfig['active'] || $moduleConfig['system'])
		 	 {
		 	 	 $tempAutoLoadNameSpaces = array(
		 	 		 'ScshuxCms\\'.$moduleConfig['codePool']=> APPROOT. "/code/".$moduleConfig['codePool'].'/',
		 	 	 	 'ScshuxCms\\'.$moduleConfig['codePool'].'\Helper'=> APPROOT. "/code/".$moduleConfig['codePool'].'/Helper/',
		 	 	 	 'ScshuxCms\\'.$moduleConfig['codePool'].'\Model'=> APPROOT. "/code/".$moduleConfig['codePool'].'/Model/',
		 	 	 	 'ScshuxCms\\'.$moduleConfig['codePool'].'\Controller'=> APPROOT. "/code/".$moduleConfig['codePool'].'/Controller/',
		 	 	 );
		 	 	 $autoLoadNameSpaces = array_merge($tempAutoLoadNameSpaces,$autoLoadNameSpaces);
		 	 	 if(!in_array($moduleConfig['codePool'],$this->runModulesCodePool))
		 	 	 {
		 	 		 $this->runModulesCodePool[] = $moduleConfig['codePool'];
		 	 	 }
		 	 }
		 }
		//注册加载
		$loaderClass = class_exists('\Phalcon\Autoload\Loader') ? '\Phalcon\Autoload\Loader' : '\Phalcon\Loader';
		$loader = new $loaderClass();
	 	$loader->registerNamespaces($autoLoadNameSpaces);
	 	$loader->register();

	}

	/**
	 * 注册URL
	 */
	private  function  initRouting()
	{

		$config = $this->di->get('config');


		$basePath = $config->base_path;

		//获取默认控制器空间
		$requestUri = $_SERVER['REQUEST_URI'];
		$urlInfoArr = explode('/', $requestUri);
		$defaultControllerNameSpace = $config->default_controller_namespace;

		if(isset($urlInfoArr[1]) && !empty($urlInfoArr[1]))
		{
			foreach ($config->controller_group as $controllerGroup)
			{
				if($controllerGroup->url == $urlInfoArr[1])
				{
					$defaultControllerNameSpace = $controllerGroup->namespace;
					$basePath = $basePath.$controllerGroup->url.'/';
					break;
				}
			}
		}


		//动态加载需要的控制器
		foreach ($this->runModulesCodePool as $codePool)
		{
			$controllerDir =  APPROOT."/code/".$codePool.'/controllers/'.$defaultControllerNameSpace;
			if(file_exists($controllerDir))
			{
				$dirHandle = opendir($controllerDir);
				while (($file = readdir($dirHandle))!=false)
				{
					if(strpos($file, '.php'))
					{
						include_once $controllerDir.'/'.$file;
						$className = str_replace('.php', '', $file);
						$config->controllerFiles->{$className} = array(
								'codePool' => $codePool,
								'file' => $controllerDir.'/'.$file,
								'group' => $defaultControllerNameSpace
						);
					}
				}
			}
		}

		//注册url管理
		$urlObj = new  \ScshuxCms\Core\Url();
		$urlObj->setBasePath($basePath);
		$urlObj->setBaseUri($basePath);
		$this->di->set("url",$urlObj);

		//url路由
		$router = new Router();
		$router->initUrl($basePath);
		$this->di->set('router',$router);

		$config->defaultControllerNameSpace = $defaultControllerNameSpace;

	}


	/**
	 * 加载分发器
	 */
	private  function  initDispatcher()
	{
		$di = $this->di;
		$view = $di->get('view');
		$eventsManager = $di->getShared('eventsManager');
		$config = $this->di->get('config');


		//加载前处理的事件
		$eventsManager->attach("dispatch:beforeDispatchLoop", function ($event, $dispatcher) use ($di,$view,$config) {

			/* @var $dispatcher \Phalcon\Mvc\Dispatcher */

			$controllerName =  $this->di->get('router')->getControllerName();
			$actionName = $this->di->get('router')->getActionName();
			$controllerClass  =  $dispatcher->getControllerClass();
			$controllerClassInfo = explode('\\', $controllerClass);
			$controllerClassIndex = count($controllerClassInfo)-1;
			$controllerFileName = $controllerClassInfo[$controllerClassIndex];
			foreach ($config->controllerFiles as $tempControllerName =>$tempControllerInfo)
			{
				if(strtolower($tempControllerName) == strtolower($controllerFileName))
				{
					$viewsDir = APPROOT.'/design/'.strtolower($tempControllerInfo['group']).'/default/'.$tempControllerInfo['codePool'];
					$view->setViewsDir($viewsDir);
					$config->currentControllerGroup = $tempControllerInfo['group'];
				}
			}

		});

		//加载异常处理的事件
		$eventsManager->attach(
				"dispatch:beforeException",
				function($event, $dispatcher, $exception)
				{
					switch ($exception->getCode()) {
						case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
						case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
							 //exit('404');
							return false;
					}
				}
		);

		//加载完成处理的事件
		$eventsManager->attach("dispatch:afterDispatchLoop", function ($event, $dispatcher) use ($di,$view) {

		});
		$dispatcher = new Dispatcher($this->di);
		$dispatcher->setDefaultNamespace('ScshuxCms\\'.$config->defaultControllerNameSpace.'\Controller');

		$dispatcher->setEventsManager($eventsManager);
		$this->di->set(
				"dispatcher",$dispatcher
		);
	}


	/**
     * 初始化php系统
     */
    private function initEnvironment()
    {
        //$this->setErrorHandler(self::DEFAULT_ERROR_HANDLER);
        error_reporting(E_ALL || ~E_NOTICE || ~E_WARNING);
        date_default_timezone_set(self::DEFAULT_TIMEZONE);
        return $this;
    }
    
    /**
     * 升级系统
     */
    private  function  installModules()
    {
    	//开发环境下检查模块版本
    	if(APPLICATION_ENV == 'development')
    	{
    		foreach ($this->runModulesCodePool as $module)
    		{
    			$configFile = APPROOT .'/code/'.$module.'/etc/config.xml';
    			if(file_exists($configFile))
    			{
    			   $version = '';
    			   preg_match('/<version>(.*)<\/version>/i', file_get_contents($configFile),$version);
    			   if(isset($version[1]) && !empty($version[1]))
    			   {
    			   		$version  = $version[1];
    			   		$description = '';
    			   		preg_match('/<description>(.*)<\/description>/i', file_get_contents($configFile),$description);
    			   		$description = isset($description[1])?$description[1]:'';
    			   		$needInstall = false;
    			   		//读取数据库中的版本进行比较
    			   		$moduleInfo  = ModuleModel::factory()->findFirst("name='{$module}'");
    			   		$moduleRuntiemVersion = '';
    			   		if(empty($moduleInfo))
    			   		{
    			   			$modleModel = new ModuleModel();
						$modleModel->saveData(array(
    			   			   'name' => $module,
    			   			   'version' => $version,
    			   			   'created' => Helper::factory()->getTime()->gmtime(),
    			   			   'is_active' => 1,
    			   			   'description' => $description
    			   			));
    			   			$needInstall = true;
    			   			$moduleRuntiemVersion = '0.0.0.0';
    			   		}else
    			   		{
    			   			if(version_compare($moduleInfo->version,$version,'<')){
    			   				$needInstall = true;
    			   				$moduleRuntiemVersion = $moduleInfo->version; 
    			   			}
    			   		}
    			   		if($needInstall) //运行升级sql
    			   		{
							$sqldir = APPROOT.'/code/'.$module.'/sql/';
							if(file_exists($sqldir)){
								$installFiles = array();
								$dirHandle = opendir($sqldir);
								while (($file = readdir($dirHandle)) !== false)
								{
									if(!preg_match('/^install-(.+)\.php$/', $file, $matches)){
										continue;
									}
									$installFiles[] = array(
										'file' => $file,
										'version' => trim($matches[1])
									);
								}
								closedir($dirHandle);
								usort($installFiles, function($left, $right){
									return version_compare($left['version'], $right['version']);
								});
								foreach($installFiles as $installFile)
								{
									$file = $installFile['file'];
									$fileversion = $installFile['version'];
									if(version_compare($moduleRuntiemVersion, $fileversion,'<') && version_compare($fileversion,$version,'<='))
									{
										include_once $sqldir.$file;
										$moduleInfo  = ModuleModel::factory()->findFirst("name='{$module}'");
										$moduleInfo->saveData(array(
											'version' => $fileversion,
											'created' => Helper::factory()->getTime()->gmtime(),
											'description' => $description
										));
									}
								}
							}
    			   		}
    			   }
    			}
    		}
    	}
    }
    
    
    
    /**
     * 
     * @desc	注册phpexcel服务
     * @date	2017年5月12日
     */
    private function SetPhpExcel()
    {
    	$di = $this->di	;
    	$di->set('phpexcel', function(){
            require_once WEBROOT.'/vendor/autoload.php';
            return new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    	});
    }

}