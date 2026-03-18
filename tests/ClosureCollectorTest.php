<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\ClosureBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\Tests\Helpers\RequestBuilder;

class ClosureCollectorTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app->bind(BreadcrumbCollectorContract::class, ClosureBreadcrumbsCollector::class);
    }

    public function usesCustomMiddlewareGroup($app): void
    {
        $app->config->set('inertia-breadcrumbs.middleware.group', 'custom');
    }

    /**
     * @param  Router  $router
     */
    public function defineRoutes($router): void
    {
        $router->inertia('/profile', 'Profile/Index')->name('profile');
        $router->inertia('/profile/edit', 'Profile/Edit')->name('profile.edit');
        $router->inertia('/dashboard', 'Dashboard')->name('dashboard');
        $router->get('/users/{user}', function (string $user) {
            return Inertia::render('Users/Show', ['user' => $user]);
        })->name('users.show')->middleware('custom');
    }

    #[Test]
    public function it_has_closure_collector_bound()
    {
        $collector = app(BreadcrumbCollectorContract::class);

        $this->assertInstanceOf(ClosureBreadcrumbsCollector::class, $collector);
    }

    #[Test]
    public function it_collects_breadcrumbs_defined_via_closure()
    {
        app(InertiaBreadcrumbs::class)->for('profile.edit', fn () => [
            Breadcrumb::make('Profile', route('profile')),
            Breadcrumb::make('Edit Profile', route('profile.edit')),
        ]);

        $request = RequestBuilder::create('profile.edit');
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame(2, $crumbs->items()->count());
        $this->assertSame([
            [
                'title' => 'Profile',
                'url' => route('profile'),
            ],
            [
                'title' => 'Edit Profile',
                'url' => route('profile.edit'),
                'current' => true,
            ],
        ], $crumbs->toArray());
    }

    #[Test]
    public function it_passes_route_parameters_to_closure()
    {
        app(InertiaBreadcrumbs::class)->for('users.show', fn (string $user) => [
            Breadcrumb::make('Profile', route('profile')),
            Breadcrumb::make("User {$user}", route('users.show', $user)),
        ]);

        $request = RequestBuilder::create('users.show', ['user' => '42']);
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame(2, $crumbs->items()->count());
        $this->assertSame('User 42', $crumbs->items()->last()->title());
    }

    #[Test]
    public function it_returns_empty_collection_when_route_has_no_breadcrumbs()
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
    public function it_overwrites_existing_definitions()
    {
        $breadcrumbs = app(InertiaBreadcrumbs::class);

        $breadcrumbs->for('profile', fn () => [
            Breadcrumb::make('Old', route('profile')),
        ]);

        $breadcrumbs->for('profile', fn () => [
            Breadcrumb::make('New', route('profile')),
        ]);

        $request = RequestBuilder::create('profile');
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame(1, $crumbs->items()->count());
        $this->assertSame('New', $crumbs->items()->first()->title());
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_infers_route_name_from_current_request()
    {
        $this->get('/users/42');

        app(InertiaBreadcrumbs::class)->for(fn (string $user) => [
            Breadcrumb::make("User {$user}", route('users.show', $user)),
        ]);

        $this->assertTrue(app(InertiaBreadcrumbs::class)->has('users.show'));
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_works_through_full_http_pipeline()
    {
        app(InertiaBreadcrumbs::class)->for('users.show', fn (string $user) => [
            Breadcrumb::make("User {$user}", route('users.show', $user)),
        ]);

        $this->getJson('/users/42')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Users/Show')
                    ->has(
                        'breadcrumbs',
                        1,
                        fn (Assert $page) => $page
                            ->where('title', 'User 42')
                            ->where('url', route('users.show', 42))
                            ->where('current', true)
                    )
            );
    }

    #[Test]
    public function it_supports_breadcrumbs_without_url()
    {
        app(InertiaBreadcrumbs::class)->for('profile', fn () => [
            Breadcrumb::make('Profile'),
        ]);

        $request = RequestBuilder::create('profile');
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame([
            [
                'title' => 'Profile',
            ],
        ], $crumbs->toArray());
    }

    #[Test]
    public function it_supports_breadcrumbs_with_data()
    {
        app(InertiaBreadcrumbs::class)->for('profile', fn () => [
            Breadcrumb::make('Profile', route('profile'), ['icon' => 'user']),
        ]);

        $request = RequestBuilder::create('profile');
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame([
            [
                'title' => 'Profile',
                'url' => route('profile'),
                'current' => true,
                'data' => ['icon' => 'user'],
            ],
        ], $crumbs->toArray());
    }

    #[Test]
    public function it_ignores_query_string_by_default()
    {
        app(InertiaBreadcrumbs::class)->for('profile', fn () => [
            Breadcrumb::make('Profile', route('profile')),
        ]);

        $request = RequestBuilder::create('profile', ['foo' => 'bar']);
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame(1, $crumbs->items()->count());
        $this->assertTrue($crumbs->items()->first()->current());
    }

    #[Test]
    public function it_respects_query_string_when_configured()
    {
        Config::set('inertia-breadcrumbs.ignore_query', false);

        app(InertiaBreadcrumbs::class)->for('profile', fn () => [
            Breadcrumb::make('Profile', route('profile')),
        ]);

        $request = RequestBuilder::create('profile', ['foo' => 'bar']);
        $crumbs = app(BreadcrumbCollectorContract::class)->forRequest($request);

        $this->assertSame(1, $crumbs->items()->count());
        $this->assertFalse($crumbs->items()->first()->current());
    }
}
