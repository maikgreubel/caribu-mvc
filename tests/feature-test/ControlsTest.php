<?php
namespace Nkey\Caribu\Mvc\Tests;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

require_once dirname(__FILE__) . '/ControlsController.php';

use Nkey\Caribu\Mvc\Application;
use Nkey\Caribu\Mvc\Controller\Request;
use \Nkey\Caribu\Mvc\Tests\ControlsController;

/**
 * Controls test case
 *
 * @author Maik Greubel <greubel@nkey.de>
 *        
 *         This file is part of Caribu MVC package
 */
class ControlsTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        Application::getInstance()->setUp()
            ->registerController('Nkey\Caribu\Mvc\Tests\ControlsController')
            ->registerViewControl('form', 'Nkey\Caribu\Mvc\View\Controls\Form')
            ->registerViewControl('image', 'Nkey\Caribu\Mvc\View\Controls\Form')
            ->setDefaults('Login');
    }

    public function testControls()
    {
        $request = Request::parse("/controls/index");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertContains('<form action="/controls/index"', $response->getBody());
    }

    public function testLoginRequest()
    {
        $request = Request::parse("/controls/login");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertContains('<title>Login</title>', $response->getBody());
    }

    public function testInvalidPlaceholder()
    {
        $request = Request::parse("/controls/emptyPlaceholder");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertNotContains('{form=nothing}', $response->getBody());
        $this->assertEquals('Empty placeholder', $response->getTitle());
    }

    public function testControlObject()
    {
        $request = Request::parse("/controls/controlsObject");
        
        $response = Application::getInstance()->serve('default', array(), $request, false);
        
        $this->assertEquals('Image control', $response->getTitle());
        $this->assertContains('<img src="http://screenshots.de.sftcdn.net/de/scrn/65000/65652/free-abstractions-screensaver-6.jpg" alt="Free image for test"/>', $response->getBody());
    }
}
