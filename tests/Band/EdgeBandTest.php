<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Band;

use Atelier\Layout\Band\EdgeBand;
use Atelier\Layout\Band\PlacedEdgeBand;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EdgeBand::class)]
#[CoversClass(PlacedEdgeBand::class)]
#[CoversClass(Rect::class)]
#[CoversClass(InvalidArgumentException::class)]
final class EdgeBandTest extends TestCase
{
    public function testTopEdgeBandReservesSpaceAboveContent(): void
    {
        $layout = EdgeBand::top('surface')
            ->bandSize(24)
            ->gap(8)
            ->padding(InsetSpec::px(4))
            ->place(Rect::fromSize(120, 100));

        $this->assertFalse($layout->overflowY);
        $this->assertTrue($layout->bandAtTop);
        $this->assertSame(4.0, $layout->frame->x);
        $this->assertSame(4.0, $layout->frame->y);
        $this->assertSame(112.0, $layout->frame->width);
        $this->assertSame(92.0, $layout->frame->height);
        $this->assertSame(4.0, $layout->bandFrame->y);
        $this->assertSame(24.0, $layout->bandFrame->height);
        $this->assertSame(36.0, $layout->contentFrame->y);
        $this->assertSame(60.0, $layout->contentFrame->height);
    }

    public function testBottomEdgeBandPlacesEdgeAfterContent(): void
    {
        $layout = EdgeBand::bottom('surface')
            ->bandSize(20)
            ->gap(6)
            ->place(Rect::fromSize(80, 60));

        $this->assertFalse($layout->overflowY);
        $this->assertFalse($layout->bandAtTop);
        $this->assertSame(40.0, $layout->bandFrame->y);
        $this->assertSame(34.0, $layout->contentFrame->height);
    }

    public function testRejectsNegativeEdgeHeight(): void
    {
        $this->expectException(InvalidArgumentException::class);

        EdgeBand::top('surface')->bandSize(-1);
    }
}
