# Cocept Filter

Cocept Filter is a bundle for Symfony and Doctrine that lets users filter datasets using a select widget.

The bundle provides a service and a widget. The widget shows the user a select which when changed inserts the appropriate filter options into the URL. The filter service gets the filter options from the URL and adds where clauses to the query builder to filter the results.

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
{{ filterWidget('name', allNames) }}
{{ filterWidget('category', allCategories) }}
```

The second parameter of the filterWidget function should be an associative array. The key is used as the select box option text and the value is used as the select box option value.

So, if you want to show the user a list of category names, the array would be something like this:

```php
[
	'Code' => 1,
	'UX' => 2
]
```

# Contributing

This bundle is actively used in production and will be maintained as such. If you wish to submit a pull request it will be promptly considered and merged.

