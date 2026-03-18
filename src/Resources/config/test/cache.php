<?php

declare(strict_types = 1);

use Amp\Cache\Cache;
use Amp\Cache\LocalCache;
use Amp\Sync\KeyedMutex;
use Amp\Sync\LocalKeyedMutex;

return [
    // services
    Cache::class => DI\factory(static fn () => new LocalCache()),
    KeyedMutex::class => DI\factory(static fn () => new LocalKeyedMutex()),
];
