<?php
namespace Nkey\Caribu\Mvc;

use \Generics\GenericsException;
use \Nkey\Caribu\Mvc\Controller\AbstractController;
use \Nkey\Caribu\Mvc\Controller\ControllerException;
use \Nkey\Caribu\Mvc\Controller\Request;
use \Nkey\Caribu\Mvc\View\ViewException;
use \Nkey\Caribu\Mvc\View\View;
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\NullLogger;

/**
 * The MVC Application main class
 *
 * This class provides all functions to route the request to
 * responsible controller and action. If no controller/action
 * could be found to match the request, the error controller
 * will be triggered.
 *
 * To work correctly all available controllers and views must
 * be registered before the request can be routed.
 *
 * The routing and rendering process will be performed by
 * calling the Application::serve() function
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
final class Application implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Retrieve the logging instance
     *
     * @return \Psr\Log\LoggerInterface The logging instance
     */
    private function getLogger()
    {
        return $this->logger;
    }

    /**
     * List of controllers
     *
     * @var array
     */
    private $controllers = null;

    /**
     * List of views
     *
     * @var array
     */
    private $views = null;

    /**
     * List of view controls
     *
     * @var array
     */
    private $viewControls = null;

    /**
     * The default controller
     *
     * @var string
     */
    private $defaultController = 'Index';

    /**
     * The default action
     *
     * @var string
     */
    private $defaultAction = 'index';

    /**
     * Singleton instance
     *
     * @var \Nkey\Caribu\Mvc\Application
     */
    private static $instance = null;

    /**
     * Default headers to send to client
     *
     * @var array
     */
    private $defaultHeaders = array();

    /**
     * The client request headers to override
     * @var array
     */
    private $overridenClientHeaders = array();

    /**
     * Additional css files to include in view
     * @var array
     */
    private $cssFiles = array();

    /**
     * Additional javascript files to include in view
     * @var array
     */
    private $jsFiles = array();

    /**
     * Get application instance
     *
     * @return \Nkey\Caribu\Mvc\Application
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Singleton constructor
     */
    private function __construct()
    {
        $this->setUp();
    }

    /**
     * Set up the default values
     *
     * @return Application Current application instance
     */
    public function setUp()
    {
        $this->controllers = array();
        $this->views = array();
        $this->viewControls = array();
        $this->setDefaults();
        $this->init();
        $this->setLogger(new NullLogger());

        return $this;
    }

    /**
     * Singleton instance
     */
    public function __clone()
    {
        throw new GenericsException("Cloning is prohibited");
    }

    /**
     * Init the application
     *
     * Register internally needed controller and view
     */
    public function init()
    {
        $this->registerController('\Nkey\Caribu\Mvc\Controller\ErrorController');
        $this->registerView('\Nkey\Caribu\Mvc\View\DefaultView');
    }

    /**
     * Set the default controller and action
     *
     * @param string $defaultController
     *            The default controller name if nothing is provided by request
     * @param string $defaultAction
     *            The default action name if nothing is provided by request
     * @return Application Current application instance
     */
    public function setDefaults($defaultController = 'Index', $defaultAction = 'index')
    {
        $this->defaultController = $defaultController;
        $this->defaultAction = $defaultAction;

        return $this;
    }

    /**
     * Register a new view
     *
     * @param AbstractView $view
     *            The view instance
     * @param int $order
     *            Override the default order given by view class
     * @param string $applicationName
     *            The application name where the view will be available in
     *
     * @throws ViewException
     *
     * @return Application Current application instance
     */
    public function registerView($view, $order = null, $applicationName = 'default')
    {
        if (! class_exists($view)) {
            throw new ViewException("No such view class {view} found", array(
                'view' => $view
            ));
        }

        $v = new $view();
        if (! $v instanceof View) {
            throw new ViewException("View {view} is not in application scope", array(
                'view' => $view
            ));
        }
        $viewOrder = $v->getOrder();
        if (null != $order) {
            $viewOrder = intval($order);
        }

        $settings = $v->getViewSettings();
        $this->views[$applicationName][$viewOrder][$settings->getViewSimpleName()] = $settings;

        return $this;
    }

    /**
     * Register a view control
     *
     * @param string $controlIdentifier The identifier under which the control will be registered
     * @param string $controlClass The class of control
     *
     * @return Application Current application instance
     */
    public function registerViewControl($controlIdentifier, $controlClass)
    {
        $this->viewControls[$controlIdentifier] = $controlClass;
        return $this;
    }

    /**
     * Unregister a given view
     *
     * @param string $view
     *            The view to unregister
     * @param string $applicationName
     *            Optional application name where the view is registered
     *
     * @return Application Current application instance
     */
    public function unregisterView($view, $order, $applicationName = 'default')
    {
        if (isset($this->views[$applicationName][$order][$view])) {
            unset($this->views[$applicationName][$order][$view]);
        }
        return $this;
    }

    /**
     * Get the best view for request
     *
     * @param Request $request
     *            The request to get best view for
     *
     * @return View The view best matched for the request
     *
     * @throws ViewException
     */
    private function getViewBestMatch(Request $request, $applicationName)
    {
        $best = null;

        if (count($this->views[$applicationName]) > 0) {
            foreach ($this->views[$applicationName] as $orderLevel => $views) {
                foreach ($views as $view) {
                    assert($view instanceof View);
                    if ($view->matchBoth($request->getController(), $request->getAction())) {
                        $best[$orderLevel] = $view;
                        continue 2;
                    }
                }
            }
        }

        if (null == $best) {
            throw new ViewException("No view found for request");
        }

        if (count($best) > 1) {
            rsort($best);
        }

        return reset($best);
    }

    /**
     * Register a new controller class
     *
     * @param string $controller
     *            The full qualified name of controller class to register
     * @param string $applicationName
     *            Optional name of application where controller will be registered in
     *
     * @return Application Current application instance
     *
     * @throws ControllerException
     */
    public function registerController($controller, $applicationName = 'default')
    {
        if (! class_exists($controller)) {
            throw new ControllerException("No such controller class {controller} found", array(
                'controller' => $controller
            ));
        }
        $c = new $controller();
        if (! ($c instanceof AbstractController)) {
            throw new ControllerException("Controller {controller} is not in application scope", array(
                'controller' => $controller
            ));
        }

        $settings = $c->getControllerSettings();
        $this->controllers[$applicationName][$settings->getControllerSimpleName()] = $settings;

        return $this;
    }

    /**
     * Start the application
     *
     * @param string $applicationName
     *            Optional application name to service the request for
     *
     * @param Request $request
     *            Optional previous generated request object
     *
     * @param boolean $send
     *            Optional whether to send the output directly to client
     *
     * @throws ControllerException
     * @throws InvalidUrlException
     */
    public function serve($applicationName = 'default', Request $request = null, $send = true)
    {
        if (null == $request) {
            $request = Request::parseFromServerRequest($this->defaultController, $this->defaultAction);
        }

        foreach ($this->overridenClientHeaders as $headerName => $headerValue) {
            $request->setParam($headerName, $headerValue);
        }

        $controller = $request->getController();
        $action = $request->getAction();

        $this->getLogger()->debug("[{remote}] Requested controller is {controller} and action is {action}", array(
            'remote' => $request->getRemoteHost(),
            'controller' => $controller,
            'action' => $action
        ));

        if (! isset($this->controllers[$applicationName][$controller])) {
            $this->getLogger()->error("[{remote}] No such controller {controller}", array(
                'remote' => $request->getRemoteHost(),
                'controller' => $controller
            ));
            $controller = 'Error';
            $action = 'error';
        }

        $controllerInstance = $this->controllers[$applicationName][$controller];
        assert($controllerInstance instanceof AbstractController);
        if (! $controllerInstance->hasAction($action)) {
            $this->getLogger()->error("[{remote}] No such action {action}", array(
                'remote' => $request->getRemoteHost(),
                'action' => $action
            ));
            $controllerInstance = $this->controllers[$applicationName]['Error'];
            $action = 'error';
        }

        $this->getLogger()->debug("[{remote}] Routing request to {controller}:{action}", array(
            'remote' => $request->getRemoteHost(),
            'controller' => $controller,
            'action' => $action
        ));

        $view = $this->getViewBestMatch($request, $applicationName);
        $view->setCssFiles($this->cssFiles);
        $view->setJsFiles($this->jsFiles);

        foreach($this->viewControls as $controlIdentifier => $controlClass) {
            $view->registerControl($controlClass, $controlIdentifier);
        }

        try
        {
            $response = $controllerInstance->call($action, $request, $view);
        }
        catch(\Exception $ex)
        {
            $controllerInstance = $this->controllers[$applicationName]['Error'];
            $action = 'exception';
            $request->setException($ex);
            $response = $controllerInstance->call($action, $request, $view);
        }

        $responseCode = $response->getHttpCode();
        $responseLen = strlen($response);
        $responseType = sprintf('%s;%s', $response->getType(), $response->getEncoding());
        $responseContent = strval($response);

        $this->getLogger()->debug("[{remote}] Response is type of {type}, length of {length} and code {code}", array(
            'remote' => $request->getRemoteHost(),
            'type' => $responseType,
            'length' => $responseLen,
            'code' => $responseCode
        ));

        if ($send) {
            header(sprintf("%s", $responseCode));
            header(sprintf("Content-Length: %d", $responseLen));
            header(sprintf("Content-Type: %s", $responseType));

            foreach ($this->defaultHeaders as $headerName => $headerValue) {
                header(sprintf("%s: %s", $headerName, $headerValue));
            }
            foreach ($response->getAdditionalHeaders() as $headerName => $headerValue) {
                header(sprintf("%s: %s", $headerName, $headerValue));
            }

            echo $responseContent;
        }

        return $response;
    }

    /**
     * Enable session handling
     *
     * @return Application The current application instance
     */
    public function enableSession()
    {
        session_start();

        return $this;
    }

    /**
     * Retrieve the default controller name
     *
     * @return string The name of default controller
     */
    public function getDefaultController()
    {
        return $this->defaultController;
    }

    /**
     * Retrieve the default action name
     *
     * @return string The name of default action
     */
    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    /**
     * Add a new header to specific value.
     *
     * Existing header will be overriden.
     *
     * @param string $name The header identifier
     * @param string $value The value to set
     *
     * @return Application The current application instance
     */
    public function addHeader($name, $value)
    {
        $this->defaultHeaders[$name] = $value;
        return $this;
    }

    /**
     * Add a header to overide a client request header
     *
     * @param string $name The header name to override
     * @param string $value The value to override
     *
     * @return Application The current application instance
     */
    public function addOverridenClientHeader($name, $value)
    {
        $this->overridenClientHeaders[$name] = $value;
        return $this;
    }

    /**
     * Add an uri for an additional javascript file
     *
     * @param string $file
     * @return Application the current application instance
     */
    public function addJsFile($file)
    {
        $this->jsFiles[] = $file;
        return $this;
    }

    /**
     * Add an uri for an additional css file
     * @param string $file
     * @return Application the current application instance
     */
    public function addCssFile($file)
    {
        $this->cssFiles[] = $file;
        return $this;
    }
}
