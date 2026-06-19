<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use Laravel\Octane\Events\RequestReceived;
use PHPUnit\Framework\Attributes\Test;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;

require_once __DIR__.'/Stubs/Octane/RequestReceived.php';

class OctaneStateTest extends TestCase
{
    #[Test]
    public function it_clears_pending_breadcrumbs_when_octane_receives_a_request(): void
    {
        $breadcrumbs = app(InertiaBreadcrumbs::class);

        // Without a current route name the closure is stored as pending
        $breadcrumbs->for(fn () => [Breadcrumb::make('Pending')]);
        $this->assertNotNull($breadcrumbs->pending());

        $this->app['events']->dispatch(new RequestReceived);

        $this->assertNull($breadcrumbs->pending());
    }
}
