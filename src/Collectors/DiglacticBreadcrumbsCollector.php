<?php

namespace RobertBoes\InertiaBreadcrumbs\Collectors;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use Diglactic\Breadcrumbs\Exceptions\UnnamedRouteException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;
use stdClass;

class DiglacticBreadcrumbsCollector extends AbstractBreadcrumbCollector
{
    public function forRequest(Request $request): BreadcrumbCollection
    {
        $breadcrumbs = $this->getBreadcrumbs($request);

        return new BreadcrumbCollection($breadcrumbs, function (stdClass $breadcrumb) use ($request): Breadcrumb {
            $data = array_diff_key(get_object_vars($breadcrumb), array_flip(['title', 'url']));

            return new Breadcrumb(
                title: $breadcrumb->title,
                current: $request->fullUrlIs($breadcrumb->url),
                url: $breadcrumb->url,
                data: $data,
            );
        });
    }

    private function getBreadcrumbs(Request $request): Collection
    {
        if (! ($route = $request->route()) instanceof Route) {
            return collect();
        }

        try {
            return Breadcrumbs::generate($route->getName(), ...array_values($route->parameters()));
        } catch (InvalidBreadcrumbException | UnnamedRouteException) {
            return collect();
        }
    }

    public static function requiredClass(): string
    {
        return Breadcrumbs::class;
    }

    public static function packageIdentifier(): string
    {
        return 'diglactic/laravel-breadcrumbs';
    }
}
