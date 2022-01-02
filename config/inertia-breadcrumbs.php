<?php

use RobertBoes\InertiaBreadcrumbs\Collectors\DiglacticBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\TabunaBreadcrumbsCollector;

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
     */
    'collector' => DiglacticBreadcrumbsCollector::class,
];
