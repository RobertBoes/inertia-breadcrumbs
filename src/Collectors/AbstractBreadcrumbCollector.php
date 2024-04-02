<?php

namespace RobertBoes\InertiaBreadcrumbs\Collectors;

use Illuminate\Http\Request;
use RobertBoes\InertiaBreadcrumbs\Exceptions\PackageNotInstalledException;

abstract class AbstractBreadcrumbCollector implements BreadcrumbCollectorContract
{
    public function __construct()
    {
        if (! $this->canUseImplementation()) {
            throw new PackageNotInstalledException(static::packageIdentifier());
        }
    }

    protected function isCurrentUrl(Request $request, ?string $url): bool
    {
        if (is_null($url)) {
            return false;
        }

        if (config('inertia-breadcrumbs.ignore_query', true)) {
            return $request->url() === $url;
        }

        return $request->fullUrlIs($url);
    }

    private function canUseImplementation(): bool
    {
        return app('inertia-breadcrumbs-package-existence')(static::requiredClass());
    }

    abstract public static function requiredClass(): string;

    abstract public static function packageIdentifier(): string;
}
