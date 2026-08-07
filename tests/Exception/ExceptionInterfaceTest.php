<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Exception;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Exception\LayoutExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidArgumentException::class)]
final class ExceptionInterfaceTest extends TestCase
{
    public function testInvalidArgumentExceptionImplementsLayoutInterfaceAndThrowable(): void
    {
        $exception = new InvalidArgumentException('boom');

        $this->assertInstanceOf(LayoutExceptionInterface::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertSame('boom', $exception->getMessage());
    }

    public function testInvalidArgumentExceptionIsCatchableAsLayoutException(): void
    {
        $caught = null;

        try {
            throw new InvalidArgumentException('caught me');
        } catch (LayoutExceptionInterface $e) {
            $caught = $e;
        }

        $this->assertInstanceOf(InvalidArgumentException::class, $caught);
        $this->assertSame('caught me', $caught->getMessage());
    }
}
