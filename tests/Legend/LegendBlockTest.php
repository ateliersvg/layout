<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Legend;

use Atelier\Layout\Alignment;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Legend\LegendBlock;
use Atelier\Layout\Legend\PlacedLegend;
use Atelier\Layout\Legend\PlacedLegendEntry;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LegendBlock::class)]
#[CoversClass(PlacedLegend::class)]
#[CoversClass(PlacedLegendEntry::class)]
#[CoversClass(Rect::class)]
#[CoversClass(InvalidArgumentException::class)]
final class LegendBlockTest extends TestCase
{
    public function testVerticalLegendStacksEntriesWithSwatchesAndLabels(): void
    {
        $layout = LegendBlock::vertical('legend')
            ->gap(8)
            ->labelGap(6)
            ->swatchSize(10, 10)
            ->padding(InsetSpec::px(4))
            ->align(Alignment::Center, Alignment::Start)
            ->add('api', 40, 12)
            ->add('worker', 60, 12);

        $placed = $layout->place(Rect::fromSize(120, 100));

        $this->assertFalse($placed->overflowX);
        $this->assertFalse($placed->overflowY);
        $this->assertSame(22.0, $placed->frame->x);
        $this->assertSame(4.0, $placed->frame->y);
        $this->assertSame(76.0, $placed->frame->width);
        $this->assertSame(32.0, $placed->frame->height);
        $this->assertSame(22.0, $placed->entry('api')?->frame->x);
        $this->assertSame(5.0, $placed->entry('api')?->swatchFrame->y);
        $this->assertSame(38.0, $placed->entry('api')?->labelFrame->x);
        $this->assertSame(24.0, $placed->entry('worker')?->frame->y);
    }

    public function testHorizontalLegendAlignsEntriesAndReportsOverflow(): void
    {
        $layout = LegendBlock::horizontal('legend')
            ->gap(10)
            ->labelGap(4)
            ->swatchSize(8, 8)
            ->padding(InsetSpec::px(2))
            ->align(Alignment::Start, Alignment::Center)
            ->add('api', 30, 10)
            ->add('worker', 40, 10);

        $placed = $layout->place(Rect::fromSize(100, 24));

        $this->assertTrue($placed->overflowX);
        $this->assertFalse($placed->overflowY);
        $this->assertSame(2.0, $placed->frame->x);
        $this->assertSame(7.0, $placed->frame->y);
        $this->assertSame(104.0, $placed->frame->width);
        $this->assertSame(10.0, $placed->frame->height);
        $this->assertSame(2.0, $placed->entry('api')?->frame->x);
        $this->assertSame(54.0, $placed->entry('worker')?->frame->x);
    }

    public function testEmptyLegendResolvesToContentFrameWithoutEntries(): void
    {
        $placed = LegendBlock::vertical('legend')
            ->padding(InsetSpec::px(4))
            ->place(Rect::fromSize(120, 100));

        $this->assertFalse($placed->overflowX);
        $this->assertFalse($placed->overflowY);
        $this->assertSame([], $placed->entries);
        $this->assertSame(4.0, $placed->frame->x);
        $this->assertSame(112.0, $placed->frame->width);
        $this->assertNull($placed->entry('missing'));
    }

    public function testRejectsNegativeLabelSize(): void
    {
        $this->expectException(InvalidArgumentException::class);

        LegendBlock::vertical('legend')->add('api', -1, 10);
    }

    public function testRejectsNegativeGap(): void
    {
        $this->expectException(InvalidArgumentException::class);

        LegendBlock::vertical('l')->gap(-1.0);
    }
}
