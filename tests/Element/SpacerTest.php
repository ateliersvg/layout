<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Element\Spacer;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Spacer::class)]
final class SpacerTest extends TestCase
{
    public function testExposesIdAndFlex(): void
    {
        $spacer = new Spacer('gap', flex: 2.0);

        $this->assertSame('gap', $spacer->id());
        $this->assertSame(2.0, $spacer->flex());
    }

    public function testMeasureAppliesMinMainSizeWithinConstraints(): void
    {
        $spacer = new Spacer('gap', minMainSize: 12.0);

        $measure = $spacer->measure(new LayoutContext(), BoxConstraints::unconstrained());

        $this->assertSame(12.0, $measure->size->width);
        $this->assertSame(0.0, $measure->size->height);
    }

    public function testSolveReturnsNodeForGivenRect(): void
    {
        $spacer = new Spacer('gap');

        $node = $spacer->solve(new LayoutContext(), new Rect(5, 6, 20, 10));

        $this->assertSame('gap', $node->id);
        $this->assertSame(5.0, $node->frame->x);
        $this->assertSame(20.0, $node->frame->width);
        $this->assertSame(20.0, $node->measure->size->width);
        $this->assertSame(10.0, $node->measure->size->height);
    }
}
