<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Grid;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\Grid\TrackSizeKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TrackSize::class)]
#[CoversClass(TrackSizeKind::class)]
final class TrackSizeTest extends TestCase
{
    public function testCreatesFixedTrack(): void
    {
        $track = TrackSize::fixed(64);

        $this->assertSame(TrackSizeKind::Fixed, $track->kind);
        $this->assertSame(64.0, $track->value);
    }

    public function testCreatesFractionTrack(): void
    {
        $track = TrackSize::fr(2);

        $this->assertSame(TrackSizeKind::Fraction, $track->kind);
        $this->assertSame(2.0, $track->value);
    }

    public function testRejectsInvalidFraction(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TrackSize::fr(0);
    }

    public function testRejectsNegativeFixedValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TrackSize::fixed(-1.0);
    }
}
