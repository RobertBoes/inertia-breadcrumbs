<?php

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
];
