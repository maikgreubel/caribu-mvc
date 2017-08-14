<?php
namespace Nkey\Caribu\Mvc\Tests;

use Nkey\Caribu\Mvc\Controller\AbstractController;
use Nkey\Caribu\Mvc\Controller\Request;

class RoutingTestController extends AbstractController
{
	/**
	 * @webMethod
	 */
	public function index(Request $request)
	{
		echo "App rulez!";
	}
}