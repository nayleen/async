<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use Nayleen\Async\Component;

interface HasDependencies
{
    /**
     * @return iterable<class-string<Component>>
     */
    public static function dependencies(): iterable;
}
