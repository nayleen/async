<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Handler;

use Nayleen\Async\Bus\Message;

/**
 * @api
 */
interface Handler
{
    public function __invoke(Message $message): void;
}
