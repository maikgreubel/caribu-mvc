<?php
namespace Nkey\Caribu\Mvc\View;

use Nkey\Caribu\Mvc\Controller\Request;

interface Control
{
    /**
     * Renders the control
     *
     * @param Request $request The request
     * @param array $parameters Optional additional parameters
     *
     * @return string The rendered control
     *
     * @throws \Nkey\Caribu\Mvc\View\Controls\ControlException
     */
    public function render(Request $request, array $parameters = array());
}
