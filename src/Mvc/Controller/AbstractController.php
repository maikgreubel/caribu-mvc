<?php
namespace Nkey\Caribu\Mvc\Controller;

use \Nkey\Caribu\Mvc\View\View;

/**
 * Basic controller functionality
 *
 * This class provides the basic functionality each controller
 * needs to work correctly.
 *
 * The controller call will be performed by Application::serve().
 *
 * All basic functions are final and cannot be overriden.
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
abstract class AbstractController
{

    /**
     * Namespace of controller class
     *
     * @var string
     */
    private $nameSpace = '';

    /**
     * Class of controller
     *
     * @var string
     */
    private $controllerClass = '';

    /**
     * Name of controller
     *
     * @var string
     */
    private $controllerName = '';

    /**
     * List of actions provided by controller
     *
     * @var array
     */
    private $actions = null;

    /**
     * Request
     *
     * @var \Nkey\Caribu\Mvc\Controller\Request
     */
    private $request;

    /**
     * Response
     *
     * @var \Nkey\Caribu\Mvc\Controller\Response
     */
    protected $response;

    /**
     * View parameters
     *
     * @var array
     */
    protected $viewParams = array();

    /**
     * Parse the parameters of action
     *
     * @param \ReflectionMethod $action
     *
     * @return boolean true if parameters meets conditions for a valid action method, false otherwise
     */
    private function parseParameters(\ReflectionMethod $action)
    {
        $params = $action->getParameters();
        if (count($params) < 1) {
            return false;
        }

        $param = $params[0];
        assert($param instanceof \ReflectionParameter);
        if (! ($class = $param->getClass()) || $class->getName() != 'Nkey\Caribu\Mvc\Controller\Request') {
            return false;
        }

        return true;
    }

    /**
     * Parse the settings out of annotations
     *
     * @param \ReflectionMethod $action
     */
    private function parseAnnotations(\ReflectionMethod $action)
    {
        if ($action->isConstructor() || $action->isDestructor() || $action->isStatic() || $action->isFinal()) {
            return;
        }

        $rfMethod = new \ReflectionMethod($this, $action->getName());
        $anno = $rfMethod->getDocComment();

        if ($anno && preg_match('#@webMethod#', $anno)) {
            $this->actions[] = $action->getName();
            return;
        }

        if (!$this->parseParameters($action)) {
            return;
        }

        $this->actions[] = $action->getName();
    }

    /**
     * Get the controller prepared for service
     *
     * @return \Nkey\Caribu\Mvc\Controller\AbstractController The controller instance
     */
    final public function getControllerSettings()
    {
        $rf = new \ReflectionClass($this);

        $this->response = new Response();
        $this->controllerClass = $rf->getShortName();
        $this->controllerName = ucfirst(str_replace('Controller', '', $this->controllerClass));
        $this->response->setTitle($this->controllerName);

        $actions = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($actions as $action) {
            $this->parseAnnotations($action);
        }

        return $this;
    }

    /**
     * Checks whether controller has specific action
     *
     * @param string $action Name of action to search for
     *
     * @return boolean true if the action exists in controller, false otherwise
     */
    final public function hasAction($action)
    {
        if (count($this->actions) === 0) {
            return false;
        }
        return array_search($action, $this->actions) !== false;
    }

    /**
     * Call the action
     *
     * @param string $action The name of action to call in controller
     *
     * @return \Nkey\Caribu\Mvc\Controller\Response The response
     */
    final public function call($action, Request $request, View $view)
    {
        $this->request = $request;

        ob_start();

        $rf = new \ReflectionMethod($this, $action);

        $anno = $rf->getDocComment();
        $matches = array();
        if (preg_match('#@responseType ([\w\/]+)#', $anno, $matches)) {
            $this->response->setType($matches[1]);
        }

        if (preg_match('#@title ([^\\n]+)#', $anno, $matches)) {
            $this->response->setTitle($matches[1]);
        }

        $rf->invoke($this, $this->request);

        $this->response->appendBody(ob_get_clean());

        $view->render($this->response, $request, $this->viewParams);
        $this->addControls($this->response, $request, $view);

        return $this->response;
    }

    /**
     * Retrieve the simple name of controller
     *
     * @return string Name of controller
     */
    final public function getControllerSimpleName()
    {
        return $this->controllerName;
    }

    /**
     * Retrieve the namespace of controller
     *
     * @return string The controller namespace
     */
    final public function getControllerNamespace()
    {
        return $this->nameSpace;
    }

    /**
     * Add the controls injected into view parameters
     *
     * @param Response $response The response rendered with controls
     * @param Request $request The request
     * @param View $view The View instance to use for rendering
     */
    protected function addControls(Response &$response, Request $request, View $view)
    {
        $matches = array();

        while(preg_match("/\{(\w+)=(\w+)\}/", $response->getBody(), $matches)) {
            $controlIdentifier = $matches[1];
            $controlName = $matches[2];
            $currentBody = $response->getBody();

            if(!isset($this->viewParams[$controlIdentifier][$controlName]) ||
                !$view->hasControl($controlIdentifier)) {
                $response->setBody(str_replace($matches[0], '', $currentBody));
                continue;
            }

            $control = $view->createControl($controlIdentifier);
            $response->setBody(str_replace(
                $matches[0],
                $control->render($request, $this->viewParams[$controlIdentifier][$controlName]),
                $currentBody
            ));
        }
    }
}
