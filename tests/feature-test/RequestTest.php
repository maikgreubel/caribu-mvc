<?php
namespace Nkey\Caribu\Mvc\Tests;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

use Nkey\Caribu\Mvc\Controller\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    private $serverVars;

    protected function setUp()
    {
        $this->serverVars = array();
        $this->serverVars['REQUEST_URI'] = '/caribu-mvc/tests/';
        $this->serverVars['SCRIPT_FILENAME'] = 'D:/web/caribu-mvc/tests/index.php';
        $this->serverVars['SCRIPT_NAME'] = '/caribu-mvc/tests/index.php';
        $this->serverVars['HTTP_HOST'] = 'localhost';
    }

    public function testRequestSimple()
    {
        $request = Request::parseFromServerRequest($this->serverVars, 'Simple', 'index');
        
        $this->assertTrue(is_null($request->getRemoteHost()));
        $this->assertEquals('Simple', $request->getController());
        $this->assertEquals('index', $request->getAction());
        $this->assertEquals('/caribu-mvc/tests/', $request->getContextPrefix());
    }

    public function testRequestAdvanced()
    {
        $this->serverVars['REMOTE_ADDR'] = '::1';
        $request = Request::parseFromServerRequest($this->serverVars, 'Simple', 'index');
        
        $this->assertFalse(is_null($request->getRemoteHost()));
        $this->assertEquals('::1', $request->getRemoteHost());
    }

    /**
     * @expectedException \Nkey\Caribu\Mvc\Controller\ControllerException
     */
    public function testParameterOverrideNonExisting()
    {
        $request = Request::parseFromServerRequest($this->serverVars, 'Simple', 'index');
        $request->setParam('Accept-Language', 'de-DE');
    }

    public function testParameterOverride()
    {
        $this->serverVars['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4';
        $request = Request::parseFromServerRequest($this->serverVars, 'Simple', 'index');
        $request->setParam('Accept-Language', 'tr-TR');
        
        $this->assertEquals('tr-TR', $request->getParam('Accept-Language'));
        $this->assertEquals('de-DE', $request->getParam('Accept-Language-Best'));
    }
}
