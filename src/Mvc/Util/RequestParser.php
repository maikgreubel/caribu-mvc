<?php
namespace Nkey\Caribu\Mvc\Util;

/**
 * Provides the request parsing functionality
 *
 * @author Maik Greubel <greubel@nkey.de>
 *        
 *         This file is part of Caribu MVC package
 */
trait RequestParser
{

    /**
     * Parse the context prefix variables to determine in which path
     * context the request has been performed.
     *
     * @param Request $request            
     */
    private static function parseContextPrefix(\Nkey\Caribu\Mvc\Controller\Request &$request, $serverVars = array())
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
    private static function parseUri(\Nkey\Caribu\Mvc\Controller\Request &$request, $uri, $defaultController, $defaultAction)
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
    private static function parseElement(\Nkey\Caribu\Mvc\Controller\Request &$req, $serverVars, $elementName, $paramName)
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
    private static function parseParameters(\Nkey\Caribu\Mvc\Controller\Request &$req, $serverVars)
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
     * Parse the remote host variables to determine client address
     *
     * @param Request $request            
     */
    private static function parseRemoteHost(\Nkey\Caribu\Mvc\Controller\Request &$request, $serverVars = array())
    {
        if (isset($serverVars['REMOTE_ADDR'])) {
            $request->remoteHost = $serverVars['REMOTE_ADDR'];
        }
        if (isset($serverVars['HTTP_X_FORWARDED_FOR'])) {
            $request->remoteHost = $serverVars['HTTP_X_FORWARDED_FOR'];
        }
    }

    /**
     * Parse the super globals for request parameters
     *
     * @param Request $request
     *            Request object to put the parameters in
     */
    private static function parseGetPostSessionCookie(\Nkey\Caribu\Mvc\Controller\Request &$request)
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
}
