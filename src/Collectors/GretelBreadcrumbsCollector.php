<?php

namespace RobertBoes\InertiaBreadcrumbs\Collectors;

use Glhd\Gretel\Macros;
use Glhd\Gretel\Registry;
use Glhd\Gretel\View\Breadcrumb as GretelBreadcrumb;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;

class GretelBreadcrumbsCollector extends AbstractBreadcrumbCollector
{
    public function forRequest(Request $request): BreadcrumbCollection
    {
        $breadcrumbs = $this->getBreadcrumbs($request);

        return new BreadcrumbCollection($breadcrumbs, function (GretelBreadcrumb $breadcrumb): Breadcrumb {
            return new Breadcrumb(
                title: $breadcrumb->title,
                current: $breadcrumb->is_current_page,
                url: $breadcrumb->url,
            );
        });
    }

    private function getBreadcrumbs(Request $request): Collection
    {
        if (! ($route = $request->route()) instanceof Route) {
            return collect();
        }

        $registry = app(Registry::class);

        return Macros::breadcrumbs($registry, $route)->toCollection();
    }

    public static function requiredClass(): string
    {
        return Registry::class;
    }

    public static function packageIdentifier(): string
    {
        return 'glhd/gretel';
    }
}
