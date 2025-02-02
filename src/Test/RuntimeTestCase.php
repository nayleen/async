<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Kernel;
use Nayleen\Async\Runtime;
use ReflectionObject;

abstract class RuntimeTestCase extends AsyncTestCase
{
    /**
     * @template T of mixed
     * @param Runtime<mixed, mixed, T> $runtime
     * @return T
     */
    final public function execute(Runtime $runtime, ?Kernel $kernel = null): mixed
    {
        $reflection = new ReflectionObject($runtime);

        return $reflection->getMethod('execute')->invoke($runtime, $kernel ?? TestKernel::create());
    }
}
