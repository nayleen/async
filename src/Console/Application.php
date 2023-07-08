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
class Application extends BaseApplication
{
    public function __construct(private readonly Kernel $kernel)
    {
        $container = $kernel->container();

        $name = $container->get('async.app_name');
        assert(is_string($name) && $name !== '');

        $version = $container->get('async.app_version');
        assert(is_string($version) && $version !== '');

        parent::__construct($name, $version);
    }

    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        $this->setAutoExit(false);

        $input ??= $this->kernel->container()->get(InputInterface::class);
        $output ??= $this->kernel->container()->get(OutputInterface::class);

        return parent::run($input, $output);
    }
}
