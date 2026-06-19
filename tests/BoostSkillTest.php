<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\Classifier\AppendAllBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\Classifier\ClassifierContract;
use RobertBoes\InertiaBreadcrumbs\Classifier\IgnoreSingleBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\Collectors\ClosureBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\DiglacticBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\GretelBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\Collectors\TabunaBreadcrumbsCollector;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;
use RobertBoes\InertiaBreadcrumbs\ShareStrategy;

/**
 * Guards the Laravel Boost guideline + skill against drift. The skill teaches
 * consumers how to use this package's API; if the API changes and the skill
 * isn't updated, the skill starts handing out hallucination-shaped advice.
 * These assertions tie every factual claim in the skill back to real code.
 */
class BoostSkillTest extends TestCase
{
    private function boostPath(string $relative): string
    {
        return __DIR__.'/../resources/boost/'.$relative;
    }

    #[Test]
    public function it_ships_the_boost_files_in_the_documented_layout(): void
    {
        // The exact paths Boost scans for third-party guidelines and skills.
        $this->assertFileExists($this->boostPath('guidelines/core.blade.php'));
        $this->assertFileExists($this->boostPath('skills/inertia-breadcrumbs/SKILL.md'));
        $this->assertFileExists($this->boostPath('skills/inertia-breadcrumbs/references/defining-breadcrumbs.md'));
        $this->assertFileExists($this->boostPath('skills/inertia-breadcrumbs/references/rendering-breadcrumbs.md'));
        $this->assertFileExists($this->boostPath('skills/inertia-breadcrumbs/references/configuration.md'));
    }

    #[Test]
    public function the_skill_has_valid_frontmatter(): void
    {
        $contents = file_get_contents($this->boostPath('skills/inertia-breadcrumbs/SKILL.md'));

        $this->assertStringStartsWith('---', $contents);
        $this->assertMatchesRegularExpression('/^name:\s*inertia-breadcrumbs\s*$/m', $contents);
        $this->assertMatchesRegularExpression('/^description:\s*\S+/m', $contents);
    }

    #[Test]
    public function every_api_symbol_the_skill_teaches_exists(): void
    {
        $this->assertTrue(method_exists(Breadcrumb::class, 'make'));
        $this->assertTrue(method_exists(InertiaBreadcrumbs::class, 'for'));
        $this->assertTrue(method_exists(InertiaBreadcrumbs::class, 'serializeUsing'));
        $this->assertTrue(method_exists(ClassifierContract::class, 'shouldShareBreadcrumbs'));

        foreach ([
            ClosureBreadcrumbsCollector::class,
            DiglacticBreadcrumbsCollector::class,
            GretelBreadcrumbsCollector::class,
            TabunaBreadcrumbsCollector::class,
            AppendAllBreadcrumbs::class,
            IgnoreSingleBreadcrumbs::class,
        ] as $class) {
            $this->assertTrue(class_exists($class), "{$class} is referenced by the skill but no longer exists");
        }

        foreach (['default', 'always', 'deferred'] as $strategy) {
            $this->assertNotNull(
                ShareStrategy::tryFrom($strategy),
                "ShareStrategy '{$strategy}' is referenced by the skill but no longer exists",
            );
        }
    }

    #[Test]
    public function the_config_keys_the_skill_documents_exist(): void
    {
        $config = require __DIR__.'/../config/inertia-breadcrumbs.php';

        $this->assertArrayHasKey('enabled', $config['middleware']);
        $this->assertArrayHasKey('group', $config['middleware']);
        $this->assertArrayHasKey('key', $config['middleware']);
        $this->assertArrayHasKey('share', $config);
        $this->assertArrayHasKey('collector', $config);
        $this->assertArrayHasKey('classifier', $config);
        $this->assertArrayHasKey('ignore_query', $config);

        // The skill tells the frontend the default prop key is "breadcrumbs".
        $this->assertSame('breadcrumbs', $config['middleware']['key']);
    }

    #[Test]
    public function it_keeps_the_gretel_double_sharing_caveat(): void
    {
        // The single most important gotcha the skill encodes — losing it
        // silently reintroduces the duplicate-breadcrumbs bug for gretel users.
        $config = file_get_contents($this->boostPath('skills/inertia-breadcrumbs/references/configuration.md'));

        $this->assertStringContainsString("'inertiajs/inertia-laravel' => false", $config);
    }
}
