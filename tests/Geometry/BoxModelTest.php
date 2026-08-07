<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Geometry;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\BoxModel;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\StrokePlacement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoxModel::class)]
#[CoversClass(StrokePlacement::class)]
final class BoxModelTest extends TestCase
{
    public function testContentRectAccountsForInsideStrokeAndPadding(): void
    {
        $box = new BoxModel(
            outer: Rect::fromSize(200, 400),
            padding: Insets::all(8),
            strokeWidth: 10,
            strokePlacement: StrokePlacement::Inside,
        );

        $content = $box->contentRect();

        $this->assertSame(18.0, $content->x);
        $this->assertSame(18.0, $content->y);
        $this->assertSame(164.0, $content->width);
        $this->assertSame(364.0, $content->height);
    }

    public function testCenteredStrokeConsumesHalfStrokeInside(): void
    {
        $box = new BoxModel(Rect::fromSize(100, 100), strokeWidth: 10, strokePlacement: StrokePlacement::Center);

        $this->assertSame(5.0, $box->strokeInset());
        $this->assertSame(90.0, $box->contentRect()->width);
    }

    public function testOutsideStrokeDoesNotReduceContent(): void
    {
        $box = new BoxModel(Rect::fromSize(100, 100), strokeWidth: 10, strokePlacement: StrokePlacement::Outside);

        $this->assertSame(0.0, $box->strokeInset());
        $this->assertSame(100.0, $box->contentRect()->width);
    }

    public function testRejectsNegativeStrokeWidth(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BoxModel(Rect::fromSize(100, 100), strokeWidth: -1);
    }
}
