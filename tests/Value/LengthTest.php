<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Value;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Length::class)]
final class LengthTest extends TestCase
{
    public function testResolvesPixelLengthIgnoringReference(): void
    {
        $length = Length::px(42);

        $this->assertSame(42.0, $length->place(1000));
        $this->assertSame(42.0, $length->place(0));
    }

    public function testResolvesPercentLengthAgainstReference(): void
    {
        $length = Length::percent(25);

        $this->assertSame(50.0, $length->place(200));
        $this->assertSame(0.0, $length->place(0));
    }

    public function testZeroLengthIsAllowed(): void
    {
        $this->assertSame(0.0, Length::px(0)->place(100));
    }

    public function testRejectsNegativePixelLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Length::px(-1);
    }

    public function testRejectsNegativePercentLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Length::percent(-0.5);
    }
}
