<?php

namespace RobertBoes\InertiaBreadcrumbs\Classifier;

use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;

interface ClassifierContract
{
    public function shouldShareBreadcrumbs(BreadcrumbCollection $collection): bool;
}
