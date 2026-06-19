<?php

namespace RobertBoes\InertiaBreadcrumbs\Exceptions;

use Exception;
use RobertBoes\InertiaBreadcrumbs\Collectors\ClosureBreadcrumbsCollector;

class PackageNotInstalledException extends Exception
{
    public function __construct(string $packageIdentifier, string $collectorClass)
    {
        parent::__construct(sprintf(
            '%s is not installed, which is required by the configured collector [%s]. '
            .'Install it with `composer require %s`, or set a different collector in '
            .'config/inertia-breadcrumbs.php (the built-in %s requires no extra package).',
            $packageIdentifier,
            $collectorClass,
            $packageIdentifier,
            ClosureBreadcrumbsCollector::class,
        ));
    }
}
