<?php
namespace Nkey\Caribu\Mvc\Router;

use Nkey\Caribu\Mvc\Controller\AbstractController;
use Nkey\Caribu\Mvc\Controller\Request;
use Nkey\Caribu\Mvc\Application;

abstract class AbstractRouter {
	
	/**
	 * Application instance
	 * 
	 * @var Application
	 */
	private $application;
	
	public function setApplication(Application $application)
	{
		$this->application = $application;
		foreach($this->routes as $routeName => $controller) {
			$this->application->registerController($controller, $routeName);
		}
	}
	
	/**
	 * @var array
	 */
	private $routes;
	
	public function addRoute(string $name, AbstractController $controller)
	{
		$this->routes[$name] = $controller;
	}
	
	/**
	 * Checks wether a route exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function hasRoute(string $name)
	{
		return key_exists($name, $this->routes);
	}
	
	/**
	 * 
	 * @param string $name
	 * @throws RouterException
	 * @return AbstractController
	 */
	private function getRoute(string $name)
	{
		if(!$this->hasRoute($name)) {
			throw new RouterException("Router {$router} is not registered");
		}
		
		return $this->routes[$name];
	}
	
	/**
	 * Route the existing request into a new controller
	 * 
	 * @param string $name The name of route
	 * @param Request $request The existing request instance
	 * @return \Nkey\Caribu\Mvc\Controller\AbstractController
	 */
	public function route(string $name, Request $request)
	{
		$parts = \explode('/', $request->getOrigin());
		$found = false;
		for($i = 0; $i < count($parts); $i++) {
			if($parts[$i] === $name && isset($parts[$i+1])) {
				$action = $parts[$i+1];
				if(strpos($action, "?")) {
					$action = strstr($action, "?");
				}
				
				$request->setAction($action);
				$found = true;
			}
		}
		if(!$found) {
			$request->setAction("index");
		}
		$controller = $this->getRoute($name);
		$request->setController($controller->getControllerSimpleName());
		
		return $controller;
	}
}