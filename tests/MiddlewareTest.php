<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Illuminate\Routing\Router;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use RobertBoes\InertiaBreadcrumbs\Middleware;

class MiddlewareTest extends TestCase
{
    public function usesCustomMiddlewareGroup($app)
    {
        $app->config->set('inertia-breadcrumbs.middleware.group', 'custom');
    }

    public function usesCustomSharedKey($app)
    {
        $app->config->set('inertia-breadcrumbs.middleware.key', '_breadcrumbs');
    }

    public function hasMiddlewareDisabled($app)
    {
        $app->config->set('inertia-breadcrumbs.middleware.enabled', false);
    }

    /**
     * @param  \Illuminate\Routing\Router  $router
     */
    public function defineRoutes($router)
    {
        $router->get('/home', function () {
            return Inertia::render('Home', []);
        })->name('home')->middleware('custom');
    }

    #[Test]
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
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
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
     * @define-env hasMiddlewareDisabled
     */
    #[Test]
    public function it_only_adds_middleware_when_enabled_in_config()
    {
        $this->assertEmpty($this->app->make(Router::class)->getMiddleware());
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_adds_breadcrumbs_for_current_route()
    {
        Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
            $trail->push('Home', route('home'));
        });

        $this->getJson('/home')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'breadcrumbs',
                        1,
                        fn (Assert $page) => $page
                            ->where('title', 'Home')
                            ->where('url', route('home'))
                            ->where('current', true)
                    )
            );
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_adds_diglactic_breadcrumbs_with_additional_data()
    {
        Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
            $trail->push('Home', route('home'), ['icon' => 'home.png']);
        });

        $this->getJson('/home')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'breadcrumbs',
                        1,
                        fn (Assert $page) => $page
                            ->where('title', 'Home')
                            ->where('url', route('home'))
                            ->where('data.icon', 'home.png')
                            ->where('current', true)
                    )
            );
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     * @define-env usesCustomSharedKey
     */
    #[Test]
    public function it_does_change_key_of_breadcrumb()
    {
        Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
            $trail->push('Home', route('home'));
        });

        $this->getJson('/home')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        '_breadcrumbs',
                        1,
                        fn (Assert $page) => $page
                            ->where('title', 'Home')
                            ->where('url', route('home'))
                            ->where('current', true)
                    )
                    ->missing('breadcrumbs')
            );
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_does_not_add_breadcrumbs_when_route_has_no_breadcrumbs()
    {
        $this->getJson('/home')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->missing('breadcrumbs')
            );
    }
}
