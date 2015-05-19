<?php
require dirname(__FILE__) . '/../vendor/autoload.php';

use \Generics\Logger\ExtendedLogger;
use \Nkey\Caribu\Mvc\Application;

// Preparing
Application::getInstance()->registerController('\Nkey\Caribu\Mvc\Tests\SimpleController')
    ->setDefaults('Simple')
    ->registerViewControl('form', '\Nkey\Caribu\Mvc\View\Controls\Form')
    ->enableSession()
    ->setLogger(new ExtendedLogger());

// Serving
Application::getInstance()->serve();
