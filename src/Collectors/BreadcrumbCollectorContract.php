<?php

namespace RobertBoes\InertiaBreadcrumbs\Collectors;

use Illuminate\Http\Request;
use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;

interface BreadcrumbCollectorContract
{
    public function forRequest(Request $request): BreadcrumbCollection;
}
