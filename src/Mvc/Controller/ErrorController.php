<?php
namespace Nkey\Caribu\Mvc\Controller;

use \Nkey\Caribu\Mvc\View\View;
use Generics\Client\HttpStatus;

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
        printf("<h2>%s</h2>", HttpStatus::getStatus(404));
        printf("Requested document %s on %s could not be found!", $request->getAction(), $request->getController());
    }

    /**
     * Error processing for exceptions
     *
     * @param \Exception $ex
     */
    public function exception(Request $request)
    {
        $ex = $request->getException();

        $this->response->setCode(500);
        printf("<h2>%s</h2>", HttpStatus::getStatus(500));
        while ($ex != null) {
            printf("<h3>%s</h3><pre>%s</pre>", $ex->getMessage(), $ex->getTraceAsString());
            $ex = $ex->getPrevious();
        }
    }
}
