<?php
namespace Nkey\Caribu\Mvc\Tests;

use Nkey\Caribu\Mvc\Controller\AbstractController;
use Nkey\Caribu\Mvc\Controller\Request;

/**
 * A test controller
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
class FeatureTestController extends AbstractController
{
    /**
     * @webMethod
     */
    public function index()
    {
        echo $this->nonWebMethod();
    }

    /**
     * @responseType text/plain
     *
     * @param Request $request
     */
    public function params(Request $request)
    {
       foreach($request->getParams() as $param => $value) {
           printf("%s = %s\n", $param, $value);
       }
    }

    public function nonWebMethod()
    {
        return $this->anotherNonWeb("Test succeeded");
    }

    public function anotherNonWeb($withString)
    {
        return $withString;
    }
}
