<?php

namespace Cocept\Bundle\FilterBundle;

use \Symfony\Component\HttpFoundation\Request;
use \Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Cocept\Bundle\FilterBundle;

class FilterFactory 
{
	/**
	 * Checks the request get params for keys starting with "filter_"
     * For each one found, adds a where clause to the query builder
     * like where key (in filter_*key*) = value (in filter_key=*value*)
	 */
    public function filter(Request $request, QueryBuilder $qb, Array $allowedProperties, Array $allowedRelationals=null){
        if(is_null($allowedRelationals))
            $allowedRelationals = array();

        $filter = new Filter($request, $qb, $allowedProperties, $allowedRelationals);
        $filter->filter();
        return $filter;

    }
}
