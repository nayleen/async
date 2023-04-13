<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Nayleen\Async\Kernel;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @api
 */
final class Application extends BaseApplication
{
    public function __construct(private readonly Kernel $kernel)
    {
        $container = $kernel->container();

        parent::__construct(
            $container->get('async.app_name'),
            $container->get('async.app_version'),
        );
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $this->setAutoExit(false);

        return parent::run(
            $this->kernel->make(InputInterface::class),
            $this->kernel->make(OutputInterface::class),
        );
    }
}
