<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Diglactic\Breadcrumbs\Breadcrumbs as DiglacticBreadcrumbs;
use Diglactic\Breadcrumbs\Generator as DiglacticTrail;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\DiglacticBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\TabunaBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Tests\Helpers\RequestBuilder;
use RobertBoes\InertiaBreadcrumbs\Tests\Stubs\Classes\DummyException;
use RobertBoes\InertiaBreadcrumbs\Tests\Stubs\Classes\InvalidDummyCollector;
use Tabuna\Breadcrumbs\Breadcrumbs as TabunaBreadcrumbs;
use Tabuna\Breadcrumbs\Trail as TabunaTrail;

class CollectorTest extends TestCase
{
    /**
     * @param \Illuminate\Routing\Router $router
     */
    public function defineRoutes($router)
    {
        $router->inertia('/profile', 'Profile/Index')->name('profile')->middleware('custom');
        $router->inertia('/profile/edit', 'Profile/Edit')->name('profile.edit')->middleware('custom');
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_required_class_does_not_exist()
    {
        $this->expectException(DummyException::class);

        new InvalidDummyCollector();
    }

    /**
     * @test
     *
     */
    public function it_collects_diglactic_breadcrumbs()
    {
        $this->app->bind(BreadcrumbCollectorContract::class, DiglacticBreadcrumbsCollector::class);
        DiglacticBreadcrumbs::for('profile.edit', function (DiglacticTrail $trail) {
            $trail->push('Profile', route('profile'));
            $trail->push('Edit profile', route('profile.edit'));
        });

        $request = RequestBuilder::create('profile.edit');

        /** @var BreadcrumbCollectorContract */
        $collector = app(BreadcrumbCollectorContract::class);

        $this->assertInstanceOf(DiglacticBreadcrumbsCollector::class, $collector);

        $crumbs = $collector->forRequest($request);

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
    public function it_collects_tabuna_breadcrumbs()
    {
        $this->app->bind(BreadcrumbCollectorContract::class, TabunaBreadcrumbsCollector::class);
        TabunaBreadcrumbs::for('profile.edit', function (TabunaTrail $trail) {
            $trail->push('Profile', route('profile'));
            $trail->push('Edit profile', route('profile.edit'));
        });

        $request = RequestBuilder::create('profile.edit');

        $collector = app(BreadcrumbCollectorContract::class);

        $this->assertInstanceOf(TabunaBreadcrumbsCollector::class, $collector);

        $crumbs = $collector->forRequest($request);
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
}
