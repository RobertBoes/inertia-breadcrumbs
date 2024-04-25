<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use PHPUnit\Framework\Attributes\Test;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;
use RobertBoes\InertiaBreadcrumbs\Exceptions\CannotCreateBreadcrumbException;
use RobertBoes\InertiaBreadcrumbs\Exceptions\PackageNotInstalledException;
use RobertBoes\InertiaBreadcrumbs\Tests\Stubs\Classes\InvalidDummyCollector;
use stdClass;

class CollectorTest extends TestCase
{
    #[Test]
    public function it_throws_an_exception_when_required_class_does_not_exist()
    {
        $this->expectException(PackageNotInstalledException::class);
        $this->expectExceptionMessage('dummy/breadcrumbs is not installed');

        new InvalidDummyCollector();
    }

    #[Test]
    public function it_creates_breadcrumb_collection_from_breadcrumbs()
    {
        $breadcrumbs = new BreadcrumbCollection([
            new Breadcrumb('test', false),
        ]);

        $this->assertSame(1, $breadcrumbs->items()->count());
    }

    #[Test]
    public function it_throws_an_excpetion_with_invalid_breadcrumbs()
    {
        $this->expectException(CannotCreateBreadcrumbException::class);
        new BreadcrumbCollection([
            [
                'title' => 'Does not work',
            ],
        ]);
    }

    #[Test]
    public function it_throws_an_exception_when_using_incorrect_initializer()
    {
        $this->expectException(CannotCreateBreadcrumbException::class);
        new BreadcrumbCollection([
            [
                'title' => 'Does not work',
            ],
        ], function ($crumb): stdClass {
            return (object) $crumb;
        });
    }
}
