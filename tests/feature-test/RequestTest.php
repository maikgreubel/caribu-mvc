<?php
namespace Nkey\Caribu\Mvc\Tests;

use Nkey\Caribu\Mvc\Controller\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $_SERVER['REQUEST_URI'] = '/caribu-mvc/tests/';
        $_SERVER['SCRIPT_FILENAME'] = 'D:/web/caribu-mvc/tests/index.php';
        $_SERVER['SCRIPT_NAME'] = '/caribu-mvc/tests/index.php';
        $_SERVER['HTTP_HOST'] = 'localhost';
    }

    public function testRequestSimple()
    {
        // $_SERVER['REDIRECT_BASE'] = '';
        // $_SERVER['CONTEXT_PREFIX'] = '';

        $request = Request::parseFromServerRequest('Simple', 'index');

        $this->assertTrue(is_null($request->getRemoteHost()));
        $this->assertEquals('Simple', $request->getController());
        $this->assertEquals('index', $request->getAction());
        $this->assertEquals('/caribu-mvc/tests/', $request->getContextPrefix());
    }

    public function testRequestAdvanced()
    {
        $_SERVER['REMOTE_ADDR'] = '::1';
        $request = Request::parseFromServerRequest('Simple', 'index');

        $this->assertFalse(is_null($request->getRemoteHost()));
        $this->assertEquals('::1', $request->getRemoteHost());
    }

    /**
     * @expectedException \Nkey\Caribu\Mvc\Controller\ControllerException
     */
    public function testParameterOverrideNonExisting()
    {
        $request = Request::parseFromServerRequest('Simple', 'index');
        $request->setParam('Accept-Language', 'de-DE');
    }

    public function testParameterOverride()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4';
        $request = Request::parseFromServerRequest('Simple', 'index');
        $request->setParam('Accept-Language', 'tr-TR');

        $this->assertEquals('tr-TR', $request->getParam('Accept-Language'));
        $this->assertEquals('de-DE', $request->getParam('Accept-Language-Best'));
    }

}