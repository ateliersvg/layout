<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Inline;

use Atelier\Layout\Alignment;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Inline\InlineGroup;
use Atelier\Layout\Inline\PlacedInlineGroup;
use Atelier\Layout\Inline\PlacedInlineItem;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InlineGroup::class)]
#[CoversClass(PlacedInlineGroup::class)]
#[CoversClass(PlacedInlineItem::class)]
#[CoversClass(Rect::class)]
#[CoversClass(InvalidArgumentException::class)]
final class InlineGroupTest extends TestCase
{
    public function testEqualItemsShareAvailableSpace(): void
    {
        $layout = InlineGroup::equal('browse.tasks')
            ->gap(10)
            ->add('choose-product')
            ->add('enter-item')
            ->add('review')
            ->place(Rect::fromSize(200, 40));

        $this->assertFalse($layout->overflowX);
        $this->assertSame(0.0, $layout->frame->x);
        $this->assertSame(200.0, $layout->frame->width);
        $this->assertSame(60.0, $layout->item('choose-product')?->frame->width);
        $this->assertSame(70.0, $layout->item('enter-item')?->frame->x);
        $this->assertSame(140.0, $layout->item('review')?->frame->x);
    }

    public function testContentSizedItemsCanCenterAndOverflow(): void
    {
        $layout = InlineGroup::contentSized('browse.tasks')
            ->gap(10)
            ->align(Alignment::Center)
            ->add('choose-product', preferredWidth: 50, minWidth: 40)
            ->add('enter-item', preferredWidth: 60, minWidth: 40)
            ->place(Rect::fromSize(200, 40));

        $this->assertFalse($layout->overflowX);
        $this->assertSame(40.0, $layout->frame->x);
        $this->assertSame(120.0, $layout->frame->width);
        $this->assertSame(40.0, $layout->item('choose-product')?->frame->x);
        $this->assertSame(100.0, $layout->item('enter-item')?->frame->x);

        $overflow = InlineGroup::contentSized('browse.tasks')
            ->gap(10)
            ->align(Alignment::End)
            ->add('choose-product', preferredWidth: 100, minWidth: 80)
            ->add('enter-item', preferredWidth: 110, minWidth: 80)
            ->place(Rect::fromSize(200, 40));

        $this->assertTrue($overflow->overflowX);
        $this->assertSame(0.0, $overflow->frame->x);
        $this->assertSame(220.0, $overflow->frame->width);
        $this->assertSame(0.0, $overflow->item('choose-product')?->frame->x);
        $this->assertSame(110.0, $overflow->item('enter-item')?->frame->x);
    }

    public function testEmptyRowResolvesToAvailableFrameWithoutItems(): void
    {
        $layout = InlineGroup::equal('empty')
            ->padding(InsetSpec::px(10))
            ->place(Rect::fromSize(200, 40));

        $this->assertFalse($layout->overflowX);
        $this->assertSame([], $layout->items);
        $this->assertSame(10.0, $layout->frame->x);
        $this->assertSame(180.0, $layout->frame->width);
        $this->assertNull($layout->item('missing'));
    }

    public function testRejectsNegativeWidths(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InlineGroup::equal('browse.tasks')->add('bad', preferredWidth: -1);
    }

    public function testRejectsNegativeGap(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InlineGroup::equal('r')->gap(-1.0);
    }
}
