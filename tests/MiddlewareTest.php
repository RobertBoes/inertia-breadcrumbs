<?php

use Illuminate\Routing\Router;
use RobertBoes\InertiaBreadcrumbs\Middleware;

it('adds middleware to web group', function () {
    expect($this->app->make(Router::class)->getMiddlewareGroups())
        ->toBe([
            'web' => [
                Middleware::class,
            ],
        ]);
});

function usesCustomMiddlewareGroup($app)
{
    $app->config->set('inertia-breadcrumbs.middleware.group', 'sqlite');
}

/**
 * @define-env usesCustomMiddlewareGroup
 */
it('adds middleware to group from configuration', function () {
    expect($this->app->make(Router::class)->getMiddlewareGroups())
        ->toBe([
            'custom' => [
                Middleware::class,
            ],
        ]);
})->skip('Does not work with Pest');
