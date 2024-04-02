<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Diglactic\Breadcrumbs\Breadcrumbs as DiglacticBreadcrumbs;
use Diglactic\Breadcrumbs\Generator as DiglacticTrail;
use Diglactic\Breadcrumbs\ServiceProvider;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\DiglacticBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Exceptions\PackageNotInstalledException;
use RobertBoes\InertiaBreadcrumbs\Tests\Concerns\SetupCollector;
use RobertBoes\InertiaBreadcrumbs\Tests\Helpers\RequestBuilder;
use RobertBoes\InertiaBreadcrumbs\Tests\Stubs\Models\User;

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

    public function usesCustomMiddlewareGroup($app)
    {
        $app->config->set('inertia-breadcrumbs.middleware.group', 'custom');
    }

    /**
     * @param  \Illuminate\Routing\Router  $router
     */
    public function defineRoutes($router)
    {
        $router->inertia('/profile', 'Profile/Index')->name('profile');
        $router->inertia('/profile/edit', 'Profile/Edit')->name('profile.edit');
        $router->inertia('/dashboard', 'Dashboard')->name('dashboard');
        $router->inertia('/users', 'Users/Index')->name('users.index');
        $router->get('/users/{user}', function (User $user) {
            return inertia('Users/Show', [
                'user_name' => $user->name,
            ]);
        })->name('users.show')->middleware(SubstituteBindings::class, 'custom');
        $router->get('/users/{person}/{user}', function (User $user) {
            return inertia('Users/Show', [
                'user_name' => $user->name,
            ]);
        })->name('users.show.person')->middleware(SubstituteBindings::class, 'custom');
        $router->get('/{name}', function (string $name) {
            return inertia('Name', [
                'name' => $name,
            ]);
        })->name('reserved-keyword-route');
    }

    #[Test]
    public function it_has_diglactic_collector_bound()
    {
        $collector = app(BreadcrumbCollectorContract::class);

        $this->assertInstanceOf(DiglacticBreadcrumbsCollector::class, $collector);
    }

    #[Test]
    public function it_throws_an_exception_when_package_is_not_installed()
    {
        $this->app->instance('inertia-breadcrumbs-package-existence', function (string $class): bool {
            return false;
        });
        $this->expectException(PackageNotInstalledException::class);
        $this->expectExceptionMessage('diglactic/laravel-breadcrumbs is not installed');

        app(BreadcrumbCollectorContract::class);
    }

    #[Test]
    public function it_collects_diglactic_breadcrumbs()
    {
        DiglacticBreadcrumbs::for('profile', function (DiglacticTrail $trail) {
            $trail->push('Profile', route('profile'));
        });

        DiglacticBreadcrumbs::for('profile.edit', function (DiglacticTrail $trail) {
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
        DiglacticBreadcrumbs::for('reserved-keyword-route', function (DiglacticTrail $trail) {
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

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_resolves_a_single_route_parameter()
    {
        $user = User::factory()->create();
        DiglacticBreadcrumbs::for('users.show', function (DiglacticTrail $trail, User $user) {
            $trail->push('Users', route('users.index'));
            $trail->push($user->name, route('users.show', ['user' => $user]));
        });

        $this->getJson(route('users.show', ['user' => $user]))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Users/Show')
                    ->has(
                        'breadcrumbs',
                        2
                    )
            );
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
    public function it_ignores_the_query_string_by_default_when_determining_current_route()
    {
        $user = User::factory()->create();
        DiglacticBreadcrumbs::for('users.show', function (DiglacticTrail $trail, User $user) {
            $trail->push($user->name, route('users.show', ['user' => $user]));
        });

        $this->getJson(route('users.show', ['user' => $user, 'foo' => 'bar']))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Users/Show')
                    ->has(
                        'breadcrumbs',
                        1,
                        fn (Assert $prop) => $prop
                            ->where('current', true)
                            ->etc()
                    )
            );
    }

    /**
     * @define-env usesCustomMiddlewareGroup
     */
    #[Test]
    public function it_does_not_ignore_query_parameters_when_configured_to_do_so_when_determining_current_route()
    {
        Config::set('inertia-breadcrumbs.ignore_query', false);

        $user = User::factory()->create();
        DiglacticBreadcrumbs::for('users.show', function (DiglacticTrail $trail, User $user) {
            $trail->push($user->name, route('users.show', ['user' => $user]));
        });

        $this->getJson(route('users.show', ['user' => $user, 'foo' => 'bar']))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Users/Show')
                    ->has(
                        'breadcrumbs',
                        1,
                        fn (Assert $prop) => $prop
                            ->missing('current')
                            ->etc()
                    )
            );
    }
}
