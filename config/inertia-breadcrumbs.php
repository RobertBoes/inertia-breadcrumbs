<?php

use RobertBoes\InertiaBreadcrumbs\Classifier\AppendAllBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\Classifier\IgnoreSingleBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\Collectors\DiglacticBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\TabunaBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\GretelBreadcrumbsCollector;

return [
    'middleware' => [
        /**
         * Determines if the middleware should automatically be registered by this package
         * If you would like to register it yourself you should set this to false
         */
        'enabled' => true,

        /**
         * The middleware is added to the 'web' group by default
         */
        'group' => 'web',
    ],

    /**
     * By default a collector for diglactic/laravel-breadcrumbs is used
     * If you're using tabuna/breadcrumbs you can use TabunaBreadcrumbsCollector::class
     * If you're using glhd/gretel you can use GretelBreadcrumbsCollector::class (see notes in the readme about using this package)
     */
    'collector' => DiglacticBreadcrumbsCollector::class,

    /**
     * A classifier to determine if the breadcrumbs should be added to the Inertia response
     * This can be useful if you have defined a breadcrumb route which other routes can extend, but you don't want to show single breadcrumbs
     */
    'classifier' => AppendAllBreadcrumbs::class,
    // 'classifier' => IgnoreSingleBreadcrumbs::class,

    /**
     * Whether the query string should be ignored when determining the current route
     */
    'ignore_query' => true,
];
