<?php
namespace Nkey\Caribu\Mvc\Tests;

use Nkey\Caribu\Mvc\View\AbstractView;

use Nkey\Caribu\Mvc\Controller\Request;
use Nkey\Caribu\Mvc\Controller\Response;

/**
 * A do nothing view
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
class DoNothingView extends AbstractView
{
    public function getOrder()
    {
        return 10;
    }

    public function render(Response &$response, Request $request, array $parameters = array())
    {
    }
}
