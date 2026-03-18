<?php

declare(strict_types = 1);

namespace Nayleen\Async\Serialization;

use Amp\Serialization\NativeSerializer;
use Amp\Serialization\Serializer;
use DI\Container;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SerializerFactory::class)]
final class SerializerFactoryTest extends TestCase
{
    #[Test]
    public function always_produces_a_serializer(): void
    {
        $this->expectNotToPerformAssertions();

        $factory = new SerializerFactory();
        $factory(new Container(), null);
    }

    /**
     * @param class-string<Serializer> $class
     */
    #[Test]
    #[TestWith(['igbinary', IgbinarySerializer::class], 'igbinary')]
    #[TestWith(['msgpack', MsgpackSerializer::class], 'msgpack')]
    #[TestWith(['native', NativeSerializer::class], 'native')]
    #[TestWith(['php', NativeSerializer::class], 'php')]
    public function produces_supported_serializer(string $type, string $class): void
    {
        $factory = new SerializerFactory();
        $serializer = $factory(new Container(), $type);

        self::assertInstanceOf($class, $serializer);
    }

    #[Test]
    public function throws_for_unsupported_serializer_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported serializer type: unsupported');

        $factory = new SerializerFactory();
        $factory(new Container(), 'unsupported');
    }
}
