<?php
namespace Nkey\Caribu\Mvc\View;

use \Nkey\Caribu\Mvc\Controller\Response;
use \Nkey\Caribu\Mvc\Controller\Request;

/**
 * Interface describes a concrete view class
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
interface View
{

    /**
     * Must be implemented by concrete view type
     *
     * Renders the output
     *
     * @param Response $response
     *            The response object which will be modified by render process
     * @param Request $request
     *            The request to use for rendering
     * @param array $parameters
     *            Parameters to use in rendering process
     *
     * @return void
     */
    public function render(Response &$response, Request $request, array $parameters = array());

    /**
     * Retrieve the order of view in best-match list
     *
     * @return int
     */
    public function getOrder();
}
