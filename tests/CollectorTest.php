<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use RobertBoes\InertiaBreadcrumbs\Tests\Stubs\Classes\DummyException;
use RobertBoes\InertiaBreadcrumbs\Tests\Stubs\Classes\InvalidDummyCollector;

class CollectorTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_when_required_class_does_not_exist()
    {
        $this->expectException(DummyException::class);

        new InvalidDummyCollector();
    }
}
