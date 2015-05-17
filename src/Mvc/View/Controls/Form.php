<?php
namespace Nkey\Caribu\Mvc\View\Controls;

use Nkey\Caribu\Mvc\View\Control;
use Nkey\Caribu\Mvc\Controller\Request;

class Form implements Control
{

    /**
     * (non-PHPdoc)
     *
     * @see \Nkey\Caribu\Mvc\View\Control::render()
     */
    public function render(Request $request, array $parameters = array())
    {
        $formAction = sprintf(
            "%s%s/%s",
            !is_null($request->getContextPrefix() && !empty($request->getContextPrefix())) ? $request->getContextPrefix() : "",
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

            $rendered .= sprintf(
                '<input type="%s" id="%s" class="%s" name="%s"/>',
                $buttonType,
                $id,
                $class,
                $button['name']
            );
        }
        $rendered .= sprintf("</form>");

        return $rendered;
    }
}
