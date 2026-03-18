<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Monolog\Utils;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * @psalm-internal Nayleen\Async
 */
final readonly class ErrorHandler
{
    public function __construct(private LoggerInterface $logger) {}

    public function __invoke(Throwable $throwable): void
    {
        $this->logger->log(
            LogLevel::ERROR,
            sprintf(
                'Uncaught %s: "%s" at %s line %s',
                Utils::getClass($throwable),
                $throwable->getMessage(),
                $throwable->getFile(),
                $throwable->getLine(),
            ),
        );
    }
}
