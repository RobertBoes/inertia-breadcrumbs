<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests\Concerns;

use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;

trait SetupCollector
{
    abstract protected function collector(): string;

    abstract protected function provider(): string;

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            $this->provider(),
        ]);
    }

    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);
        $app->bind(BreadcrumbCollectorContract::class, $this->collector());
    }
}
