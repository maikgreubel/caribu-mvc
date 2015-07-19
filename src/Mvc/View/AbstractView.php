<?php
namespace Nkey\Caribu\Mvc\View;

use \Nkey\Caribu\Mvc\Controller\Response;
use \Nkey\Caribu\Mvc\Controller\Request;
use \Nkey\Caribu\Mvc\View\View;

/**
 * Abstract view class
 *
 * This class provides some basic functions which are needed by every
 * concrete view class. It initializes the view by reading the
 * annotations from elements and provide them as settings.
 *
 * It also has the ability to check whether the view is responsible
 * for a particular request.
 *
 * All basic functions are final and cannot be overriden.
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
abstract class AbstractView implements View
{

    /**
     * The name of view
     *
     * @var string
     */
    private $viewName;

    /**
     * List of allowed controllers
     *
     * @var array
     */
    private $controllers = array();

    /**
     * List of allowed actions
     *
     * @var array
     */
    private $actions = array();

    /**
     * List of view controls
     *
     * @var array
     */
    private $controls = array();

    /**
     * List of additional css files
     * @var array
     */
    private $cssFiles = array();

    /**
     * List of additional javascript files
     * @var array
     */
    private $jsFiles = array();

    /**
     * (non-PHPdoc)
     *
     * @see \Nkey\Caribu\Mvc\View\View::getOrder()
     */
    abstract public function getOrder();

    /**
     * (non-PHPdoc)
     *
     * @see \Nkey\Caribu\Mvc\View\View::render()
     */
    abstract public function render(Response &$response, Request $request, $parameters = array());

    /**
     * Retrieve the settings from view
     *
     * @return \Nkey\Caribu\Mvc\View\View
     */
    final public function getViewSettings()
    {
        $rf = new \ReflectionClass($this);

        $this->viewName = str_replace('View', '', $rf->getShortName());

        if (($anno = $rf->getDocComment())) {
            $matches = array();
            if (preg_match("#@applyTo\((.*)\)#", $anno, $matches)) {
                $params = array();
                parse_str(str_replace(',', '&', $matches[1]), $params);

                foreach ($params as $param => $value) {
                    if ($param == 'controller') {
                        $this->controllers = explode('|', $value);
                    }

                    if ($param == 'action') {
                        $this->actions = explode('|', $value);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Nkey\Caribu\Mvc\View\View::matchController()
     */
    final public function matchController($controller)
    {
        return (
            in_array($controller, $this->controllers) ||
            in_array('any', $this->controllers) ||
            count($this->controllers) == 0
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Nkey\Caribu\Mvc\View\View::matchAction()
     */
    final public function matchAction($action)
    {
        return (in_array($action, $this->actions) || in_array('any', $this->actions) || count($this->actions) == 0);
    }

    /**
     * (non-PHPdoc)
     * @see \Nkey\Caribu\Mvc\View\View::matchBoth()
     */
    final public function matchBoth($controller, $action)
    {
        return $this->matchController($controller) && $this->matchAction($action);
    }

    /**
     * Get name of view
     *
     * @return string The name of view
     */
    final public function getViewSimpleName()
    {
        return $this->viewName;
    }

    /**
     * (non-PHPdoc)
     * @see \Nkey\Caribu\Mvc\View\View::registerControl()
     */
    final public function registerControl($controlClass, $controlIdentifier)
    {
        $this->controls[$controlIdentifier] = $controlClass;
    }

    /**
     * (non-PHPdoc)
     * @see \Nkey\Caribu\Mvc\View\View::createControl()
     */
    final public function createControl($controlIdentifier)
    {
        $rf = new \ReflectionClass($this->controls[$controlIdentifier]);
        return $rf->newInstance();
    }

    /**
     * (non-PHPdoc)
     * @see \Nkey\Caribu\Mvc\View\View::hasControl()
     */
    final public function hasControl($controlIdentifier)
    {
       return isset($this->controls[$controlIdentifier]);
    }

    /**
     * (non-PHPdoc)
     * @see \Nkey\Caribu\Mvc\View\View::setCssFiles()
     */
    final public function setCssFiles(array $files)
    {
        $this->cssFiles = $files;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Nkey\Caribu\Mvc\View\View::setJsFiles()
     */
    final public function setJsFiles(array $files)
    {
        $this->jsFiles = $files;
        return $this;
    }

    /**
     * Retrieve all js files
     *
     * @return array
     */
    final protected function getJsFiles()
    {
        return $this->jsFiles;
    }

    /**
     * Retrieve all css files
     *
     * @return array
     */
    final protected function getCssFiles()
    {
        return $this->cssFiles;
    }
}
