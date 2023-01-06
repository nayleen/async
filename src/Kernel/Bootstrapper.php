<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use DI\ContainerBuilder;
use Nayleen\Async\Kernel\Component\Component;

final class Bootstrapper extends Component
{
    public function name(): string
    {
        return 'bootstrap';
    }

    public function register(ContainerBuilder $containerBuilder): void
    {
        $this->configure($containerBuilder, __DIR__ . '/../../config/bootstrap.php');
    }
}
