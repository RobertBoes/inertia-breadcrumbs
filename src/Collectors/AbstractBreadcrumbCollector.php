<?php

namespace RobertBoes\InertiaBreadcrumbs\Collectors;

use RobertBoes\InertiaBreadcrumbs\Exceptions\PackageNotInstalledException;

abstract class AbstractBreadcrumbCollector implements BreadcrumbCollectorContract
{
    public function __construct()
    {
        if (! $this->canUseImplementation()) {
            throw new PackageNotInstalledException(static::packageIdentifier());
        }
    }

    private function canUseImplementation(): bool
    {
        return app('inertia-breadcrumbs-package-existence')(static::requiredClass());
    }

    abstract public static function requiredClass(): string;

    abstract public static function packageIdentifier(): string;
}
