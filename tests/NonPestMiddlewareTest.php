<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Illuminate\Routing\Router;
use Inertia\Inertia;
use Inertia\Testing\Assert;
use RobertBoes\InertiaBreadcrumbs\Middleware;

class NonPestMiddlewareTest extends TestCase
{
    public function usesCustomMiddlewareGroup($app)
    {
        $app->config->set('inertia-breadcrumbs.middleware.group', 'custom');
    }

    public function hasMiddlewareDisabled($app)
    {
        $app->config->set('inertia-breadcrumbs.middleware.enabled', false);
    }

    /**
     * @param \Illuminate\Routing\Router $router
     */
    public function defineRoutes($router)
    {
        $router->get('/home', function () {
            return Inertia::render('Home', []);
        })->name('home')->middleware('custom');
    }

    /**
     * @test
     */
    public function it_adds_middleware_to_web_group()
    {
        $this->assertEquals(
            $this->app->make(Router::class)->getMiddlewareGroups(),
            [
                'web' => [
                    Middleware::class,
                ],
            ]
        );
    }

    /**
     * @test
     * @define-env usesCustomMiddlewareGroup
     */
    public function it_adds_middleware_to_custom_group()
    {
        $this->assertEquals(
            $this->app->make(Router::class)->getMiddlewareGroups(),
            [
                'custom' => [
                    Middleware::class,
                ],
            ]
        );
    }

    /**
     * @test
     * @define-env hasMiddlewareDisabled
     */
    public function it_only_adds_middleware_when_enabled_in_config()
    {
        $this->assertEmpty($this->app->make(Router::class)->getMiddleware());
    }

    /**
     * @test
     * @define-env usesCustomMiddlewareGroup
     */
    public function it_adds_breadcrumbs_for_current_route()
    {
        Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
            $trail->push('Home', route('home'));
        });

        $this->getJson('/home')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has('breadcrumbs', 1, fn (Assert $page) => $page
                        ->where('title', 'Home')
                        ->where('url', route('home'))
                    )
            );
    }
}
