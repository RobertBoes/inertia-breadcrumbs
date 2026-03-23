<?php

namespace RobertBoes\InertiaBreadcrumbs;

class PackageExistenceChecker
{
    public function __invoke(string $class): bool
    {
        return class_exists($class);
    }
}
