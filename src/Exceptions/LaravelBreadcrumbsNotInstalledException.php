<?php

namespace RobertBoes\InertiaBreadcrumbs\Exceptions;

use Exception;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use RobertBoes\InertiaBreadcrumbs\Solutions\InstallLaravelBreadcrumbs;

class LaravelBreadcrumbsNotInstalledException extends Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return new InstallLaravelBreadcrumbs;
    }
}
