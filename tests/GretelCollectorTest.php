<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Glhd\Gretel\Support\GretelServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\GretelBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Exceptions\PackageNotInstalledException;
use RobertBoes\InertiaBreadcrumbs\PackageExistenceChecker;
use RobertBoes\InertiaBreadcrumbs\Tests\Concerns\SetupCollector;
use RobertBoes\InertiaBreadcrumbs\Tests\Helpers\RequestBuilder;

class GretelCollectorTest extends TestCase
{
    use SetupCollector;

    protected function collector(): string
    {
        return GretelBreadcrumbsCollector::class;
    }

    protected function provider(): string
    {
        return GretelServiceProvider::class;
    }

    public function usesCustomMiddlewareGroup($app)
    {
        $app->config->set('inertia-breadcrumbs.middleware.group', 'custom');
        $app->make(Router::class)->pushMiddlewareToGroup('custom', \RobertBoes\InertiaBreadcrumbs\Middleware::class);
    }

    /**
     * @param  Router  $router
     */
    public function defineRoutes($router)
    {
        $router->inertia('/profile', 'Profile/Index')->name('profile')->breadcrumb('Profile');
        $router->inertia('/profile/edit', 'Profile/Edit')->name('profile.edit')->breadcrumb('Edit profile', 'profile');
        $router->inertia('/dashboard', 'Dashboard')->name('dashboard');
    }

    #[Test]
    public function it_has_gretel_collector_bound()
    {
        $collector = app(BreadcrumbCollectorContract::class);

        $this->assertInstanceOf(GretelBreadcrumbsCollector::class, $collector);
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
        $this->expectExceptionMessage('glhd/gretel is not installed');

        app(BreadcrumbCollectorContract::class);
    }

    #[Test]
    public function it_collects_gretel_breadcrumbs()
    {
        $request = RequestBuilder::create('profile.edit');
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

    #[Test]
    #[DefineEnvironment('usesCustomMiddlewareGroup')]
    public function it_ignores_the_query_string_by_default_when_determining_current_route()
    {
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

    #[Test]
    #[DefineEnvironment('usesCustomMiddlewareGroup')]
    public function it_does_not_ignore_query_parameters_when_configured_to_do_so_when_determining_current_route()
    {
        Config::set('inertia-breadcrumbs.ignore_query', false);

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
