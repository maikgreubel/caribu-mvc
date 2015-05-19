<?php
namespace Nkey\Caribu\Mvc\View;

use Nkey\Caribu\Mvc\Controller\Request;

/**
 * This interface describes a control
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
interface Control
{
    /**
     * Renders the control
     *
     * @param Request $request The request
     * @param mixed $parameters Optional additional parameters
     *
     * @return string The rendered control
     *
     * @throws \Nkey\Caribu\Mvc\View\Controls\ControlException
     */
    public function render(Request $request, $parameters = array());
}
