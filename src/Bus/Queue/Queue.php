<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

/**
 * @api
 */
interface Queue
{
    public function consume(): ?string;

    public function enqueue(string $message): void;

    public function name(): string;
}
