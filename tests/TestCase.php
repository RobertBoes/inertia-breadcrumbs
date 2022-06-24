<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
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
    }

    protected function getPackageProviders($app)
    {
        return [
            InertiaServiceProvider::class,
            InertiaBreadcrumbsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_inertia-breadcrumbs_table.php.stub';
        $migration->up();
        */
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }
}
