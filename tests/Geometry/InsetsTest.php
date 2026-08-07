<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Geometry;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Insets;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Insets::class)]
final class InsetsTest extends TestCase
{
    public function testZeroIsEmptyOnEveryEdge(): void
    {
        $insets = Insets::zero();

        $this->assertSame(0.0, $insets->top);
        $this->assertSame(0.0, $insets->right);
        $this->assertSame(0.0, $insets->bottom);
        $this->assertSame(0.0, $insets->left);
    }

    public function testRejectsNegativeInset(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Insets(-1.0, 0.0, 0.0, 0.0);
    }
}
