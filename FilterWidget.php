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

    public function filterWidget(\Twig_Environment $environment, $columnName, array $options){
        // get current filter
        $request = $this->request;
        $currentFilter = null;
        $paramName = "filter_" . $columnName;
        if($request->query->has($paramName))
            $currentFilter = $request->query->get('filter_' . $columnName);
        
        // render template
        return $environment->render("CoceptFilterBundle:Filter:filter_widget.html.twig", 
            array("request" => $request, "columnName" => $columnName, "options" => $options, "currentFilter" => $currentFilter));
    }

    public function getName()
    {
        return "filterWidget";
    }
}
