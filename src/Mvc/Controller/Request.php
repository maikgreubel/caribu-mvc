<?php
namespace Nkey\Caribu\Mvc\Controller;

use \Generics\Socket\InvalidUrlException;

/**
 * The request is encapsulated in this class
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
class Request
{

    /**
     * Address of remote host
     *
     * @var string
     */
    private $remoteHost;

    /**
     * Original request uri
     *
     * @var string
     */
    private $origin = null;

    /**
     * Controller requested
     *
     * @var string
     */
    private $controller = null;

    /**
     * Action requested
     *
     * @var string
     */
    private $action = null;

    /**
     * Parameters of the request
     *
     * @var array
     */
    private $params = array();

    /**
     * Prefix of the uri inside application context
     *
     * @var string
     */
    private $contextPrefix = null;

    /**
     * Create a new instance of request
     *
     * @param string $defaultController
     *            The name of default controller of nothing is provided
     * @param string $defaultAction
     *            The name of default action if nothing is provided
     */
    private function __construct($defaultController, $defaultAction)
    {
        $this->controller = $defaultController;
        $this->action = $defaultAction;
    }

    /**
     * Parse an uri into its request parts
     *
     * @param string $uri
     *            The uri to parse
     *
     * @return \Nkey\Caribu\Mvc\Controller\Request The new created request
     */
    public static function parse($uri, $defaultController = 'Index', $defaultAction = 'index')
    {
        $req = new self($defaultController, $defaultAction);
        $req->origin = $uri;

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $req->remoteHost = $_SERVER['REMOTE_ADDR'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $req->remoteHost = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        // Save the request parameters for later usage and rewrite the uri
        $savedRequestParams = array();
        if (strpos($uri, '?')) {
            parse_str(substr($uri, strpos($uri, '?') + 1), $savedRequestParams);
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        // Since apache 2.3.13 we have now an additional index which provides the context
        if (isset($_SERVER['CONTEXT_PREFIX']) && $_SERVER['CONTEXT_PREFIX'] != '') {
            $req->contextPrefix = $_SERVER['CONTEXT_PREFIX'] . '/';
        } elseif (isset($_SERVER['REDIRECT_BASE'])) {
            // Try to determine the context from redirect base
            $req->contextPrefix = $_SERVER['REDIRECT_BASE'];
        } elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
            // Fallback - get context out of script path
            if (isset($_SERVER['HTTP_HOST'])) {
                $scriptName = preg_replace('/^.+[\\\\\\/]/', '', $_SERVER['SCRIPT_FILENAME']);
                $req->contextPrefix = str_replace($scriptName, '', $_SERVER['SCRIPT_NAME']);
            }
        }

        // All beyond the context prefix is our application request uri
        $contextUri = $uri;
        if (null != $req->contextPrefix) {
            $contextUri = str_replace($req->contextPrefix, '', $uri);
        }

        // Split parts
        $parts = array();
        if ($contextUri != '') {
            while ($contextUri[0] == '/') {
                $contextUri = substr($contextUri, 1);
            }
            $parts = explode('/', $contextUri);
        }

        // Check if there was a controller requested
        if (count($parts) > 0) {
            $req->controller = ucfirst(trim($parts[0]));
            array_shift($parts);
            if (! $req->controller) {
                $req->controller = $defaultController;
            }
        }

        // Check if there was an action requested
        if (count($parts) > 0) {
            $req->action = trim($parts[0]);
            array_shift($parts);
            if (! $req->action) {
                $req->action = $defaultAction;
            }
        }

        // Walk over all parameters and put them into container
        for ($i = 0; $i < count($parts); $i = $i + 2) {
            $paramName = trim($parts[$i]);
            $paramValue = isset($parts[$i + 1]) ? trim($parts[$i + 1]) : '';
            if ($paramName && $paramValue) {
                $req->params[$paramName] = $paramValue;
            }
        }

        $req->params = array_merge($req->params, $savedRequestParams);

        // Et'voila
        return $req;
    }

    /**
     * Parse uri directly from request uri
     *
     * @param $defaultController The
     *            name of the default controller
     * @param $defaultAction The
     *            name of the default action
     *
     * @return \Nkey\Caribu\Mvc\Controller\Request
     *
     * @throws InvalidUrlException If no uri exists (e.g. sapi = cli)
     */
    public static function parseFromServerRequest($defaultController = 'Index', $defaultAction = 'index')
    {
        if (! isset($_SERVER['REQUEST_URI'])) {
            throw new InvalidUrlException("No such uri provided");
        }
        return self::parse($_SERVER['REQUEST_URI'], $defaultController, $defaultAction);
    }

    /**
     * The origin uri
     *
     * @return string The original request string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * The requested controller
     *
     * @return string The controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * The requested action
     *
     * @return string The action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Retrieve the request parameters
     *
     * @return array The request parameters
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Retrieve the context prefix
     *
     * @return string The context prefix
     */
    public function getContextPrefix()
    {
        return $this->contextPrefix;
    }

    /**
     * Retrieve the remote host
     *
     * @return string The remote host address
     */
    public function getRemoteHost()
    {
        return $this->remoteHost;
    }
}
