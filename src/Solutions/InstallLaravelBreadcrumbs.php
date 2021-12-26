<?php

namespace RobertBoes\InertiaBreadcrumbs\Solutions;

use Facade\IgnitionContracts\RunnableSolution;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class InstallLaravelBreadcrumbs implements RunnableSolution
{
    public function getSolutionTitle(): string
    {
        return 'Laravel-breadcrumbs is not installed';
    }

    public function getSolutionDescription(): string
    {
        return 'It looks like laravel-breadcrumbs is not installed while that generator is used,
            you can install it using `composer require diglactic/laravel-breadcrumbs`, or you can write your own breadcrumb provider';
    }

    public function getDocumentationLinks(): array
    {
        return [
            'Creating a custom breadcrumbs provider' => 'https://github.com/RobertBoes/inertia-breadcrumbs'
        ];
    }

    public function getSolutionActionDescription(): string
    {
        return 'We can try to solve this exception by running the command for you.
            If the command fails this is most likely because it was not possible to install composer dependencies,
            you should run the command in a terminal yourself.';
    }

    public function getRunButtonText(): string
    {
        return 'Install diglactic/laravel-breadcrumbs';
    }

    public function run(array $parameters = [])
    {
        $process = new Process([
            $this->phpBinary(),
            'vendor/bin/composer',
            'require',
            'diglactic/laravel-breadcrumbs',
        ], base_path());
        $process->run();

        if (! $process->isSuccessful()) {
            abort(500);
        }
    }

    /*
     *  The array you return here will be passed to the `run` function.
     *
     *  Make sure everything you return here is serializable.
     *
     */
    public function getRunParameters(): array
    {   
        return [];
    }

    private function phpBinary(): string
    {
        return (new PhpExecutableFinder)->find();
    }
}
