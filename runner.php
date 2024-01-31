<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Nayleen\Async\Test\NoopWorker;

require __DIR__ . '/vendor/autoload.php';

(new Cluster(new NoopWorker(), count: 1))->run();
