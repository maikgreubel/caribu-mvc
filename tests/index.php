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

    public function formTest(Request $request)
    {
        if($request->getParam("loggedin", 'boolean')) {
            printf('<a href="%ssimple/logout">logout</a>', $request->getContextPrefix());
        } else {
            $this->viewParams['form']['login'] = array(
                "controller" => "simple",
                "action" => "login",
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
    }

    public function login(Request $request)
    {
        if($request->getParam("username") == "test" && $request->getParam("password") == "tset") {
            $_SESSION['loggedin'] = true;
        }
        $this->response->addHeader('Location', sprintf('%ssimple/formTest', $request->getContextPrefix()));
    }

    public function logout(Request $request)
    {
        unset($_SESSION["loggedin"]);
        $this->response->addHeader('Location', sprintf('%ssimple/formTest', $request->getContextPrefix()));
    }
}

// Preparing
Application::getInstance()->registerController('SimpleController')
    ->setDefaults('Simple')
    ->registerViewControl('form', '\Nkey\Caribu\Mvc\View\Controls\Form')
    ->enableSession()
    ->setLogger(new ExtendedLogger());

// Serving
Application::getInstance()->serve();
