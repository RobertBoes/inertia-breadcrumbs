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

        $name = $route->getName();

        if ($name === null || ! $this->breadcrumbs->has($name)) {
            return new BreadcrumbCollection([]);
        }

        $callback = $this->breadcrumbs->get($name);

        /** @var array<int, Breadcrumb> $items */
        $items = $callback(...array_values($route->parameters()));

        $items = collect($items)->map(fn (Breadcrumb $breadcrumb) => new Breadcrumb(
            title: $breadcrumb->title(),
            current: $breadcrumb->current() ?: $this->isCurrentUrl($request, $breadcrumb->url()),
            url: $breadcrumb->url(),
            data: $breadcrumb->data(),
        ));

        return new BreadcrumbCollection($items);
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
