<?php
namespace Nkey\Caribu\Mvc\Tests;

use Nkey\Caribu\Mvc\Controller\Request;
use Nkey\Caribu\Mvc\Controller\Response;

/**
 * An invalid test view
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
class InvalidView
{
    public function render(Response &$response, Request $request, $parameters = array())
    {
    }
}
