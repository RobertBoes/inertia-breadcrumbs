<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\TabunaBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Exceptions\PackageNotInstalledException;
use RobertBoes\InertiaBreadcrumbs\PackageExistenceChecker;
use RobertBoes\InertiaBreadcrumbs\Tests\Concerns\SetupCollector;
use RobertBoes\InertiaBreadcrumbs\Tests\Helpers\RequestBuilder;
use Tabuna\Breadcrumbs\Breadcrumbs as TabunaBreadcrumbs;
use Tabuna\Breadcrumbs\BreadcrumbsServiceProvider;
use Tabuna\Breadcrumbs\Trail as TabunaTrail;

class TabunaCollectorTest extends TestCase
{
    use SetupCollector;

    protected function setUp(): void
    {
        $this->skipIfCollectorPackageMissing();

        // Clear Gretel's 'breadcrumbs' macro if it leaked from a previous test class,
        // so Tabuna's service provider can register its own version.
        Route::flushMacros();

        parent::setUp();
    }

    protected function provider(): string
    {
        return BreadcrumbsServiceProvider::class;
    }

    protected function collector(): string
    {
        return TabunaBreadcrumbsCollector::class;
    }

    public function usesCustomMiddlewareGroup($app)
    {
        $app->config->set('inertia-breadcrumbs.middleware.group', 'custom');
        $app->make(\Illuminate\Routing\Router::class)->pushMiddlewareToGroup('custom', \RobertBoes\InertiaBreadcrumbs\Middleware::class);
    }

    /**
     * @param  Router  $router
     */
    public function defineRoutes($router)
    {
        $router->inertia('/profile', 'Profile/Index')->name('profile');
        $router->inertia('/profile/edit', 'Profile/Edit')->name('profile.edit');
        $router->inertia('/dashboard', 'Dashboard')->name('dashboard');

        $router->get('/macro-test', function () {
            return inertia('MacroTest', []);
        })->name('macro.test')->middleware('custom')->breadcrumbs(fn (TabunaTrail $trail) => $trail->push('Macro Test', route('macro.test')));

        $router->get('/{name}', function (string $name) {
            return inertia('Name', [
                'name' => $name,
            ]);
        })->name('reserved-keyword-route');
    }

    #[Test]
    public function it_has_tabuna_collector_bound()
    {
        $collector = app(BreadcrumbCollectorContract::class);

        $this->assertInstanceOf(TabunaBreadcrumbsCollector::class, $collector);
    }

    #[Test]
    public function it_throws_an_exception_when_package_is_not_installed()
    {
        $this->app->instance(PackageExistenceChecker::class, new class extends PackageExistenceChecker
        {
            public function __invoke(string $class): bool
            {
                return false;
            }
        });
        $this->expectException(PackageNotInstalledException::class);
        $this->expectExceptionMessage('tabuna/breadcrumbs is not installed');

        app(BreadcrumbCollectorContract::class);
    }

    #[Test]
    public function it_collects_tabuna_breadcrumbs()
    {
        TabunaBreadcrumbs::for('profile', function (TabunaTrail $trail) {
            $trail->push('Profile', route('profile'));
        });

        TabunaBreadcrumbs::for('profile.edit', function (TabunaTrail $trail) {
            $trail->parent('profile');
            $trail->push('Edit profile', route('profile.edit'));
            $trail->push('Crumb without link');
        });

        $request = RequestBuilder::create('profile.edit');
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame(3, $crumbs->items()->count());
        $this->assertSame([
            [
                'title' => 'Profile',
                'url' => route('profile'),
            ],
            [
                'title' => 'Edit profile',
                'url' => route('profile.edit'),
                'current' => true,
            ],
            [
                'title' => 'Crumb without link',
            ],
        ], $crumbs->toArray());
    }

    #[Test]
    public function it_can_use_a_reserved_keyword()
    {
        TabunaBreadcrumbs::for('reserved-keyword-route', function (TabunaTrail $trail) {
            $trail->push('Reserved', route('reserved-keyword-route', ['name' => 'robert']));
        });

        $request = RequestBuilder::create('reserved-keyword-route', ['name' => 'robert']);
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame(1, $crumbs->items()->count());
        $this->assertSame([
            [
                'title' => 'Reserved',
                'url' => route('reserved-keyword-route', ['name' => 'robert']),
                'current' => true,
            ],
        ], $crumbs->toArray());
    }

    #[Test]
    public function it_returns_an_empty_collection_when_route_has_no_breadcrumbs()
    {
        $request = RequestBuilder::create('dashboard');
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertTrue($crumbs->items()->isEmpty());
    }

    #[Test]
    public function it_returns_empty_collection_for_404_page()
    {
        $request = RequestBuilder::notFound('foo');
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertTrue($crumbs->items()->isEmpty());
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_resolves_breadcrumbs_defined_via_route_macro()
    {
        $this->getJson('/macro-test')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('MacroTest')
                    ->has(
                        'breadcrumbs',
                        1,
                        fn (Assert $page) => $page
                            ->where('title', 'Macro Test')
                            ->where('url', route('macro.test'))
                            ->where('current', true)
                    )
            );
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_ignores_the_query_string_by_default_when_determining_current_route()
    {
        TabunaBreadcrumbs::for('profile.edit', function (TabunaTrail $trail) {
            $trail->push('Profile', route('profile'));
            $trail->push('Edit profile', route('profile.edit'));
        });

        $request = RequestBuilder::create('profile.edit', ['foo' => 'bar']);
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame(2, $crumbs->items()->count());
        $this->assertSame([
            [
                'title' => 'Profile',
                'url' => route('profile'),
            ],
            [
                'title' => 'Edit profile',
                'url' => route('profile.edit'),
                'current' => true,
            ],
        ], $crumbs->toArray());
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_does_not_ignore_query_parameters_when_configured_to_do_so_when_determining_current_route()
    {
        Config::set('inertia-breadcrumbs.ignore_query', false);

        TabunaBreadcrumbs::for('profile.edit', function (TabunaTrail $trail) {
            $trail->push('Profile', route('profile'));
            $trail->push('Edit profile', route('profile.edit'));
        });

        $request = RequestBuilder::create('profile.edit', ['foo' => 'bar']);
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame(2, $crumbs->items()->count());
        $this->assertSame([
            [
                'title' => 'Profile',
                'url' => route('profile'),
            ],
            [
                'title' => 'Edit profile',
                'url' => route('profile.edit'),
            ],
        ], $crumbs->toArray());
    }
}
