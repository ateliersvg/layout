<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Geometry;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Size;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Size::class)]
final class SizeTest extends TestCase
{
    public function testRejectsNegativeDimension(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Size(-1.0, 0.0);
    }
}
