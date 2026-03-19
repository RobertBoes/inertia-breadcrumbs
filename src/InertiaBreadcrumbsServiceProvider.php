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

    public function packageRegistered(): void
    {
        $this->app->singleton(InertiaBreadcrumbs::class);
        $this->app->singleton(PackageExistenceChecker::class);
        $this->app->bind(BreadcrumbCollectorContract::class, config('inertia-breadcrumbs.collector', DiglacticBreadcrumbsCollector::class));
        $this->app->bind(ClassifierContract::class, config('inertia-breadcrumbs.classifier', AppendAllBreadcrumbs::class));

        $this->clearStateOnOctaneRequest();
    }

    private function clearStateOnOctaneRequest(): void
    {
        if (! class_exists(\Laravel\Octane\Events\RequestReceived::class)) {
            return;
        }

        $this->app['events']->listen(\Laravel\Octane\Events\RequestReceived::class, function (): void {
            $this->app->make(InertiaBreadcrumbs::class)->clearPending();
        });
    }

    public function packageBooted(): void
    {
        if (! config('inertia-breadcrumbs.middleware.enabled', true)) {
            return;
        }

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup(
            group: config('inertia-breadcrumbs.middleware.group', 'web'),
            middleware: Middleware::class,
        );
    }
}
