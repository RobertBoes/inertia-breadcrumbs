<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests\Stubs\Classes;

use Illuminate\Http\Request;
use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;
use RobertBoes\InertiaBreadcrumbs\Collectors\AbstractBreadcrumbCollector;

class InvalidDummyCollector extends AbstractBreadcrumbCollector
{
    public static function requiredClass(): string
    {
        return NonExistentDummyClass::class;
    }

    public static function notInstalledException(): string
    {
        return DummyException::class;
    }

    public function forRequest(Request $request): BreadcrumbCollection
    {
        //
    }
}
