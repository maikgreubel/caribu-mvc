<?php
namespace Nkey\Caribu\Mvc\Tests;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

require_once dirname(__FILE__) . '/FeatureTestController.php';
require_once dirname(__FILE__) . '/InvalidController.php';

use \Nkey\Caribu\Mvc\Controller\Request;
use \Nkey\Caribu\Mvc\Application;

/**
 * Feature test case
 *
 * @author Maik Greubel <greubel@nkey.de>
 *        
 *         This file is part of Caribu MVC package
 */
class FeatureTest extends \PHPUnit\Framework\TestCase
{

    protected function setUp()
    {
        Application::getInstance()->setUp()
            ->registerController('Nkey\Caribu\Mvc\Tests\FeatureTestController')
            ->setDefaults('FeatureTest');
    }

    public function testFeature()
    {
        $request = Request::parse("/featureTest/index");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertEquals(0, count($request->getParams()));
        $this->assertEquals('/featureTest/index', $request->getOrigin());
        
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
        $this->assertEquals('FeatureTest', $response->getTitle());
        $this->assertEquals('HTTP/1.1 200 OK', $response->getHttpCode());
        $this->assertContains('Test succeeded', $response->getBody());
    }

    public function testNoAction()
    {
        $request = Request::parse("/featureTest/noAction");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertEquals(404, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
        $this->assertContains('<h2>Not Found</h2>', $response->getBody());
        $this->assertEquals('Error', $response->getTitle());
        $this->assertEquals('HTTP/1.1 404 Not Found', $response->getHttpCode());
    }

    public function testNonWebMethod()
    {
        $request = Request::parse("/featureTest/nonWebMethod");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertEquals(404, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
        $this->assertContains('<h2>Not Found</h2>', $response->getBody());
        $this->assertEquals('Error', $response->getTitle());
        $this->assertEquals('HTTP/1.1 404 Not Found', $response->getHttpCode());
    }

    public function testEx()
    {
        $serverVars['REMOTE_ADDR'] = '::1';
        $serverVars['SERVER_ADDR'] = '::1';
        $serverVars['DOCUMENT_ROOT'] = '/var/www/html';
        $serverVars['REDIRECT_BASE'] = '/caribu-mvc/tests/';
        $serverVars['REDIRECT_URL'] = '/caribu-mvc/tests/featureTest/';
        $serverVars['SCRIPT_NAME'] = '/var/www/html/caribu-mvc/tests/index.php';
        $serverVars['SCRIPT_NAME'] = '/caribu-mvc/tests/index.php';
        
        $request = Request::parse("/featureTest/exception", $serverVars);
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        $this->assertEquals(500, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
        $this->assertContains('<h2>Internal Server Error</h2>', $response->getBody());
        $this->assertEquals('Error', $response->getTitle());
        $this->assertEquals('HTTP/1.1 500 Internal Server Error', $response->getHttpCode());
    }

    public function testDefaults()
    {
        $request = Request::parse("/");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertEquals('Index', $request->getController());
        $this->assertEquals('index', $request->getAction());
        
        $this->assertEquals(404, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    public function testNoController()
    {
        $request = Request::parse("/zest/index");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertEquals(404, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    public function testFeatureParams()
    {
        $request = Request::parse("/featureTest/params/id/24/perform/save");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/plain', $response->getType());
        $this->assertEquals("id = 24\nperform = save\n", $response->getBody());
    }

    public function testFeatureParamsQueryString()
    {
        $request = Request::parse("/featureTest/params/?id=24&perform=save");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/plain', $response->getType());
        $this->assertEquals("id = 24\nperform = save\n", $response->getBody());
    }

    public function testRemoteAddress()
    {
        $serverVars['REMOTE_ADDR'] = '127.0.0.1';
        
        $request = Request::parse("/featureTest/index", $serverVars);
        
        $this->assertEquals('127.0.0.1', $request->getRemoteHost());
    }

    public function testProxy()
    {
        $serverVars['REMOTE_ADDR'] = '127.0.0.2';
        $serverVars['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
        
        $request = Request::parse("/featureTest/index", $serverVars);
        
        $this->assertEquals('127.0.0.1', $request->getRemoteHost());
    }

    public function testSimulateHttpServer()
    {
        $serverVars['REMOTE_ADDR'] = '::1';
        $serverVars['SERVER_ADDR'] = '::1';
        $serverVars['DOCUMENT_ROOT'] = '/var/www/html';
        $serverVars['REDIRECT_BASE'] = '/caribu-mvc/tests/';
        $serverVars['REDIRECT_URL'] = '/caribu-mvc/tests/featureTest/';
        $serverVars['SCRIPT_NAME'] = '/var/www/html/caribu-mvc/tests/index.php';
        $serverVars['SCRIPT_NAME'] = '/caribu-mvc/tests/index.php';
        
        $request = Request::parse("/featureTest/index", $serverVars);
        
        $response = Application::getInstance()->serve('default', $serverVars, $request, false);
        
        $this->assertEquals('FeatureTest', $request->getController());
        $this->assertEquals('index', $request->getAction());
        $this->assertEquals('/caribu-mvc/tests/', $request->getContextPrefix());
        $this->assertEquals('::1', $request->getRemoteHost());
        
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    public function testSimulateHttpServerImplicitRequest()
    {
        $serverVars['REMOTE_ADDR'] = '::1';
        $serverVars['SERVER_ADDR'] = '::1';
        $serverVars['DOCUMENT_ROOT'] = '/var/www/html';
        $serverVars['REDIRECT_BASE'] = '/caribu-mvc/tests/';
        $serverVars['REDIRECT_URL'] = '/caribu-mvc/tests/featureTest/';
        $serverVars['SCRIPT_NAME'] = '/var/www/html/caribu-mvc/tests/index.php';
        $serverVars['SCRIPT_NAME'] = '/caribu-mvc/tests/index.php';
        $serverVars['REQUEST_URI'] = '/featureTest/index';
        
        $response = Application::getInstance()->serve('default', $serverVars, null, false);
        
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    public function testSimulateHttpServerContextPrefix()
    {
        $serverVars['REMOTE_ADDR'] = '::1';
        $serverVars['HTTP_HOST'] = '::1';
        $serverVars['DOCUMENT_ROOT'] = '/var/www/html';
        $serverVars['REDIRECT_BASE'] = '/caribu-mvc-test/';
        $serverVars['CONTEXT_PREFIX'] = '/caribu-mvc-test';
        $serverVars['REDIRECT_URL'] = '/caribu-mvc-test/featureTest/';
        $serverVars['SCRIPT_NAME'] = '/var/www/html/caribu-mvc/tests/index.php';
        $serverVars['SCRIPT_NAME'] = '/caribu-mvc/tests/index.php';
        
        $request = Request::parse("/featureTest/index", $serverVars);
        
        $response = Application::getInstance()->serve('default', $serverVars, $request, false);
        
        $this->assertEquals('FeatureTest', $request->getController());
        $this->assertEquals('index', $request->getAction());
        $this->assertEquals('/caribu-mvc-test/', $request->getContextPrefix());
        $this->assertEquals('::1', $request->getRemoteHost());
        
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    public function testRequestUri()
    {
        $serverVars['REMOTE_ADDR'] = '::1';
        $serverVars['HTTP_HOST'] = '::1';
        $serverVars['DOCUMENT_ROOT'] = '/var/www/html';
        $serverVars['REDIRECT_BASE'] = '/caribu-mvc-test/';
        $serverVars['REDIRECT_URL'] = '/caribu-mvc-test/featureTest/';
        $serverVars['SCRIPT_NAME'] = '/var/www/html/caribu-mvc/tests/index.php';
        $serverVars['SCRIPT_NAME'] = '/caribu-mvc/tests/index.php';
        $serverVars['REQUEST_URI'] = '/featureTest/index';
        
        $request = Request::parseFromServerRequest($serverVars);
        
        $response = Application::getInstance()->serve('default', $serverVars, $request, false);
        
        $this->assertEquals('FeatureTest', $request->getController());
        $this->assertEquals('index', $request->getAction());
        $this->assertEquals('/caribu-mvc-test/', $request->getContextPrefix());
        $this->assertEquals('::1', $request->getRemoteHost());
        
        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    /**
     * @expectedException \Generics\Socket\InvalidUrlException
     * @expectedExceptionMessage No such uri provided
     */
    public function testRequestUriMissing()
    {
        Request::parseFromServerRequest(array());
    }

    /**
     * @expectedException \Generics\GenericsException
     * @expectedExceptionMessage Cloning is prohibited
     */
    public function testClone()
    {
        clone Application::getInstance();
    }

    /**
     * @expectedException \Nkey\Caribu\Mvc\Controller\ControllerException
     * @expectedExceptionMessage No such controller class NoController found
     */
    public function testRegisterNonExistingController()
    {
        Application::getInstance()->registerController('NoController');
    }

    /**
     * @expectedException \Nkey\Caribu\Mvc\Controller\ControllerException
     * @expectedExceptionMessage Controller \Nkey\Caribu\Mvc\Tests\InvalidController is not in application scope
     */
    public function testInvalidController()
    {
        Application::getInstance()->registerController('\Nkey\Caribu\Mvc\Tests\InvalidController');
    }
}
