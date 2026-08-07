<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Utility;

use Atelier\Layout\Anchor;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Utility\Snap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Snap::class)]
final class SnapTest extends TestCase
{
    public function testSnapMovesSubjectAnchorToTargetAnchor(): void
    {
        $subject = new Rect(0, 0, 20, 10);
        $target = new Rect(100, 50, 40, 40);

        $snapped = Snap::rect($subject, Anchor::TopCenter, $target, Anchor::BottomCenter, offsetY: -5);

        $this->assertSame(110.0, $snapped->x);
        $this->assertSame(85.0, $snapped->y);
        $this->assertSame(20.0, $snapped->width);
        $this->assertSame(10.0, $snapped->height);
    }
}
