<?php

namespace Cocept\Bundle\FilterBundle;

use \Symfony\Component\HttpFoundation\Request;
use \Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Filter
{
    public static $filterPrefix = "filter_";
    public static $operatorPrefix = "operator_";
    public static $defaultOperator = 'ilike';
    public static $filterValuePrefix = ':filterValue';
    public static $operators = array(
        'like',
        'ilike',
        'eq',
        'neq',
    );

    private $request;
    private $queryBuilder;
    private $allowedProperties;
    private $allowedRelationals;
    private $filters;

    public function __construct(Request $request, QueryBuilder $queryBuilder, Array $allowedProperties, Array $allowedRelationals){
        // cache params
        $this->setRequest($request);
        $this->setQueryBuilder($queryBuilder);
        $this->setAllowedProperties($allowedProperties);
        $this->setAllowedAssociations($allowedRelationals);

        // calculate filters
        $this->extractFilters();
    }

    public function filter(){
        $alias = $this->queryBuilder->getRootAliases()[0];

        foreach ($this->getFilters() as $key => $value) {
            // found a filter param so extract values
            $columnName = $this->paramToColumnName($key); // eg city
            $columnValue = $value; // eg London

            // only allow whitelisted columns
            $this->accessControl($columnName);

            // get operator
            $operator = $this->getOperator( $columnName );

            // update queryBuilder
            $this->updateQueryBuilder($operator, $alias, $columnName, $columnValue);
        }

        return $this->getQueryBuilder();
    }

    private function accessControl($columnName){
        // If field name is not allowed, throw 403
        if( false === in_array($columnName, $this->allowedProperties) 
            && false === in_array($columnName, $this->allowedRelationals) )
        {
            throw new HttpException(403, "Cheating are we? Can't filter by " . $columnName . "");
        }
    }

    /** 
     * Returns a human readable summary of filters 
     * e.g.: Type: House, Town: Darlaston, Postcode: WS10
     */
    public function getSummary(){
        $summary = array();
        foreach ($this->getFilters() as $key => $value) {
            $key = $this->paramToColumnName($key);
            $summary[] = ucfirst($key) . ": " . str_replace("%", "", ucfirst($value));
        }
        return implode(", ", $summary);
    }

    /**
     * Extracts filters from the url param string
     */
    private function extractFilters(){
        $filters = array();
        foreach ($this->request->query as $key => $value) {
            if($this->startsWith($this::$filterPrefix, $key))
                $filters[$key] = $value;
        }
        $this->setFilters($filters);
        return $this->getFilters();
    }

    /**
     * Removes the filter prefix from the param key
     */
    private function paramToColumnName($param){
        return substr($param, strlen($this::$filterPrefix));
    }

    /**
     * Constructs the where clause based on the operator being used
     */
    private function updateQueryBuilder($operator, $alias, $columnName, $columnValue) {
        // handle normal fields
        switch ($operator) {
            case 'ilike':
                return $this->_updateQueryBuilderIlike($operator, $alias, $columnName, $columnValue);
            case 'like':
                return $this->_updateQueryBuilderLike($operator, $alias, $columnName, $columnValue);
            case 'eq':
                return $this->_updateQueryBuilderEq($operator, $alias, $columnName, $columnValue);
            case 'neq':
                return $this->_updateQueryBuilderNeq($operator, $alias, $columnName, $columnValue);
        }
    }

    private function _updateQueryBuilderIlike($operator, $alias, $columnName, $columnValue) {
        if( in_array($columnName, $this->allowedRelationals) ) {
            $this->_updateQueryBuilderRelationalEq($alias, $columnName, $columnValue);
            return;
        }

        $columnValueParamKey = $this::$filterValuePrefix . $columnName; // :FilterValueCity
        $this->queryBuilder->andwhere("lower($alias.$columnName) like lower($columnValueParamKey)")
             ->setParameter($columnValueParamKey, "%$columnValue%");
    }

    private function _updateQueryBuilderLike($operator, $alias, $columnName, $columnValue) {
        if( in_array($columnName, $this->allowedRelationals) ) {
            $this->_updateQueryBuilderRelationalEq($alias, $columnName, $columnValue);
            return;
        }

        $columnValueParamKey = $this::$filterValuePrefix . $columnName; // :FilterValueCity
        $this->queryBuilder->andwhere("$alias.$columnName like $columnValueParamKey")
             ->setParameter($columnValueParamKey, "%$columnValue%");
    }

    private function _updateQueryBuilderEq($operator, $alias, $columnName, $columnValue) {
        if( in_array($columnName, $this->allowedRelationals) ) {
            $this->_updateQueryBuilderRelationalEq($alias, $columnName, $columnValue);
            return;
        }

        $columnValueParamKey = $this::$filterValuePrefix . $columnName; // :FilterValueCity
        $this->queryBuilder->andwhere("$alias.$columnName = $columnValueParamKey ")
             ->setParameter($columnValueParamKey, $columnValue);
    }

    private function _updateQueryBuilderNeq($operator, $alias, $columnName, $columnValue) {
        if( in_array($columnName, $this->allowedRelationals) ) {
            $this->_updateQueryBuilderRelationalNeq($alias, $columnName, $columnValue);
            return;
        }

        $columnValueParamKey = $this::$filterValuePrefix . $columnName; // :FilterValueCity
        $this->queryBuilder->andwhere("$alias.$columnName != $columnValueParamKey ")
             ->setParameter($columnValueParamKey, $columnValue);
    }

    private function _updateQueryBuilderRelationalEq($alias, $columnName, $columnValue){
        $this->queryBuilder->andwhere($this->queryBuilder->expr()->eq("IDENTITY($alias.$columnName)", $columnValue));
    }

    private function _updateQueryBuilderRelationalNeq($alias, $columnName, $columnValue){
        $this->queryBuilder->andwhere($this->queryBuilder->expr()->neq("IDENTITY($alias.$columnName)", $columnValue));
    }

    /**
     * Gets the filtration operator form the URL (one of $this::$operators)
     * If no operator, returns the default operator
     */
    private function getOperator($columnName){
        $operatorPrefixParam = $this::$operatorPrefix . $columnName;
        if($this->request->query->has( $operatorPrefixParam )) {
            $operatorParamValue = $this->request->query->get( $operatorPrefixParam );
            if(in_array($operatorParamValue, $this::$operators))
                return $operatorParamValue;
        }
        return $this::$defaultOperator;
    }

    /**
     * Returns true if the $haystack starts with the $needle 
     */
    private function startsWith($needle, $haystack)
    {
         $length = strlen($needle);
         return (substr($haystack, 0, $length) === $needle);
    }

    public function getRequest(){
        return $this->request;
    }

    public function setRequest($request){
        $this->request = $request;
        $this->setFilters(null);
    }

    public function getFilters(){
        return $this->filters;
    }

    public function setFilters($filters){
        $this->filters = $filters;
    }

    public function getQueryBuilder(){
        return $this->queryBuilder;
    }

    public function setQueryBuilder($queryBuilder){
        $this->queryBuilder = $queryBuilder;
    }

    public function getAllowedProperties(){
        return $this->allowedProperties;
    }

    public function setAllowedProperties($allowedProperties){
        $this->allowedProperties = $allowedProperties;
    }

    public function getAllowedAssociations(){
        return $this->allowedRelationals;
    }

    public function setAllowedAssociations($allowedRelationals){
        $this->allowedRelationals = $allowedRelationals;
    }
}
