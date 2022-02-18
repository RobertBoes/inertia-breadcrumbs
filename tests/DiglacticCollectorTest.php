<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Diglactic\Breadcrumbs\Breadcrumbs as DiglacticBreadcrumbs;
use Diglactic\Breadcrumbs\Generator as DiglacticTrail;
use Diglactic\Breadcrumbs\ServiceProvider;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\DiglacticBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Exceptions\PackageNotInstalledException;
use RobertBoes\InertiaBreadcrumbs\Tests\Concerns\SetupCollector;
use RobertBoes\InertiaBreadcrumbs\Tests\Helpers\RequestBuilder;

class DiglacticCollectorTest extends TestCase
{
    use SetupCollector;

    protected function collector(): string
    {
        return DiglacticBreadcrumbsCollector::class;
    }

    protected function provider(): string
    {
        return ServiceProvider::class;
    }

    /**
     * @param \Illuminate\Routing\Router $router
     */
    public function defineRoutes($router)
    {
        $router->inertia('/profile', 'Profile/Index')->name('profile');
        $router->inertia('/profile/edit', 'Profile/Edit')->name('profile.edit');
        $router->inertia('/dashboard', 'Dashboard')->name('dashboard');
    }

    /**
     * @test
     */
    public function it_has_diglactic_collector_bound()
    {
        $collector = app(BreadcrumbCollectorContract::class);

        $this->assertInstanceOf(DiglacticBreadcrumbsCollector::class, $collector);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_package_is_not_installed()
    {
        $this->app->instance('inertia-breadcrumbs-package-existence', function (string $class): bool {
            return false;
        });
        $this->expectException(PackageNotInstalledException::class);
        $this->expectExceptionMessage('diglactic/laravel-breadcrumbs is not installed');

        app(BreadcrumbCollectorContract::class);
    }

    /**
     * @test
     *
     */
    public function it_collects_diglactic_breadcrumbs()
    {
        DiglacticBreadcrumbs::for('profile.edit', function (DiglacticTrail $trail) {
            $trail->push('Profile', route('profile'));
            $trail->push('Edit profile', route('profile.edit'));
        });

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

    /**
     * @test
     */
    public function it_returns_an_empty_collection_when_route_has_no_breadcrumbs()
    {
        $request = RequestBuilder::create('dashboard');
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertTrue($crumbs->items()->isEmpty());
    }

    /**
     * @test
     */
    public function it_returns_empty_collection_for_404_page()
    {
        $request = RequestBuilder::notFound('foo');
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertTrue($crumbs->items()->isEmpty());
    }
}
