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
     * Parse the remote host variables to determine client address
     *
     * @param Request $request
     */
    private static function parseRemoteHost(Request &$request)
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $request->remoteHost = $_SERVER['REMOTE_ADDR'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $request->remoteHost = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }

    /**
     * Parse the context prefix variables to determine in which path
     * context the request has been performed.
     *
     * @param Request $request
     */
    private static function parseContextPrefix(Request &$request)
    {
        // Since apache 2.3.13 we have now an additional index which provides the context
        if (isset($_SERVER['CONTEXT_PREFIX']) && $_SERVER['CONTEXT_PREFIX'] != '') {
            $request->contextPrefix = $_SERVER['CONTEXT_PREFIX'] . '/';
        } elseif (isset($_SERVER['REDIRECT_BASE'])) {
            // Try to determine the context from redirect base
            $request->contextPrefix = $_SERVER['REDIRECT_BASE'];
        } elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
            // Fallback - get context out of script path
            if (isset($_SERVER['HTTP_HOST'])) {
                $scriptName = preg_replace('/^.+[\\\\\\/]/', '', $_SERVER['SCRIPT_FILENAME']);
                $request->contextPrefix = str_replace($scriptName, '', $_SERVER['SCRIPT_NAME']);
            }
        }
    }

    /**
     * Parse the prepared uri into its parts
     *
     * @param Request $request The unprepared request object
     * @param string $uri The prepared uri
     * @param string $defaultController The name of default controller if nothing is requested
     * @param string $defaultAction The name of default action if nothing is requested
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
            while ($contextUri[0] == '/') {
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
     * @param Request $request Request object to put the parameters in
     */
    private static function parseGetPostSessionCookie(Request &$request)
    {
        foreach($_GET as $name => $value) {
            $request->params[$name] = $value;
        }
        foreach($_POST as $name => $value) {
            $request->params[$name] = $value;
        }
        foreach($_COOKIE as $name => $value) {
            $request->params[$name] = $value;
        }
        foreach($_FILES as $name => $value) {
            $request->params[$name] = $value;
        }
        if (isset($_SESSION)) {
            foreach($_SESSION as $name => $value) {
                $request->params[$name] = $value;
            }
        }
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

        self::parseRemoteHost($req);

        self::parseGetPostSessionCookie($req);

        // Save the request parameters for later usage and rewrite the uri
        $savedRequestParams = array();
        if (strpos($uri, '?')) {
            parse_str(substr($uri, strpos($uri, '?') + 1), $savedRequestParams);
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        self::parseContextPrefix($req);

        $parts = self::parseUri($req, $uri, $defaultController, $defaultAction);

        // Walk over all parameters and put them into container
        for ($i = 0; $i < count($parts); $i = $i + 2) {
            $paramName = trim($parts[$i]);
            $paramValue = isset($parts[$i + 1]) ? trim($parts[$i + 1]) : '';
            if ($paramName && $paramValue) {
                $req->params[$paramName] = $paramValue;
            }
        }

        $req->params = array_merge($req->params, $savedRequestParams);

        // Read the options from http headers
        if (php_sapi_name() != 'cli') {
            $req->params['Accept'] = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
            $req->params['Accept-Language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;
            $req->params['Accept-Encoding'] = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : null;
            $req->params['User-Agent-CPU'] = isset($_SERVER['HTTP_UA_CPU']) ? $_SERVER['HTTP_UA_CPU'] : null;
            $req->params['User-Agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
            $req->params['Host'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
            $req->params['Cache-Control'] = isset($_SERVER['HTTP_CACHE_COTROL']) ? $_SERVER['HTTP_CACHE_COTROL'] : null;
            $req->params['Connection'] = isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : null;
            $req->params['X-Forwarded-For'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;

            if (null !== $req->params['Accept-Language']) {
                $accepted = explode(',',$req->params['Accept-Language']);
                $req->params['Accept-Language-Best'] = $accepted[0];
                foreach ($accepted as $acceptedLang) {
                    $matches = array();
                    if (preg_match("/^((?i)[a-z]{2}[-_](?:[a-z]{2}){1,2}(?:_[a-z]{2})?).*/", $acceptedLang, $matches)) {
                        $req->params['Accept-Language-Best'] = $matches[1];
                        break;
                    }
                }
            }
        }

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

    /**
     * Check whether a given parameter exists
     *
     * @param string $name The name of the parameter
     * @return boolean true in case of it exists, false otherwise
     */
    public function hasParam($name)
    {
        return isset($this->params[$name]);
    }

    /**
     * Get value of particular parameter
     *
     * @param string $name The name of parameters
     * @param string $typeOf The type expected parameter value
     * @return mixed Depending on $typeOf the value as requested type and escaped
     */
    public function getParam($name, $typeOf = 'string')
    {
        $result = $this->hasParam($name) ? $this->params[$name] : null;

        switch($typeOf) {

            case 'bool':
            case 'boolean':
                $result = function_exists('boolval') ? boolval($result) : (bool)$result;
                break;

            case 'string':
            default:
                $result = htmlentities(strval($result));
                break;
        }

        return $result;
    }
}
