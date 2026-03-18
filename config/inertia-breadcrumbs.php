<?php

use RobertBoes\InertiaBreadcrumbs\Classifier\AppendAllBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\Classifier\IgnoreSingleBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\Collectors\ClosureBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\DiglacticBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\GretelBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\TabunaBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\ShareStrategy;

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

        /**
         * The key of shared breadcrumbs
         */
        'key' => 'breadcrumbs',
    ],

    /**
     * Controls how breadcrumbs are shared with Inertia.
     *
     * - ShareStrategy::Default  — Standard shared prop (excluded during partial reloads unless requested)
     * - ShareStrategy::Always   — Always included, even during partial reloads
     * - ShareStrategy::Deferred — Excluded from initial load, auto-fetched after render
     */
    'share' => ShareStrategy::Default,

    /**
     * By default a collector for diglactic/laravel-breadcrumbs is used
     * If you're using tabuna/breadcrumbs you can use TabunaBreadcrumbsCollector::class
     * If you're using glhd/gretel you can use GretelBreadcrumbsCollector::class (see notes in the readme about using this package)
     * If you don't want an external package you can use ClosureBreadcrumbsCollector::class
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
