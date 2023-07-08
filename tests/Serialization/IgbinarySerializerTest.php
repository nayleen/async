<?php

declare(strict_types = 1);

namespace Nayleen\Async\Serialization;

use Amp\Serialization\SerializationException;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Safe;
use stdClass;

/**
 * @internal
 */
final class IgbinarySerializerTest extends TestCase
{
    private string $value = 'some_string';

    public function notSerializable(): Generator
    {
        yield 'anonymous class' => [
            new class() {
            },
        ];
        yield 'Closure' => [fn (): mixed => null];
        yield 'Generator' => [fn (): Generator => yield 123];
        yield 'ReflectionClass' => [new ReflectionClass(stdClass::class)];
        yield 'resource' => [Safe\fopen(__FILE__, 'rb')];
    }

    /**
     * @test
     * @dataProvider notSerializable
     */
    public function serialization_failure_throws_exception(mixed $value): void
    {
        $this->expectException(SerializationException::class);

        $serializer = new IgbinarySerializer();
        $serializer->serialize($value);
    }

    /**
     * @test
     */
    public function serialize_produces_expected_result(): string
    {
        $serializer = new IgbinarySerializer();
        $serialized = $serializer->serialize($this->value);

        self::assertSame(igbinary_serialize($this->value), $serialized);

        return $serialized;
    }

    /**
     * @test
     * @depends serialize_produces_expected_result
     */
    public function unserialize_restores_original_value(string $serialized): void
    {
        $serializer = new IgbinarySerializer();
        self::assertSame($this->value, $serializer->unserialize($serialized));
    }

    /**
     * @test
     */
    public function unserialize_throws_on_unserializable_value(): void
    {
        $this->expectException(SerializationException::class);

        $serializer = new IgbinarySerializer();
        $serialized = $serializer->serialize($this->value);
        $serialized = substr($serialized, 1);

        $serializer->unserialize($serialized);
    }
}
