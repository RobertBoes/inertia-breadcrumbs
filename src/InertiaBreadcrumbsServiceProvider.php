<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Illuminate\Routing\Router;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InertiaBreadcrumbsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('inertia-breadcrumbs')
            ->hasConfigFile();
    }

    public function packageRegistered()
    {
        $this->app->bind(BreadcrumbCollectorContract::class, config('inertia-breadcrumbs.collector', LaravelBreadcrumbsCollector::class));
    }

    public function packageBooted()
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        if (! config('inertia-breadcrumbs.middleware.enabled', true)) {
            return;
        }

        /** @var Router */
        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup(config('inertia-breadcrumbs.middleware.group', 'web'), Middleware::class);
    }
}
