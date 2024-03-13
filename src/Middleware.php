<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RobertBoes\InertiaBreadcrumbs\Classifier\ClassifierContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;

class Middleware
{
    private BreadcrumbCollectorContract $collector;

    private ClassifierContract $classifier;

    public function __construct(BreadcrumbCollectorContract $collector, ClassifierContract $classifier)
    {
        $this->collector = $collector;
        $this->classifier = $classifier;
    }

    public function handle(Request $request, Closure $next)
    {
        $breadcrumbs = $this->breadcrumbs($request);

        if (is_null($breadcrumbs)) {
            return $next($request);
        }

        Inertia::share(
            key: config('inertia-breadcrumbs.middleware.key', 'breadcrumbs'),
            value: $breadcrumbs,
        );

        return $next($request);
    }

    private function breadcrumbs(Request $request): ?BreadcrumbCollection
    {
        $breadcrumbs = $this->collector->forRequest($request);

        if ($breadcrumbs->items()->isEmpty()) {
            return null;
        }

        if (! $this->classifier->shouldShareBreadcrumbs($breadcrumbs)) {
            return null;
        }

        return $breadcrumbs;
    }
}
