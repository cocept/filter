# Cocept Filter

Cocept Filter is a bundle for Symfony and Doctrine that lets users filter datasets using a select widget.

The bundle provides a service and a widget. The widget shows the user a search input or select box which when changed inserts the appropriate filter options into the URL. 
The filter service gets the filter options from the URL and adds where clauses to the query builder to filter the results.

Multiple filters can be applied, and operators can also be specified. This bundle plays nicely with KNP Paginator.

# Demo Application

You can use this symfony demo application to see the filter bundle in action:

https://github.com/cocept/filter-test

# Installation

## Install with composer

```sh
composer require cocept/filter
```

## Load the bundle

Add the bundle to your application kernel:

```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Cocept\Bundle\FilterBundle\CoceptFilterBundle(),
        // ...
    );
}
```

## Apply the filter in your controller

Find the index action of the controller you want to filter and add the following line:

```php
$this->get('cocept_filter.filter')->filter($request, $qb, array('name'), array('category'));
```

The third parameter should be an array containing a list of regular fields (string, int etc) that the user is allowed to filter.

The fourth parameter should be an array containing a list of foreign key fields that the user is allowed to filter.

## Add the widget to your twig template

In your twig template, add the following line:

```jinja
{{ filterWidget('name') }}
{{ filterWidget('category', allCategories) }}
```

The first parameter of the filterWidget function should be the field on which to filter.

If the second parameter is omitted, the user will be shown a search field and the ilike operator will be used. Otherwise, an associative array must be provided and the user will see a select box and a button to toggle between the eq and neq operators. The array key will be used as the option label and the value as the option value.

So, if you want to show the user a list of category names, the array would be something like this:

```php
[
	'Code' => 1,
	'UX' => 2
]
```

## Load the JavaScripts

The filterWidget twig extension uses a javascript file to add the filter options to the URL parameters. Add the following to the head section of your twig template:

```jinja
<head>
	...
	{% javascripts
        	'@CoceptFilterBundle/Resources/public/js/jquery.js'
        	'@CoceptFilterBundle/Resources/public/js/jquery.query-object.js'
        	'@CoceptFilterBundle/Resources/public/js/filter.js'
	%}
		<script src="{{ asset_url }}"></script>
	{% endjavascripts %}
	...
</head>
```

## Try it out

The widget adds URL parameters like filter_name=someval, where name is the column name and someval is the value to match. You can also add operator_name=neq, where name is the column and neq is the operator to use in the where clause, with the following mappings:

 - neq: !=
 - ilike: ilike
 - eq: =

An example URL with filter parameters might be:

http://localhost:8000/post/?filter_category=5&operator_category=neq&filter_name=php&operator_name=ilike

# Contributing

This bundle is actively used in production and will be maintained as such. If you wish to submit a pull request it will be promptly considered and merged.
