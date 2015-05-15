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
     * Retrieve the settings from view
     *
     * @return \Nkey\Caribu\Mvc\View\View
     */
    public final function getViewSettings()
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
     * Checks whether the given controller matches this view
     *
     * @param string $controller
     *            The name of controller to match the view for
     *
     * @return boolean true in case of view is responsible the controller, false otherwise
     */
    public final function matchController($controller)
    {
        return (
            in_array($controller, $this->controllers) ||
            in_array('any', $this->controllers) ||
            count($this->controllers) == 0
        );
    }

    /**
     * Checks whether the given action matches this view
     *
     * @param string $action
     *            The name of action to match the view for
     *
     * @return boolean true in case of view is responsible the action, false otherwise
     */
    public final function matchAction($action)
    {
        return (in_array($action, $this->actions) || in_array('any', $this->actions) || count($this->actions) == 0);
    }

    /**
     * Get name of view
     *
     * @return string The name of view
     */
    public final function getViewSimpleName()
    {
        return $this->viewName;
    }
}
