<?php
namespace Nkey\Caribu\Mvc\View;

use \Nkey\Caribu\Mvc\Controller\Response;
use \Nkey\Caribu\Mvc\Controller\Request;
use \Nkey\Caribu\Mvc\View\AbstractView;

/**
 * This is the default view class
 *
 *
 * It provides the lowest level of view rending possible.
 * The render function determines the output type and either
 * renders html or let the body content untouched.
 *
 * @applyTo(controller=any,action=any)
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
class DefaultView extends AbstractView
{
    /**
     * (non-PHPdoc)
     * @see \Nkey\Caribu\Mvc\View\View::getOrder()
     */
    public function getOrder()
    {
        return -1;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Nkey\Caribu\Mvc\View\View::render()
     */
    public function render(Response &$response, Request $request, array $parameters = array())
    {
        if ($response->getType() == 'text/html') {
            $html = sprintf("
<!DOCTYPE html>
<html>
<head>
<title>%s</title>
</head>

<body>
%s
</body>

</html>", $response->getTitle(), $response->getBody());

            $response->setBody($html);
        }
    }
}
