<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use DI\ContainerBuilder;
use Nayleen\Async\Kernel\Component\Component;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop\Driver;

/**
 * @internal
 */
final class KernelTestComponent extends Component
{
    private readonly LoggerInterface $logger;
    private readonly Driver $loop;

    public static function create(Driver $loop, LoggerInterface $logger)
    {
        $instance = new self();
        $instance->logger = $logger;
        $instance->loop = $loop;

        return $instance;
    }

    public function name(): string
    {
        return 'test';
    }

    public function register(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addDefinitions(
            [
                Driver::class => $this->loop,
                LoggerInterface::class => $this->logger,
            ]
        );
    }
}
