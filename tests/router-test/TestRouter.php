<?php
namespace Nkey\Caribu\Mvc\Tests;

use Nkey\Caribu\Mvc\Router\AbstractRouter;

class TestRouter extends AbstractRouter
{
	public function __construct()
	{
		$this->addRoute('routed', new RoutedController());
	}
}