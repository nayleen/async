<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use Nayleen\Async\Component;
use Nayleen\Finder\Expectation;
use Nayleen\Finder\Expectation\ExtendsClass;
use Nayleen\Finder\Expectation\IsInstantiable;
use Nayleen\Finder\Finder as BaseFinder;

/**
 * @template T of Component
 * @template-extends BaseFinder<T>
 */
class Finder extends BaseFinder
{
    protected function expectation(): Expectation
    {
        return (new ExtendsClass(Component::class))->and(new IsInstantiable());
    }
}
