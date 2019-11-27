<?php

namespace Cocept\Bundle\FilterBundle;

use Symfony\Component\HttpFoundation\RequestStack;

class FilterWidget extends \Twig_Extension
{

    protected $request;

    public function setRequest(RequestStack $request_stack)
    {
        $this->request = $request_stack->getCurrentRequest();
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('filterWidget', array($this, 'filterWidget'), ['is_safe' => ['html'], 'needs_environment' => true])
        );
    }

    public function filterWidget(\Twig_Environment $environment, string $columnName, array $options = null){
        // get current filter
        $request = $this->request;
        $currentFilter = null;
        $currentOperator = null;

        if($request->query->has("filter_" . $columnName)) {
            $currentFilter = $request->query->get('filter_' . $columnName);
        }

        if($request->query->has("operator_" . $columnName)) {
            $currentOperator = $request->query->get('operator_' . $columnName);
        }

        $operatorToggleText = 'Is';
        if ($currentOperator == 'neq') {
            $operatorToggleText = 'Not';
        }
        
        // render template
        return $environment->render("CoceptFilterBundle:Filter:filter_widget.html.twig", 
            array(
                "request" => $request, 
                "columnName" => $columnName, 
                "options" => $options, 
                "operatorToggleText" => $operatorToggleText,
                "currentFilter" => $currentFilter,
                "currentOperator" => $currentOperator,
            ));
    }

    public function getName()
    {
        return "filterWidget";
    }
}
