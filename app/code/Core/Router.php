<?php
/**
 * Router
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\Core;
class  Router extends \Phalcon\Mvc\Router
{
	
	/**
	 * 初始化框架url规则
	 */
	public  function  initUrl($basePath)
	{
		$basePath = rtrim($basePath,'/');
		$this->add(
				$basePath.'/:controller/:action/:params',
				[
						'controller' => 1,
						'action' => 2,
						'params' => 3
				]
		);
		$this->add(
				$basePath.'/:controller/:action/',
				[
						'controller' => 1,
						'action' => 2,
				]
		);
		$this->add(
				$basePath.'/:controller',
				[
						'controller' => 1,
						'action' => 'index',
				]
		);
		$this->add(
				$basePath.'/:controller/',
				[
						'controller' => 1,
						'action' => 'index',
				]
		);
		$this->add(
				$basePath.'/',
				[
						'controller' => 'index',
						'action' => 'index',
				]
		);
		$this->add(
				$basePath,
				[
						'controller' => 'index',
						'action' => 'index',
				]
		);
		return $this;
	}
	
}