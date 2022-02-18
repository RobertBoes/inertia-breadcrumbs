<?php

namespace RobertBoes\InertiaBreadcrumbs\Exceptions;

use Exception;

class PackageNotInstalledException extends Exception
{
    public function __construct(string $packageIdentifier)
    {
        parent::__construct(sprintf("%s is not installed", $packageIdentifier));
    }
}
