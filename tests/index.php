<?php
require dirname(__FILE__) . '/../vendor/autoload.php';

use \Nkey\Caribu\Mvc\Controller\AbstractController;
use \Nkey\Caribu\Mvc\Controller\Request;
use \Nkey\Caribu\Mvc\Application;
use \Nkey\Caribu\Mvc\View\AbstractView;

use \Generics\Logger\ExtendedLogger;

/**
 * A simple test controller
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
class SimpleController extends AbstractController
{

    /**
     * @webMethod
     *
     * @title Hey there page
     */
    public function index()
    {
        echo "Hey, there!\n\n";
    }

    /**
     * @responseType text/plain
     *
     * @param \Nkey\Caribu\Mvc\Controller\Request $request
     */
    public function paramTest(Request $request)
    {
        foreach ($request->getParams() as $param => $value) {
            printf("%s => %s\n", $param, $value);
        }
    }
}

// Preparing
Application::getInstance()->registerController('SimpleController')
    ->setDefaults('Simple')
    ->setLogger(new ExtendedLogger());

// Serving
Application::getInstance()->serve();
