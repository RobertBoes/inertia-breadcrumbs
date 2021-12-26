<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Illuminate\Http\Request;

interface BreadcrumbCollectorContract
{
    public function forRequest(Request $request): BreadcrumbCollection;
}
