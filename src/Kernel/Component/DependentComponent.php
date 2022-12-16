<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

interface DependentComponent
{
    /**
     * @return class-string<Component>[]
     */
    public static function dependencies(): array;
}
