
[![Build Status](https://travis-ci.org/maikgreubel/caribu-mvc.png)](https://travis-ci.org/maikgreubel/caribu-mvc)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/maikgreubel/caribu-mvc/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/maikgreubel/caribu-mvc/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/maikgreubel/caribu-mvc/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/maikgreubel/caribu-mvc/?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/56392b3ac30cb5000b000554/badge.svg?style=flat)](https://www.versioneye.com/user/projects/56392b3ac30cb5000b000554)

# caribu-mvc

Tiny annotation based MVC framework

For now only a simple example, what you can do with Caribu MVC.

composer.json:
```json
{
  "require" : {
    "nkey/caribu-mvc" : "dev-master",
    "nkey/phpgenerics" : "dev-master",
    "psr/log" : "1.0.0"
  }
}
```

public/index.php:
```php
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
class IndexController extends AbstractController
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
Application::getInstance()->registerController('IndexController')
    ->setLogger(new ExtendedLogger());

// Serving
Application::getInstance()->serve();
```


Then in your browser window open http://host/ and see the output of index() web method.
The address http://host/index, as well as http://host/index/index provides the same functionality.

Surf to http://host/index/paramTest/param1/Hello/param2/Caribu or to get the same output
the address http://host/index/paramTest?param1=Hello&param2=Caribu

Documentation follows soon in wiki.
