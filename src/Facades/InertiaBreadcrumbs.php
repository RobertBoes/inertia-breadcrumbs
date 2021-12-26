<?php

namespace RobertBoes\InertiaBreadcrumbs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs
 */
class InertiaBreadcrumbs extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'inertia-breadcrumbs';
    }
}
