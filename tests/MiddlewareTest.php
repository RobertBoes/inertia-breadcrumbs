<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Illuminate\Routing\Router;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\ClosureBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\Middleware;
use RobertBoes\InertiaBreadcrumbs\ShareStrategy;
use RobertBoes\InertiaBreadcrumbs\Tests\Helpers\RequestBuilder;

class MiddlewareTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app->bind(BreadcrumbCollectorContract::class, ClosureBreadcrumbsCollector::class);
    }

    public function usesCustomMiddlewareGroup($app)
    {
        $app->config->set('inertia-breadcrumbs.middleware.group', 'custom');
        $app->make(Router::class)->pushMiddlewareToGroup('custom', Middleware::class);
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
     * @param  Router  $router
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
        $groups = $this->app->make(Router::class)->getMiddlewareGroups();

        $this->assertArrayHasKey('web', $groups);
        $this->assertContains(Middleware::class, $groups['web']);
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_adds_middleware_to_custom_group()
    {
        $groups = $this->app->make(Router::class)->getMiddlewareGroups();

        $this->assertArrayHasKey('custom', $groups);
        $this->assertContains(Middleware::class, $groups['custom']);
    }

    /**
     * @define-env hasMiddlewareDisabled
     */
    #[Test]
    public function it_only_adds_middleware_when_enabled_in_config()
    {
        $groups = $this->app->make(Router::class)->getMiddlewareGroups();

        $webMiddleware = $groups['web'] ?? [];
        $this->assertNotContains(Middleware::class, $webMiddleware);
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_adds_breadcrumbs_for_current_route()
    {
        app(InertiaBreadcrumbs::class)->for('home', fn () => [
            Breadcrumb::make('Home', route('home')),
        ]);

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
    public function it_adds_breadcrumbs_with_additional_data()
    {
        app(InertiaBreadcrumbs::class)->for('home', fn () => [
            Breadcrumb::make('Home', route('home'), ['icon' => 'home.png']),
        ]);

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
        app(InertiaBreadcrumbs::class)->for('home', fn () => [
            Breadcrumb::make('Home', route('home')),
        ]);

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
        app(InertiaBreadcrumbs::class)->for('home', fn () => [
            Breadcrumb::make('Home', route('home')),
        ]);

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
        app(InertiaBreadcrumbs::class)->for('home', fn () => [
            Breadcrumb::make('Home', route('home')),
        ]);

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
        app(InertiaBreadcrumbs::class)->for('home', fn () => [
            Breadcrumb::make('Home', route('home')),
        ]);

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
        if (! method_exists(Assert::class, 'loadDeferredProps')) {
            $this->markTestSkipped('loadDeferredProps is not available in this version of Inertia');
        }

        app(InertiaBreadcrumbs::class)->for('home', fn () => [
            Breadcrumb::make('Home', route('home')),
        ]);

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
