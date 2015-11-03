<?php
namespace Nkey\Caribu\Mvc\Tests;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

require_once dirname(__FILE__) . '/FeatureTestController.php';
require_once dirname(__FILE__) . '/InvalidView.php';
require_once dirname(__FILE__) . '/DoNothingView.php';

use \Nkey\Caribu\Mvc\Controller\Request;
use \Nkey\Caribu\Mvc\Application;
use \Nkey\Caribu\Mvc\Tests\FeatureTestController;
use \Nkey\Caribu\Mvc\Tests\InvalidView;
use \Nkey\Caribu\Mvc\Tests\DoNothingView;

/**
 * View test case
 *
 * @author Maik Greubel <greubel@nkey.de>
 *        
 *         This file is part of Caribu MVC package
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        Application::getInstance()->setUp()
            ->registerController('Nkey\Caribu\Mvc\Tests\FeatureTestController')
            ->setDefaults('FeatureTest');
    }

    /**
     * @expectedException \Nkey\Caribu\Mvc\View\ViewException
     * @expectedExceptionMessage No such view class Nkey\Caribu\Mvc\View\NoView found
     */
    public function testNonExistingView()
    {
        Application::getInstance()->registerView('Nkey\Caribu\Mvc\View\NoView');
    }

    /**
     * @expectedException \Nkey\Caribu\Mvc\View\ViewException
     * @expectedExceptionMessage View Nkey\Caribu\Mvc\Tests\InvalidView is not in application scope
     */
    public function testInvalidView()
    {
        Application::getInstance()->registerView('Nkey\Caribu\Mvc\Tests\InvalidView');
    }

    public function testRegisterDoNothingView()
    {
        Application::getInstance()->registerView('Nkey\Caribu\Mvc\Tests\DoNothingView', 10);
        
        $request = Request::parse("/featureTest/index");
        
        $response = Application::getInstance()->serve('default', $request, false);
        
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
        $this->assertEquals('FeatureTest', $response->getTitle());
        $this->assertEquals('HTTP/1.1 200 OK', $response->getHttpCode());
        $this->assertContains('Test succeeded', $response->getBody());
        $this->assertNotContains('<!DOCTYPE', $response->getBody());
    }

    /**
     * @expectedException \Nkey\Caribu\Mvc\View\ViewException
     * @expectedExceptionMessage No view found for request
     */
    public function testUnregister()
    {
        Application::getInstance()->unregisterView('Default', 0);
        
        $request = Request::parse("/featureTest/index");
        
        Application::getInstance()->serve('default', $request, false);
    }

    static function main()
    {
        $suite = new \PHPUnit_Framework_TestSuite(__CLASS__);
        \PHPUnit_TextUI_TestRunner::run($suite);
    }
}

if (! defined('PHPUnit_MAIN_METHOD')) {
    ViewTest::main();
}
