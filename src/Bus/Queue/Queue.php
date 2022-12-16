<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\Promise;

interface Queue
{
    /**
     * @return Promise<null|string>
     */
    public function consume(): Promise;

    /**
     * @return Promise<null>
     */
    public function enqueue(string $message): Promise;

    public function name(): string;
}
