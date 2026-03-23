<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbsServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'RobertBoes\\InertiaBreadcrumbs\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        View::addLocation(__DIR__.'/Stubs/views');
    }

    protected function defineEnvironment($app)
    {
        $app->config->set('inertia.testing.ensure_pages_exist', false);
        $app->config->set('database.default', 'testing');

        // Register the 'custom' middleware group used in tests.
        // Laravel 13 requires middleware groups to be registered before routes reference them.
        $app->make(Router::class)->middlewareGroup('custom', []);
    }

    protected function getPackageProviders($app)
    {
        return [
            InertiaServiceProvider::class,
            InertiaBreadcrumbsServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }
}
