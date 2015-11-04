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
    use \Nkey\Caribu\Mvc\Util\RequestParser;

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
     * An occured exception
     *
     * @var \Exception
     */
    private $exception;

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
     * @param array $serverVars
     *            The variables provided by sapi
     *            
     * @param string $defaultController
     *            The name of the default controller if nothing else is requested
     *            
     * @param string $defaultAction
     *            The name of the default action if nothing else is requested
     *            
     * @return \Nkey\Caribu\Mvc\Controller\Request The new created request
     */
    public static function parse($uri, $serverVars = array(), $defaultController = 'Index', $defaultAction = 'index')
    {
        $req = new self($defaultController, $defaultAction);
        $req->origin = $uri;
        
        self::parseRemoteHost($req, $serverVars);
        
        self::parseGetPostSessionCookie($req);
        
        // Save the request parameters for later usage and rewrite the uri
        $savedRequestParams = array();
        if (strpos($uri, '?')) {
            parse_str(substr($uri, strpos($uri, '?') + 1), $savedRequestParams);
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        
        self::parseContextPrefix($req, $serverVars);
        
        $parts = self::parseUri($req, $uri, $defaultController, $defaultAction);
        
        // Walk over all parameters and put them into container
        $numParts = count($parts);
        for ($i = 0; $i < $numParts; $i = $i + 2) {
            $paramName = trim($parts[$i]);
            $paramValue = isset($parts[$i + 1]) ? trim($parts[$i + 1]) : '';
            if ($paramName && $paramValue) {
                $req->params[$paramName] = $paramValue;
            }
        }
        
        $req->params = array_merge($req->params, $savedRequestParams);
        
        self::parseParameters($req, $serverVars);
        
        // Et'voila
        return $req;
    }

    /**
     * Parse uri directly from request uri
     *
     * @param
     *            array The server variables provided by sapi
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
    public static function parseFromServerRequest($serverVars, $defaultController = 'Index', $defaultAction = 'index')
    {
        if (! isset($serverVars['REQUEST_URI'])) {
            throw new InvalidUrlException("No such uri provided");
        }
        return self::parse($serverVars['REQUEST_URI'], $serverVars, $defaultController, $defaultAction);
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

    /**
     * Check whether a given parameter exists
     *
     * @param string $name
     *            The name of the parameter
     * @return boolean true in case of it exists, false otherwise
     */
    public function hasParam($name)
    {
        return isset($this->params[$name]);
    }

    /**
     * Get value of particular parameter
     *
     * @param string $name
     *            The name of parameters
     * @param string $typeOf
     *            The type expected parameter value
     * @return mixed Depending on $typeOf the value as requested type and escaped
     */
    public function getParam($name, $typeOf = 'string')
    {
        $result = $this->hasParam($name) ? $this->params[$name] : null;
        
        switch ($typeOf) {
            
            case 'bool':
            case 'boolean':
                $result = function_exists('boolval') ? boolval($result) : (bool) $result;
                break;
            
            case 'double':
            case 'float':
                $result = doubleval($result);
                break;
            
            case 'int':
                $result = intval($result);
                break;
            
            case 'string':
            default:
                $result = htmlentities(strval($result));
                break;
        }
        
        return $result;
    }

    /**
     * To override a given parameter
     *
     * @param string $name
     *            The parameter name to override
     * @param string $value
     *            The value to override
     *            
     * @return Request the current request as fluent interface
     *        
     * @throws ControllerException in case of the parameter does not exist
     */
    public function setParam($name, $value)
    {
        if (! $this->hasParam($name)) {
            throw new ControllerException("Parameter {param} does not exist", array(
                'param' => $name
            ));
        }
        
        $this->params[$name] = $value;
    }

    /**
     * Set the exception occured
     *
     * @param Exception $ex            
     *
     * @return Request the current request instance
     */
    public function setException(\Exception $ex)
    {
        $this->exception = $ex;
        return $this;
    }

    /**
     * Get the exception occured
     *
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
