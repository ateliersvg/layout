<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Value;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Value\Dimension;
use Atelier\Layout\Value\DimensionKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Dimension::class)]
#[CoversClass(DimensionKind::class)]
final class DimensionTest extends TestCase
{
    public function testCreatesFixedDimension(): void
    {
        $dimension = Dimension::fixed(42);

        $this->assertSame(DimensionKind::Fixed, $dimension->kind);
        $this->assertSame(42.0, $dimension->value);
    }

    public function testCreatesStretchDimension(): void
    {
        $dimension = Dimension::stretch(2);

        $this->assertSame(DimensionKind::Stretch, $dimension->kind);
        $this->assertSame(2.0, $dimension->value);
    }

    public function testCreatesAutoDimension(): void
    {
        $dimension = Dimension::auto();

        $this->assertSame(DimensionKind::Auto, $dimension->kind);
        $this->assertSame(0.0, $dimension->value);
    }

    public function testCreatesMinContentDimension(): void
    {
        $dimension = Dimension::minContent();

        $this->assertSame(DimensionKind::MinContent, $dimension->kind);
        $this->assertSame(0.0, $dimension->value);
    }

    public function testCreatesMaxContentDimension(): void
    {
        $dimension = Dimension::maxContent();

        $this->assertSame(DimensionKind::MaxContent, $dimension->kind);
        $this->assertSame(0.0, $dimension->value);
    }

    public function testRejectsInvalidStretchFactor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Dimension::stretch(0);
    }

    public function testRejectsNegativeFixedValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Dimension::fixed(-1.0);
    }
}
