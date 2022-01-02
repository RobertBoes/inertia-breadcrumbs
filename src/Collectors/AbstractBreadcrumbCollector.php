<?php

namespace RobertBoes\InertiaBreadcrumbs\Collectors;

abstract class AbstractBreadcrumbCollector implements BreadcrumbCollectorContract
{
    public function __construct()
    {
        if (! $this->canUseImplementation()) {
            throw new (static::notInstalledException());
        }
    }

    private function canUseImplementation(): bool
    {
        return class_exists(static::requiredClass());
    }

    abstract public static function requiredClass(): string;

    abstract public static function notInstalledException(): string;
}
