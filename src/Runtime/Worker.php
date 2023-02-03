<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use BadMethodCallException;
use Nayleen\Async\Kernel;
use Nayleen\Async\Runtime;
use Nayleen\Async\Worker as WorkerImplementation;

use function Amp\async;

/**
 * @api
 */
final class Worker extends Runtime
{
    private function __construct(
        Kernel $kernel,
        private readonly WorkerImplementation $worker
    ) {
        parent::__construct($kernel);
    }

    public static function create(
        Kernel $kernel = new Kernel(),
        string|WorkerImplementation|null $worker = null,
    ): Worker {
        assert(func_num_args() === 2, new BadMethodCallException('Please provide a worker class name or container item'));

        if (is_string($worker)) {
            $worker = $kernel->make($worker);
            assert($worker instanceof WorkerImplementation);
        }

        return new self($kernel, $worker);
    }

    public function run(): int
    {
        $this->kernel->run(fn () => async($this->worker->run(...), $this->kernel));

        return 0;
    }
}
