<?php

namespace RobertBoes\InertiaBreadcrumbs\Classifier;

use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;

class IgnoreSingleBreadcrumbs implements ClassifierContract
{
    public function shouldShareBreadcrumbs(BreadcrumbCollection $collection): bool
    {
        return $collection->items()->count() > 1;
    }
}
