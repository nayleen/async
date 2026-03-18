<?php

declare(strict_types = 1);

use Amp\ByteStream;
use Monolog\Handler\TestHandler;

return [
    // services
    'app.stderr' => DI\factory(static fn () => new ByteStream\WritableBuffer()),
    'app.stdin' => DI\factory(static fn () => new ByteStream\ReadableBuffer()),
    'app.stdout' => DI\factory(static fn () => new ByteStream\WritableBuffer()),

    'log.handlers' => DI\add([
        DI\get(TestHandler::class),
    ]),

    TestHandler::class => DI\factory(static fn () => new TestHandler(bubble: false)),
];
