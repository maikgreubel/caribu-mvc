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

    /**
     * Combination check whether controller and action matches this view
     *
     * @param string $controller The name of controller
     * @param string $action The name of action
     *
     * @return boolean true if both matches the view, false otherwise
     */
    public function matchBoth($controller, $action);

    /**
     * Checks whether the given controller matches this view
     *
     * @param string $controller
     *            The name of controller to match the view for
     *
     * @return boolean true in case of view is responsible the controller, false otherwise
     */
    public function matchController($controller);

    /**
     * Checks whether the given action matches this view
     *
     * @param string $action
     *            The name of action to match the view for
     *
     * @return boolean true in case of view is responsible the action, false otherwise
     */
    public function matchAction($action);
}
