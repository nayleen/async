<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Serialization\CompressingSerializer;
use Amp\Serialization\NativeSerializer;
use Amp\Serialization\Serializer;

return [
    // serialization services
    CompressingSerializer::class => static fn (
        Serializer $serializer,
    ): CompressingSerializer => new CompressingSerializer($serializer),

    Serializer::class => static fn (): Serializer => new NativeSerializer(),
];
