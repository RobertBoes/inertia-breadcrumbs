<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Illuminate\Routing\Router;
use RobertBoes\InertiaBreadcrumbs\Classifier\AppendAllBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\Classifier\ClassifierContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\DiglacticBreadcrumbsCollector;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InertiaBreadcrumbsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('inertia-breadcrumbs')
            ->hasConfigFile();
    }

    public function packageRegistered()
    {
        $this->app->bind(BreadcrumbCollectorContract::class, config('inertia-breadcrumbs.collector', DiglacticBreadcrumbsCollector::class));
        $this->app->bind(ClassifierContract::class, config('inertia-breadcrumbs.classifier', AppendAllBreadcrumbs::class));
        $this->app->instance('inertia-breadcrumbs-package-existence', function (string $class): bool {
            return class_exists($class);
        });
    }

    public function packageBooted()
    {
        if (! config('inertia-breadcrumbs.middleware.enabled', true)) {
            return;
        }

        /** @var Router */
        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup(config('inertia-breadcrumbs.middleware.group', 'web'), Middleware::class);
    }
}
