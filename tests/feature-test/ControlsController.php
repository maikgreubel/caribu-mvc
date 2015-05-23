<?php
namespace Nkey\Caribu\Mvc\Tests;

use Nkey\Caribu\Mvc\Controller\AbstractController;
use Nkey\Caribu\Mvc\Controller\Request;

use Nkey\Caribu\Mvc\View\Controls\Image;

/**
 * Another test controller
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
class ControlsController extends AbstractController
{
    /**
     * @webMethod
     */
    public function index()
    {
        $this->viewParams['form']['login'] = array(
            "fields" => array(
                array("name" => "username"),
                array("name" => "password", "type" => "password")
            ),
            "buttons" => array(
                array("name" => "Login")
            )
        );

        echo "{form=login}";
    }

    /**
     * @title Login
     * @webMethod
     */
    public function login()
    {
    }

    /**
     * @webMethod
     * @title Empty placeholder
     */
    public function emptyPlaceholder()
    {
        echo "{form=nothing}";
    }

    /**
     * @webMethod
     * @title Image control
     */
    public function controlsObject()
    {
        $this->viewParams['image']['abstract'] = new Image('http://screenshots.de.sftcdn.net/de/scrn/65000/65652/free-abstractions-screensaver-6.jpg', 'Free image for test');

        echo "{image=abstract}";
    }
}