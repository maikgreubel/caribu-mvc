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
    /**
     * (non-PHPdoc)
     *
     * @see \Nkey\Caribu\Mvc\View\AbstractView::getOrder()
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Nkey\Caribu\Mvc\View\AbstractView::render()
     */
    public function render(Response &$response, Request $request, $parameters = array())
    {
    }
}
