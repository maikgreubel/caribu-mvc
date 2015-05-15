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
     * Get the controller prepared for service
     *
     * @return \Nkey\Caribu\Mvc\Controller\AbstractController The controller instance
     */
    public final function getControllerSettings()
    {
        $rf = new \ReflectionClass($this);

        $this->response = new Response();
        $this->controllerClass = $rf->getShortName();
        $this->controllerName = ucfirst(str_replace('Controller', '', $this->controllerClass));
        $this->response->setTitle($this->controllerName);

        $actions = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($actions as $action) {
            assert($action instanceof \ReflectionMethod);
            if (! $action->isConstructor() && ! $action->isDestructor() && ! $action->isStatic() && ! $action->isFinal()) {

                $rfMethod = new \ReflectionMethod($this, $action->getName());
                $anno = $rfMethod->getDocComment();
                if ($anno) {
                    if (preg_match('#@webMethod#', $anno)) {
                        $this->actions[] = $action->getName();
                        continue;
                    }
                }

                $params = $action->getParameters();
                if (count($params) < 1) {
                    continue;
                }
                $param = $params[0];
                assert($param instanceof \ReflectionParameter);
                if (! ($class = $param->getClass()) || $class->getName() != 'Nkey\Caribu\Mvc\Controller\Request') {
                    continue;
                }
                $this->actions[] = $action->getName();
            }
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
    public final function hasAction($action)
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
    public final function call($action, Request $request, View $view)
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

        return $this->response;
    }

    /**
     * Retrieve the simple name of controller
     *
     * @return string Name of controller
     */
    public final function getControllerSimpleName()
    {
        return $this->controllerName;
    }
}
