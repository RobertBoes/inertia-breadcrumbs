<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Inertia\Inertia;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Inertia\Testing\AssertableInertia as Assert;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\Middleware;

class SerializationTest extends TestCase
{
    /**
     * @param \Illuminate\Routing\Router $router
     */
    public function defineRoutes($router): void
    {
        $router->get('/home', function () {
            return Inertia::render('Home', []);
        })->name('home')->middleware([Middleware::class]);
    }

    /**
     * @test
     */
    public function it_serializes_breadcrumbs(): void
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
                            ->missing('data')
                    )
            );
    }

    /**
     * @test
     */
    public function it_can_use_a_custom_serializer(): void
    {
        Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
            $trail->push('Home', route('home'));
        });

        InertiaBreadcrumbs::serializeUsing(fn (Breadcrumb $breadcrumb) => [
            'name' => $breadcrumb->title(),
            'href' => $breadcrumb->url(),
            'active' => $breadcrumb->current(),
            'data' => $breadcrumb->data(),
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
                            ->where('name', 'Home')
                            ->where('href', route('home'))
                            ->where('active', true)
                            ->where('data', [])
                    )
            );
    }
}
