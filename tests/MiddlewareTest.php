<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Illuminate\Routing\Router;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;
use RobertBoes\InertiaBreadcrumbs\Middleware;
use RobertBoes\InertiaBreadcrumbs\ShareStrategy;
use RobertBoes\InertiaBreadcrumbs\Tests\Helpers\RequestBuilder;

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

    public function usesAlwaysShareStrategy($app)
    {
        $app->config->set('inertia-breadcrumbs.share', ShareStrategy::Always);
    }

    public function usesDeferredShareStrategy($app)
    {
        $app->config->set('inertia-breadcrumbs.share', ShareStrategy::Deferred);
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
        $this->assertEmpty($this->app->make(Router::class)->getMiddlewareGroups());
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
    public function it_shares_null_when_route_has_no_breadcrumbs()
    {
        $this->getJson('/home')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->where('breadcrumbs', null)
            );
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_adds_breadcrumbs_with_cached_routes()
    {
        Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
            $trail->push('Home', route('home'));
        });

        // Simulate route caching by compiling and reloading routes
        $compiled = $this->app['router']->getRoutes()->compile();
        $this->app['router']->setCompiledRoutes($compiled);

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
    public function it_collects_breadcrumbs_with_cached_routes_via_collector()
    {
        Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
            $trail->push('Home', route('home'));
        });

        // Simulate route caching
        $compiled = $this->app['router']->getRoutes()->compile();
        $this->app['router']->setCompiledRoutes($compiled);

        // Verify the route is still resolvable via the compiled collection
        $route = $this->app['router']->getRoutes()->getByName('home');
        $this->assertNotNull($route, 'Route should be resolvable from compiled routes');
        $this->assertSame('home', $route->getName(), 'Route name should be preserved in compiled routes');

        $request = RequestBuilder::create('home');
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame(1, $crumbs->items()->count());
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     * @define-env usesAlwaysShareStrategy
     */
    #[Test]
    public function it_shares_breadcrumbs_with_always_strategy()
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
     * @define-env usesDeferredShareStrategy
     */
    #[Test]
    public function it_shares_breadcrumbs_with_deferred_strategy()
    {
        Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
            $trail->push('Home', route('home'));
        });

        $this->getJson('/home')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->missing('breadcrumbs')
                    ->loadDeferredProps(
                        fn (Assert $page) => $page
                            ->has(
                                'breadcrumbs',
                                1,
                                fn (Assert $page) => $page
                                    ->where('title', 'Home')
                                    ->where('url', route('home'))
                                    ->where('current', true)
                            )
                    )
            );
    }
}
