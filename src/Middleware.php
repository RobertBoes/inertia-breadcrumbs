<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RobertBoes\InertiaBreadcrumbs\Classifier\ClassifierContract;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;

class Middleware
{
    public function __construct(
        private readonly BreadcrumbCollectorContract $collector,
        private readonly ClassifierContract $classifier
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $resolver = fn () => $this->breadcrumbs($request)?->toArray();

        $configured = config('inertia-breadcrumbs.share');

        $strategy = match (true) {
            $configured instanceof ShareStrategy => $configured,
            is_string($configured) => ShareStrategy::tryFrom($configured) ?? ShareStrategy::Default,
            default => ShareStrategy::Default,
        };

        $value = match ($strategy) {
            ShareStrategy::Always => Inertia::always($resolver),
            ShareStrategy::Deferred => Inertia::defer($resolver),
            ShareStrategy::Default => $resolver,
        };

        Inertia::share(
            key: config('inertia-breadcrumbs.middleware.key', 'breadcrumbs'),
            value: $value,
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
