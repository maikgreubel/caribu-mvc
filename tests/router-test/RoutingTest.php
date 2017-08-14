<?php
namespace Nkey\Caribu\Mvc\Tests;

use Nkey\Caribu\Mvc\Application;
use Nkey\Caribu\Mvc\Controller\Request;

class RoutingTest extends \PHPUnit\Framework\TestCase
{
	protected function setUp()
	{
		Application::getInstance()->setUp()
			->registerController('Nkey\Caribu\Mvc\Tests\RoutingTestController')
			->registerRouter(new TestRouter())
			->setDefaults('RoutingTest');
	}
	
	public function testNoRouting()
	{
		$request = Request::parse("/routingTest");
		
		$response = Application::getInstance()->serve('default', array(), $request, false);
		
		$this->assertEquals(0, count($request->getParams()));
		$this->assertEquals('/routingTest', $request->getOrigin());
		$this->assertContains('App rulez!', $response->getBody());
	}
	
	public function testRouting()
	{
		$request = Request::parse("/routingTest/routed");
		
		$response = Application::getInstance()->serve('default', array(), $request, false);
		
		$this->assertEquals(0, count($request->getParams()));
		$this->assertEquals('/routingTest/routed', $request->getOrigin());
		$this->assertContains("flex'd", $response->getBody());
		
	}
}