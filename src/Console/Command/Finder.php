<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Command;

use Nayleen\Finder\Expectation;
use Nayleen\Finder\Expectation\ExtendsClass;
use Nayleen\Finder\Expectation\IsInstantiable;
use Nayleen\Finder\Finder as BaseFinder;
use Symfony\Component\Console\Command\Command;

class Finder extends BaseFinder
{
    protected function expectation(): Expectation
    {
        return (new ExtendsClass(Command::class))->and(new IsInstantiable());
    }
}
