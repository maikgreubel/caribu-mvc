<?php
namespace Nkey\Caribu\Mvc\View\Controls;

use Nkey\Caribu\Mvc\View\Control;
use Nkey\Caribu\Mvc\Controller\Request;

/**
 * A very basic form control
 *
 * @author Maik Greubel <greubel@nkey.de>
 *         This file is part of Caribu MVC package
 */
class Form implements Control
{

    /**
     * (non-PHPdoc)
     *
     * @see \Nkey\Caribu\Mvc\View\Control::render()
     */
    public function render(Request $request, $parameters = array())
    {
        $contentPrefix = "/";
        if (!is_null($request->getContextPrefix())) {
            $contentPrefix = $request->getContextPrefix();
        }

        $formAction = sprintf(
            "%s%s/%s",
            $contentPrefix,
            isset($parameters['controller']) ? $parameters['controller'] : lcfirst($request->getController()),
            isset($parameters['action']) ? $parameters['action'] : lcfirst($request->getAction())
        );

        if (isset($parameters['formAction'])) {
            $formAction = $parameters['formAction'];
        }

        $rendered = sprintf(
            '<form action="%s" method="%s"%s>',
            $formAction,
            isset($parameters['formMethod']) ? $parameters['formMethod'] : "POST",
            isset($parameters['formClass']) ? (sprintf(' class="%s"', $parameters['formClass'])) : ""
        );

        foreach($parameters['fields'] as $field) {
            if(!isset($field['name'])) {
                throw new ControlException("Field must have at least a name!");
            }
            $fieldType = isset($field['type']) ? $field['type'] : 'text';
            $id = isset($field['id']) ? $field['id'] : $field['name'];
            $class = isset($field['class']) ? $field['class'] : $field['name'];

            $rendered .= sprintf(
                '<input type="%s" id="%s" class="%s" name="%s"/>',
                $fieldType,
                $id,
                $class,
                $field['name']
            );
        }

        foreach($parameters['buttons'] as $button) {
            if(!isset($button['name'])) {
                throw new ControlException("Button must have at least a name!");
            }
            $buttonType = isset($button['type']) ? $button['type'] : "submit";
            $id = isset($button['id']) ? $button['id'] : $button['name'];
            $class = isset($button['class']) ? $button['class'] : $button['name'];
            $label = isset($button['label']) ? $button['label'] : $button['name'];

            $rendered .= sprintf(
                '<button type="%s" id="%s" class="%s" name="%s">%s</button>',
                $buttonType,
                $id,
                $class,
                $button['name'],
                $label
            );
        }

        $rendered .= sprintf("</form>");

        return $rendered;
    }
}
