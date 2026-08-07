<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Geometry;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\GroupBounds;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GroupBounds::class)]
#[CoversClass(Rect::class)]
#[CoversClass(Insets::class)]
#[CoversClass(Size::class)]
#[CoversClass(InvalidArgumentException::class)]
final class GroupBoundsTest extends TestCase
{
    public function testFrameReturnsNullForEmptyInput(): void
    {
        $groupBounds = GroupBounds::fromFrames('group', []);

        $this->assertNull($groupBounds->frame());
    }

    public function testExposesId(): void
    {
        $this->assertSame('group', GroupBounds::fromFrames('group', [])->id());
    }

    public function testFrameExpandsUnionWithPaddingLabelReserveAndMinSize(): void
    {
        $groupBounds = GroupBounds::fromFrames('group', [
            'a' => new Rect(20, 40, 30, 10),
            'b' => new Rect(60, 50, 20, 20),
        ])
            ->padding(Insets::all(5))
            ->topReserve(12)
            ->minSize(new Size(100, 80));

        $frame = $groupBounds->frame();

        $this->assertNotNull($frame);
        $this->assertSame(15.0, $frame->x);
        $this->assertSame(23.0, $frame->y);
        $this->assertSame(100.0, $frame->width);
        $this->assertSame(80.0, $frame->height);
    }

    public function testClampToCanvasDoesNotProduceNegativeSizes(): void
    {
        $groupBounds = GroupBounds::fromFrames('group', [
            'a' => new Rect(20, 20, 10, 10),
        ])->clampTo(new Rect(0, 0, 12, 12));

        $frame = $groupBounds->frame();

        $this->assertNotNull($frame);
        $this->assertSame(0.0, $frame->width);
        $this->assertSame(0.0, $frame->height);
        $this->assertSame(20.0, $frame->x);
        $this->assertSame(20.0, $frame->y);
    }

    public function testRejectsNegativeLabelReserve(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GroupBounds::fromFrames('group', [new Rect(0, 0, 10, 10)])->topReserve(-1);
    }

    public function testConstructorRejectsNegativeLabelTop(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GroupBounds('c', labelTop: -1.0);
    }
}
