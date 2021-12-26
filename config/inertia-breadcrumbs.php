<?php

return [
    'middleware' => [
        'enabled' => true,
        'group' => 'web',
    ],

    'collector' => RobertBoes\InertiaBreadcrumbs\LaravelBreadcrumbsCollector::class,
];
