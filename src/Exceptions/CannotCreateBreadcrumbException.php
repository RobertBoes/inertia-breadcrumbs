<?php

namespace RobertBoes\InertiaBreadcrumbs\Exceptions;

use Exception;

class CannotCreateBreadcrumbException extends Exception
{
    public function __construct(string $message = 'Unable to create breadcrumb from the given data')
    {
        parent::__construct($message);
    }
}
