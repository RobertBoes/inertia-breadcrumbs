<?php

namespace RobertBoes\InertiaBreadcrumbs\Collectors;

use Illuminate\Http\Request;
use RobertBoes\InertiaBreadcrumbs\Exceptions\PackageNotInstalledException;
use RobertBoes\InertiaBreadcrumbs\PackageExistenceChecker;

abstract class AbstractBreadcrumbCollector implements BreadcrumbCollectorContract
{
    public function __construct(private readonly PackageExistenceChecker $packageChecker)
    {
        if (! ($this->packageChecker)(static::requiredClass())) {
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

    abstract public static function requiredClass(): string;

    abstract public static function packageIdentifier(): string;
}
