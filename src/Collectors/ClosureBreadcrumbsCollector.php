<?php

namespace RobertBoes\InertiaBreadcrumbs\Collectors;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;

class ClosureBreadcrumbsCollector implements BreadcrumbCollectorContract
{
    public function __construct(private readonly InertiaBreadcrumbs $breadcrumbs) {}

    public function forRequest(Request $request): BreadcrumbCollection
    {
        if (! ($route = $request->route()) instanceof Route) {
            return new BreadcrumbCollection([]);
        }

        $callback = $this->resolveCallback($route);

        if ($callback === null) {
            return new BreadcrumbCollection([]);
        }

        /** @var array<int, Breadcrumb> $items */
        $items = $callback(...array_values($route->parameters()));

        return new BreadcrumbCollection($items, fn (Breadcrumb $breadcrumb) => new Breadcrumb(
            title: $breadcrumb->title(),
            current: $breadcrumb->current() ?: $this->isCurrentUrl($request, $breadcrumb->url()),
            url: $breadcrumb->url(),
            data: $breadcrumb->data(),
        ));
    }

    private function resolveCallback(Route $route): ?\Closure
    {
        $name = $route->getName();

        if ($name !== null && $this->breadcrumbs->has($name)) {
            return $this->breadcrumbs->get($name);
        }

        return $this->breadcrumbs->pending();
    }

    private function isCurrentUrl(Request $request, ?string $url): bool
    {
        if (is_null($url)) {
            return false;
        }

        if (config('inertia-breadcrumbs.ignore_query', true)) {
            return $request->url() === $url;
        }

        return $request->fullUrlIs($url);
    }
}
