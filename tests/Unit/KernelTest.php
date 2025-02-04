<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\WritableStream;
use Amp\DeferredCancellation;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Component\Bootstrapper;
use Nayleen\Async\Test\TestKernel;
use Revolt\EventLoop\Driver;
use Symfony\Component\Clock\ClockInterface;

use function Amp\async;

/**
 * @internal
 * @small
 */
final class KernelTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function always_prepends_bootstrapper(): void
    {
        $components = iterator_to_array((new TestKernel())->components);

        self::assertInstanceOf(Bootstrapper::class, $components[0]);
    }

    /**
     * @test
     */
    public function bootstrapper_gets_deduplicated(): void
    {
        $kernel = new Kernel([new Bootstrapper()]);
        $components = iterator_to_array($kernel->components);

        self::assertInstanceOf(Bootstrapper::class, $components[0]);
        self::assertNotInstanceOf(Bootstrapper::class, $components[1]);
    }

    /**
     * @test
     */
    public function can_retrieve_clock(): void
    {
        $clock = $this->createMock(ClockInterface::class);
        $clock->expects(self::once())->method('now');

        $kernel = TestKernel::create([
            Clock::class => new Clock(self::createStub(Driver::class), $clock),
        ]);
        $kernel->clock()->now();
    }

    /**
     * @test
     */
    public function can_retrieve_default_channel(): void
    {
        $stdOut = $this->createMock(WritableStream::class);
        $stdOut->expects(self::once())->method('write');

        $kernel = new TestKernel(stdOut: $stdOut);
        $kernel->channel()->send('Test');
    }

    /**
     * @test
     */
    public function run_returns_null_when_event_loop_is_cancelled(): void
    {
        $cancellation = new DeferredCancellation();
        $kernel = TestKernel::create([], cancellation: $cancellation->getCancellation());

        $return = $kernel->run(static function (Kernel $kernel) use ($cancellation): mixed {
            $cancellation->cancel();

            return async(fn () => 420)->await($kernel->cancellation);
        });

        self::assertNull($return);
    }

    /**
     * @test
     */
    public function run_returns_value_from_callback(): void
    {
        $return = (new TestKernel())->run(static fn (): int => 420);

        self::assertSame(420, $return);
    }
}
