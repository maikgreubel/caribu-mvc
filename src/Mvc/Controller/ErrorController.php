<?php
namespace Nkey\Caribu\Mvc\Controller;

use \Nkey\Caribu\Mvc\View\View;

/**
 * The error controller
 *
 * The error controller provides the fallback rendering if some request
 * could not be routed correctly.
 *
 * It will be registered in application as soon as the Application::init()
 * function is performed.
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
class ErrorController extends AbstractController
{
    /**
     * Error processing method
     *
     * @param Request $request The request
     */
    public function error(Request $request)
    {
        $this->response->setCode(404);
        printf("<h2>HTTP 404</h2>");
        printf("Requested document %s on %s could not be found!", $request->getAction(), $request->getController());
    }
}
