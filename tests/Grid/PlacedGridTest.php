<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Grid;

use Atelier\Layout\Alignment;
use Atelier\Layout\Axis;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Grid\GridSlot;
use Atelier\Layout\Grid\PlacedGrid;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Result\PlacedTree;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlacedGrid::class)]
#[CoversClass(GridSlot::class)]
final class PlacedGridTest extends TestCase
{
    private function solvedLayout(): PlacedGrid
    {
        $grid = Grid::tracks(
            'grid',
            [TrackSize::fixed(20), TrackSize::fixed(30)],
            [TrackSize::fixed(10), TrackSize::fixed(15)],
        )
            ->gap(5, 7)
            ->padding(InsetSpec::px(4))
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::fixed('a', 10, 10))
            ->add(Frame::fixed('b', 10, 10))
            ->add(Frame::fixed('c', 10, 10))
            ->add(Frame::fixed('d', 10, 10));

        return $grid->build()->layout(new LayoutContext(), Rect::fromSize(100, 60));
    }

    public function testColumnAndRowFramesExposeTrackGeometry(): void
    {
        $layout = $this->solvedLayout();

        $this->assertSame(4.0, $layout->column(0)?->x);
        $this->assertSame(20.0, $layout->column(0)?->width);
        $this->assertSame(29.0, $layout->column(1)?->x);
        $this->assertSame(30.0, $layout->column(1)?->width);

        $this->assertSame(4.0, $layout->row(0)?->y);
        $this->assertSame(10.0, $layout->row(0)?->height);
        $this->assertSame(21.0, $layout->row(1)?->y);
        $this->assertSame(15.0, $layout->row(1)?->height);
    }

    public function testTrackDelegatesToColumnOrRowByAxis(): void
    {
        $layout = $this->solvedLayout();

        $this->assertSame($layout->column(1), $layout->track(Axis::Horizontal, 1));
        $this->assertSame($layout->row(1), $layout->track(Axis::Vertical, 1));
    }

    public function testCellReturnsIntersectionOfColumnAndRow(): void
    {
        $layout = $this->solvedLayout();

        $this->assertSame(29.0, $layout->slot(1, 1)?->x);
        $this->assertSame(21.0, $layout->slot(1, 1)?->y);
        $this->assertSame(30.0, $layout->slot(1, 1)?->width);
        $this->assertSame(15.0, $layout->slot(1, 1)?->height);
    }

    public function testItemAndNamedAreaResolveById(): void
    {
        $layout = $this->solvedLayout();

        $this->assertSame('a', $layout->item('a')?->id);
        $this->assertSame(0, $layout->item('a')?->column);
        $this->assertSame(0, $layout->item('a')?->row);
        $this->assertSame(1, $layout->item('d')?->column);
        $this->assertSame(1, $layout->item('d')?->row);

        $this->assertSame($layout->item('b')?->frame, $layout->namedArea('b'));
        $this->assertSame(29.0, $layout->namedArea('b')?->x);
    }

    public function testCellsReturnsEveryPlacedItem(): void
    {
        $layout = $this->solvedLayout();

        $this->assertSame(4, \count($layout->slots()));
        $this->assertSame('a', $layout->slots()[0]->id);
    }

    public function testFrameOfDelegatesToPlacedTree(): void
    {
        $layout = $this->solvedLayout();

        $this->assertInstanceOf(PlacedTree::class, $layout->result);
        $this->assertSame(4.0, $layout->frameOf('c')?->x);
        $this->assertSame(21.0, $layout->frameOf('c')?->y);
        $this->assertSame(20.0, $layout->frameOf('c')?->width);
        $this->assertSame(15.0, $layout->frameOf('c')?->height);
    }

    public function testQueryMethodsReturnNullForUnknownIndicesAndIds(): void
    {
        $layout = $this->solvedLayout();

        $this->assertNull($layout->column(9));
        $this->assertNull($layout->row(9));
        $this->assertNull($layout->slot(9, 9));
        $this->assertNull($layout->track(Axis::Horizontal, 9));
        $this->assertNull($layout->track(Axis::Vertical, 9));
        $this->assertNull($layout->item('missing'));
        $this->assertNull($layout->namedArea('missing'));
        $this->assertNull($layout->frameOf('missing'));
    }
}
