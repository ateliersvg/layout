<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests;

use Atelier\Layout\Geometry\Size;
use Atelier\Layout\IntrinsicSize;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IntrinsicSize::class)]
final class IntrinsicSizeTest extends TestCase
{
    public function testExposesSizeAndDefaultsBaselinesToNull(): void
    {
        $measure = new IntrinsicSize(new Size(40, 20));

        $this->assertSame(40.0, $measure->size->width);
        $this->assertSame(20.0, $measure->size->height);
        $this->assertNull($measure->firstBaseline);
        $this->assertNull($measure->lastBaseline);
    }

    public function testExposesProvidedBaselines(): void
    {
        $measure = new IntrinsicSize(new Size(40, 20), firstBaseline: 16.0, lastBaseline: 18.0);

        $this->assertSame(16.0, $measure->firstBaseline);
        $this->assertSame(18.0, $measure->lastBaseline);
    }
}
