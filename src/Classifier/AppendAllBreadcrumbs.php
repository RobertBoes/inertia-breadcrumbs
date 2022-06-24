<?php

namespace RobertBoes\InertiaBreadcrumbs\Classifier;

use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;

class AppendAllBreadcrumbs implements ClassifierContract
{
    public function shouldShareBreadcrumbs(BreadcrumbCollection $collection): bool
    {
        return true;
    }
}
