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
     * Parse the remote host variables to determine client address
     *
     * @param Request $request            
     */
    private static function parseRemoteHost(Request &$request, $serverVars = array())
    {
        if (isset($serverVars['REMOTE_ADDR'])) {
            $request->remoteHost = $serverVars['REMOTE_ADDR'];
        }
        if (isset($serverVars['HTTP_X_FORWARDED_FOR'])) {
            $request->remoteHost = $serverVars['HTTP_X_FORWARDED_FOR'];
        }
    }

    /**
     * Parse the context prefix variables to determine in which path
     * context the request has been performed.
     *
     * @param Request $request            
     */
    private static function parseContextPrefix(Request &$request, $serverVars = array())
    {
        // Since apache 2.3.13 we have now an additional index which provides the context
        if (isset($serverVars['CONTEXT_PREFIX']) && $serverVars['CONTEXT_PREFIX'] != '') {
            $request->contextPrefix = $serverVars['CONTEXT_PREFIX'] . '/';
        } elseif (isset($serverVars['REDIRECT_BASE'])) {
            // Try to determine the context from redirect base
            $request->contextPrefix = $serverVars['REDIRECT_BASE'];
        } elseif (isset($serverVars['SCRIPT_FILENAME']) && isset($serverVars['SCRIPT_NAME'])) {
            // Fallback - get context out of script path
            if (isset($serverVars['HTTP_HOST'])) {
                $scriptName = preg_replace('/^.+[\\\\\\/]/', '', $serverVars['SCRIPT_FILENAME']);
                $request->contextPrefix = str_replace($scriptName, '', $serverVars['SCRIPT_NAME']);
            }
        }
    }

    /**
     * Parse the prepared uri into its parts
     *
     * @param Request $request
     *            The unprepared request object
     * @param string $uri
     *            The prepared uri
     * @param string $defaultController
     *            The name of default controller if nothing is requested
     * @param string $defaultAction
     *            The name of default action if nothing is requested
     *            
     * @return array Parsed parts for later usage
     */
    private static function parseUri(Request &$request, $uri, $defaultController, $defaultAction)
    {
        // All beyond the context prefix is our application request uri
        $contextUri = $uri;
        if (null != $request->contextPrefix && '/' != $request->contextPrefix) {
            $contextUri = str_replace($request->contextPrefix, '', $uri);
        }
        
        // Split parts
        $parts = array();
        if ($contextUri != '') {
            while (isset($contextUri[0]) && $contextUri[0] == '/') {
                $contextUri = substr($contextUri, 1);
            }
            $parts = explode('/', $contextUri);
        }
        
        // Check if there was a controller requested
        if (count($parts) > 0) {
            $request->controller = ucfirst(trim($parts[0]));
            array_shift($parts);
            if (! $request->controller) {
                $request->controller = $defaultController;
            }
        }
        
        // Check if there was an action requested
        if (count($parts) > 0) {
            $request->action = trim($parts[0]);
            array_shift($parts);
            if (! $request->action) {
                $request->action = $defaultAction;
            }
        }
        
        return $parts;
    }

    /**
     * Parse the super globals for request parameters
     *
     * @param Request $request
     *            Request object to put the parameters in
     */
    private static function parseGetPostSessionCookie(Request &$request)
    {
        foreach ($_GET as $name => $value) {
            $request->params[$name] = $value;
        }
        foreach ($_POST as $name => $value) {
            $request->params[$name] = $value;
        }
        foreach ($_COOKIE as $name => $value) {
            $request->params[$name] = $value;
        }
        foreach ($_FILES as $name => $value) {
            $request->params[$name] = $value;
        }
        if (isset($_SESSION)) {
            foreach ($_SESSION as $name => $value) {
                $request->params[$name] = $value;
            }
        }
    }

    /**
     * Parse a single http header element into parameter for the request object
     *
     * @param Request $req
     *            The destination request object
     * @param array $serverVars
     *            The server variables provided by sapi
     * @param string $elementName
     *            The element to parse
     * @param string $paramName
     *            The destination parameter name
     */
    private static function parseElement(Request &$req, $serverVars, $elementName, $paramName)
    {
        if (isset($serverVars[$elementName])) {
            $req->params[$paramName] = $serverVars[$elementName];
        }
    }

    /**
     * Parse the server variables which represents HTTP headers into parameter values for the request object
     *
     * @param Request $req
     *            The request object
     *            
     * @param
     *            array The server variables provided by sapi
     */
    private static function parseParameters(Request &$req, $serverVars)
    {
        self::parseElement($req, $serverVars, 'HTTP_ACCEPT', 'Accept');
        self::parseElement($req, $serverVars, 'HTTP_ACCEPT_LANGUAGE', 'Accept-Language');
        self::parseElement($req, $serverVars, 'HTTP_ACCEPT_ENCODING', 'Accept-Encoding');
        self::parseElement($req, $serverVars, 'HTTP_UA_CPU', 'User-Agent-CPU');
        self::parseElement($req, $serverVars, 'HTTP_USER_AGENT', 'User-Agent');
        self::parseElement($req, $serverVars, 'HTTP_HOST', 'Host');
        self::parseElement($req, $serverVars, 'HTTP_CACHE_COTROL', 'Cache-Control');
        self::parseElement($req, $serverVars, 'HTTP_CONNECTION', 'Connection');
        self::parseElement($req, $serverVars, 'HTTP_X_FORWARDED_FOR', 'X-Forwarded-For');
        
        if (isset($req->params['Accept-Language'])) {
            $accepted = explode(',', $req->params['Accept-Language']);
            $req->params['Accept-Language-Best'] = $accepted[0];
            foreach ($accepted as $acceptedLang) {
                $matches = array();
                // TODO: Respect the quality field from rfc2616
                if (preg_match("/^((?i)[a-z]{2}[-_](?:[a-z]{2}){1,2}(?:_[a-z]{2})?).*/", $acceptedLang, $matches)) {
                    $req->params['Accept-Language-Best'] = $matches[1];
                    break;
                }
            }
        }
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
