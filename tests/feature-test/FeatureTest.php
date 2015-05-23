<?php
namespace Nkey\Caribu\Mvc\Tests;

require_once dirname(__FILE__).'/FeatureTestController.php';
require_once dirname(__FILE__).'/InvalidController.php';

use \Nkey\Caribu\Mvc\Controller\Request;
use \Nkey\Caribu\Mvc\Application;

use \Nkey\Caribu\Mvc\Tests\FeatureTestController;
use \Nkey\Caribu\Mvc\Tests\InvalidController;

/**
 * Feature test case
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
class FeatureTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Application::getInstance()
            ->setUp()
            ->registerController('Nkey\Caribu\Mvc\Tests\FeatureTestController')
            ->setDefaults('FeatureTest');
    }

    public function testFeature()
    {
        $request = Request::parse("/featureTest/index");

        $response = Application::getInstance()->serve('default', $request, false);

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

        $response = Application::getInstance()->serve('default', $request, false);

        $this->assertEquals(404, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
        $this->assertContains('<h2>HTTP 404</h2>', $response->getBody());
        $this->assertEquals('Error', $response->getTitle());
        $this->assertEquals('HTTP/1.1 404 Not Found', $response->getHttpCode());
    }

    public function testNonWebMethod()
    {
        $request = Request::parse("/featureTest/nonWebMethod");

        $response = Application::getInstance()->serve('default', $request, false);

        $this->assertEquals(404, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
        $this->assertContains('<h2>HTTP 404</h2>', $response->getBody());
        $this->assertEquals('Error', $response->getTitle());
        $this->assertEquals('HTTP/1.1 404 Not Found', $response->getHttpCode());
    }

    public function testDefaults()
    {
        $request = Request::parse("/");

        $response = Application::getInstance()->serve('default', $request, false);

        $this->assertEquals('Index', $request->getController());
        $this->assertEquals('index', $request->getAction());

        $this->assertEquals(404, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    public function testNoController()
    {
        $request = Request::parse("/zest/index");

        $response = Application::getInstance()->serve('default', $request, false);

        $this->assertEquals(404, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    public function testFeatureParams()
    {
        $request = Request::parse("/featureTest/params/id/24/perform/save");

        $response = Application::getInstance()->serve('default', $request, false);

        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/plain', $response->getType());
        $this->assertEquals("id = 24\nperform = save\n", $response->getBody());
    }

    public function testFeatureParamsQueryString()
    {
        $request = Request::parse("/featureTest/params/?id=24&perform=save");

        $response = Application::getInstance()->serve('default', $request, false);

        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/plain', $response->getType());
        $this->assertEquals("id = 24\nperform = save\n", $response->getBody());
    }

    public function testRemoteAddress()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = Request::parse("/featureTest/index");

        $this->assertEquals('127.0.0.1', $request->getRemoteHost());
    }

    public function testProxy()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';

        $request = Request::parse("/featureTest/index");

        $this->assertEquals('127.0.0.1', $request->getRemoteHost());
    }


    public function testSimulateHttpServer()
    {
        $_SERVER['REMOTE_ADDR']     = '::1';
        $_SERVER['SERVER_ADDR']     = '::1';
        $_SERVER['DOCUMENT_ROOT']   = '/var/www/html';
        $_SERVER['REDIRECT_BASE']   = '/caribu-mvc/tests/';
        $_SERVER['REDIRECT_URL']    = '/caribu-mvc/tests/featureTest/';
        $_SERVER['SCRIPT_NAME']     = '/var/www/html/caribu-mvc/tests/index.php';
        $_SERVER['SCRIPT_NAME']     = '/caribu-mvc/tests/index.php';

        $request = Request::parse("/featureTest/index");

        $response = Application::getInstance()->serve('default', $request, false);

        $this->assertEquals('FeatureTest', $request->getController());
        $this->assertEquals('index', $request->getAction());
        $this->assertEquals('/caribu-mvc/tests/', $request->getContextPrefix());
        $this->assertEquals('::1', $request->getRemoteHost());

        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    public function testSimulateHttpServerImplicitRequest()
    {
        $_SERVER['REMOTE_ADDR']     = '::1';
        $_SERVER['SERVER_ADDR']     = '::1';
        $_SERVER['DOCUMENT_ROOT']   = '/var/www/html';
        $_SERVER['REDIRECT_BASE']   = '/caribu-mvc/tests/';
        $_SERVER['REDIRECT_URL']    = '/caribu-mvc/tests/featureTest/';
        $_SERVER['SCRIPT_NAME']     = '/var/www/html/caribu-mvc/tests/index.php';
        $_SERVER['SCRIPT_NAME']     = '/caribu-mvc/tests/index.php';
        $_SERVER['REQUEST_URI']     = '/featureTest/index';

        $response = Application::getInstance()->serve('default', null, false);

        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    public function testSimulateHttpServerContextPrefix()
    {
        $_SERVER['REMOTE_ADDR']     = '::1';
        $_SERVER['HTTP_HOST']       = '::1';
        $_SERVER['DOCUMENT_ROOT']   = '/var/www/html';
        $_SERVER['REDIRECT_BASE']   = '/caribu-mvc-test/';
        $_SERVER['CONTEXT_PREFIX']  = '/caribu-mvc-test';
        $_SERVER['REDIRECT_URL']    = '/caribu-mvc-test/featureTest/';
        $_SERVER['SCRIPT_NAME']     = '/var/www/html/caribu-mvc/tests/index.php';
        $_SERVER['SCRIPT_NAME']     = '/caribu-mvc/tests/index.php';

        $request = Request::parse("/featureTest/index");

        $response = Application::getInstance()->serve('default', $request, false);

        $this->assertEquals('FeatureTest', $request->getController());
        $this->assertEquals('index', $request->getAction());
        $this->assertEquals('/caribu-mvc-test/', $request->getContextPrefix());
        $this->assertEquals('::1', $request->getRemoteHost());

        $this->assertEquals(200, $response->getCode());
        $this->assertEquals('text/html', $response->getType());
    }

    public function testRequestUri()
    {
        $_SERVER['REMOTE_ADDR']     = '::1';
        $_SERVER['HTTP_HOST']       = '::1';
        $_SERVER['DOCUMENT_ROOT']   = '/var/www/html';
        $_SERVER['REDIRECT_BASE']   = '/caribu-mvc-test/';
        $_SERVER['REDIRECT_URL']    = '/caribu-mvc-test/featureTest/';
        $_SERVER['SCRIPT_NAME']     = '/var/www/html/caribu-mvc/tests/index.php';
        $_SERVER['SCRIPT_NAME']     = '/caribu-mvc/tests/index.php';
        $_SERVER['REQUEST_URI']     = '/featureTest/index';

        $request = Request::parseFromServerRequest();

        $response = Application::getInstance()->serve('default', $request, false);

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
        Request::parseFromServerRequest();
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
