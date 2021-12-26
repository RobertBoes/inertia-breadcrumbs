<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RobertBoes\InertiaBreadcrumbs\Exceptions\LaravelBreadcrumbsNotInstalledException;

class LaravelBreadcrumbsCollector implements BreadcrumbCollectorContract
{
    public function __construct()
    {
        if (! $this->laravelBreadcrumbsExist()) {
            throw new LaravelBreadcrumbsNotInstalledException();
        }
    }

    public function forRequest(Request $request): BreadcrumbCollection
    {
        $breadcrumbs = $this->getBreadcrumbs();

        return BreadcrumbCollection::make($breadcrumbs);
    }

    private function laravelBreadcrumbsExist(): bool
    {
        return class_exists(Breadcrumbs::class);
    }

    private function getBreadcrumbs(): Collection
    {
        try {
            return Breadcrumbs::generate();
        } catch (InvalidBreadcrumbException $e) {
            return collect();
        }
    }
}
