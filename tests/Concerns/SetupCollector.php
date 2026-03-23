<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests\Concerns;

use RobertBoes\InertiaBreadcrumbs\Collectors\AbstractBreadcrumbCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\BreadcrumbCollectorContract;

trait SetupCollector
{
    abstract protected function collector(): string;

    abstract protected function provider(): string;

    protected function setUp(): void
    {
        $this->skipIfCollectorPackageMissing();

        parent::setUp();
    }

    protected function skipIfCollectorPackageMissing(): void
    {
        $collector = $this->collector();

        if (is_subclass_of($collector, AbstractBreadcrumbCollector::class)) {
            if (! class_exists($collector::requiredClass())) {
                $this->markTestSkipped("{$collector::packageIdentifier()} is not installed");
            }
        }
    }

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
