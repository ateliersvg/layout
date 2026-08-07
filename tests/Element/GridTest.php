<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Axis;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Grid\GridItem;
use Atelier\Layout\Grid\GridSlot;
use Atelier\Layout\Grid\PlacedGrid;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Result\PlacedTree;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Grid::class)]
#[CoversClass(GridItem::class)]
#[CoversClass(GridSlot::class)]
#[CoversClass(PlacedGrid::class)]
#[CoversClass(Frame::class)]
#[CoversClass(TrackSize::class)]
#[CoversClass(PlacedTree::class)]
#[CoversClass(InvalidArgumentException::class)]
final class GridTest extends TestCase
{
    public function testGridPlacesChildrenIntoTracks(): void
    {
        $grid = Grid::columns('grid', 2)
            ->padding(InsetSpec::px(10))
            ->gap(10)
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::fixed('a', 10, 10))
            ->add(Frame::fixed('b', 10, 10))
            ->add(Frame::fixed('c', 10, 10));

        $result = $grid->solve(new LayoutContext(), Rect::fromSize(120, 80));

        $this->assertSame(10.0, $result->frameOf('a')?->x);
        $this->assertSame(10.0, $result->frameOf('a')?->y);
        $this->assertSame(65.0, $result->frameOf('b')?->x);
        $this->assertSame(45.0, $result->frameOf('c')?->y);
        $this->assertSame(45.0, $result->frameOf('a')?->width);
    }

    public function testGridCanCenterChildrenInsideCells(): void
    {
        $grid = Grid::columns('grid', 1)
            ->align(Alignment::Center, Alignment::Center)
            ->add(Frame::fixed('a', 20, 10));

        $result = $grid->solve(new LayoutContext(), Rect::fromSize(100, 50));

        $this->assertSame(40.0, $result->frameOf('a')?->x);
        $this->assertSame(20.0, $result->frameOf('a')?->y);
        $this->assertSame(20.0, $result->frameOf('a')?->width);
    }

    public function testGridSupportsFixedAutoAndFractionTracks(): void
    {
        $grid = Grid::tracks('grid', [
            TrackSize::fixed(20),
            TrackSize::auto(),
            TrackSize::fr(2),
        ])
            ->gap(10)
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::fixed('a', 10, 10))
            ->add(Frame::fixed('b', 30, 10))
            ->add(Frame::fixed('c', 10, 10));

        $result = $grid->solve(new LayoutContext(), Rect::fromSize(160, 40));

        $this->assertSame(20.0, $result->frameOf('a')?->width);
        $this->assertSame(30.0, $result->frameOf('b')?->width);
        $this->assertSame(90.0, $result->frameOf('c')?->width);
        $this->assertSame(30.0, $result->frameOf('b')?->x);
        $this->assertSame(70.0, $result->frameOf('c')?->x);
    }

    public function testGridSupportsRowTracks(): void
    {
        $grid = Grid::tracks('grid', [TrackSize::fr(), TrackSize::fr()], [TrackSize::fixed(20), TrackSize::fr(2)])
            ->gap(10)
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::fixed('a', 10, 10))
            ->add(Frame::fixed('b', 10, 10))
            ->add(Frame::fixed('c', 10, 10));

        $result = $grid->solve(new LayoutContext(), Rect::fromSize(110, 100));

        $this->assertSame(20.0, $result->frameOf('a')?->height);
        $this->assertSame(70.0, $result->frameOf('c')?->height);
        $this->assertSame(30.0, $result->frameOf('c')?->y);
    }

    public function testGridSupportsSpanningItems(): void
    {
        $grid = Grid::columns('grid', 3)
            ->gap(10)
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::fixed('hero', 10, 10), columnSpan: 2)
            ->add(Frame::fixed('side', 10, 10))
            ->add(Frame::fixed('footer', 10, 10), columnSpan: 3);

        $result = $grid->solve(new LayoutContext(), Rect::fromSize(320, 110));

        $this->assertSame(210.0, $result->frameOf('hero')?->width);
        $this->assertSame(0.0, $result->frameOf('hero')?->x);
        $this->assertSame(220.0, $result->frameOf('side')?->x);
        $this->assertSame(320.0, $result->frameOf('footer')?->width);
        $this->assertSame(60.0, $result->frameOf('footer')?->y);
    }

    public function testGridExposesTrackAndCellMetadata(): void
    {
        $grid = Grid::tracks('grid', [TrackSize::fixed(20), TrackSize::fixed(30)], [TrackSize::fixed(10), TrackSize::fixed(15)])
            ->gap(5, 7)
            ->padding(InsetSpec::px(4))
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::fixed('a', 10, 10))
            ->add(Frame::fixed('b', 10, 10))
            ->add(Frame::fixed('c', 10, 10))
            ->add(Frame::fixed('d', 10, 10));

        $layout = $grid->build()->layout(new LayoutContext(), Rect::fromSize(100, 60));

        $this->assertSame(4.0, $layout->column(0)?->x);
        $this->assertSame(20.0, $layout->column(0)?->width);
        $this->assertSame(29.0, $layout->column(1)?->x);
        $this->assertSame(30.0, $layout->column(1)?->width);
        $this->assertSame(4.0, $layout->row(0)?->y);
        $this->assertSame(10.0, $layout->row(0)?->height);
        $this->assertSame(21.0, $layout->row(1)?->y);
        $this->assertSame(15.0, $layout->row(1)?->height);
        $this->assertSame($layout->column(1), $layout->track(Axis::Horizontal, 1));
        $this->assertSame($layout->row(1), $layout->track(Axis::Vertical, 1));
        $this->assertSame(29.0, $layout->slot(1, 1)?->x);
        $this->assertSame(21.0, $layout->slot(1, 1)?->y);
        $this->assertSame(30.0, $layout->slot(1, 1)?->width);
        $this->assertSame(15.0, $layout->slot(1, 1)?->height);
        $this->assertSame('a', $layout->item('a')?->id);
        $this->assertSame(0, $layout->item('a')?->column);
        $this->assertSame(1, $layout->item('d')?->row);
        $this->assertSame(29.0, $layout->namedArea('b')?->x);
        $this->assertSame(4, \count($layout->slots()));
        $this->assertSame(4.0, $layout->frameOf('c')?->x);
        $this->assertSame(21.0, $layout->frameOf('c')?->y);
        $this->assertSame(20.0, $layout->frameOf('c')?->width);
        $this->assertSame(15.0, $layout->frameOf('c')?->height);
    }

    public function testGridSlotMetadataRemainsStableAfterInsertion(): void
    {
        $grid = Grid::columns('grid', 2)
            ->gap(5)
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::fixed('first', 10, 10))
            ->add(Frame::fixed('second', 10, 10));

        $before = $grid->build()->layout(new LayoutContext(), Rect::fromSize(100, 50));

        $grid = $grid->add(Frame::fixed('inserted', 10, 10));

        $after = $grid->build()->layout(new LayoutContext(), Rect::fromSize(100, 50));

        $this->assertSame('first', $before->slots()[0]->id);
        $this->assertSame('first', $after->slots()[0]->id);
        $this->assertSame('inserted', $after->slots()[2]->id);
        $this->assertSame(0, $after->item('first')?->column);
        $this->assertSame(1, $after->item('second')?->column);
        $this->assertSame(0.0, $after->namedArea('inserted')?->x);
    }

    public function testGridSupportsPerItemAlignmentOverrides(): void
    {
        $grid = Grid::columns('grid', 1)
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::fixed('a', 20, 10), alignX: Alignment::End, alignY: Alignment::Center);

        $result = $grid->solve(new LayoutContext(), Rect::fromSize(100, 50));

        $this->assertSame(80.0, $result->frameOf('a')?->x);
        $this->assertSame(20.0, $result->frameOf('a')?->y);
        $this->assertSame(20.0, $result->frameOf('a')?->width);
        $this->assertSame(10.0, $result->frameOf('a')?->height);
    }

    public function testGridRejectsColumnSpanLargerThanColumnCount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Grid::columns('grid', 2)->add(Frame::fixed('a', 10, 10), columnSpan: 3);
    }

    public function testGridMeasureDistributesFractionTracksWithinConstraints(): void
    {
        $grid = Grid::columns('grid', 2)
            ->gap(10)
            ->padding(InsetSpec::px(5))
            ->add(Frame::fixed('a', 10, 10))
            ->add(Frame::fixed('b', 10, 10))
            ->add(Frame::fixed('c', 10, 10));

        $measure = $grid->measure(new LayoutContext(), new BoxConstraints(maxWidth: 120, maxHeight: 120));

        $this->assertSame(120.0, $measure->size->width);
        $this->assertSame(120.0, $measure->size->height);
    }

    public function testGridMeasureUsesPreferredTrackSpaceWhenUnconstrained(): void
    {
        $grid = Grid::columns('grid', 2)
            ->add(Frame::fixed('a', 30, 20))
            ->add(Frame::fixed('b', 40, 10));

        $measure = $grid->measure(new LayoutContext(), BoxConstraints::unconstrained());

        $this->assertSame(70.0, $measure->size->width);
        $this->assertSame(20.0, $measure->size->height);
    }

    public function testGridRowsOverrideRowTrackSizing(): void
    {
        $grid = Grid::columns('grid', 1)
            ->rows([TrackSize::fixed(30)])
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::fixed('a', 10, 10));

        $result = $grid->solve(new LayoutContext(), Rect::fromSize(100, 100));

        $this->assertSame(30.0, $result->frameOf('a')?->height);
    }

    public function testGridExposesItsId(): void
    {
        $this->assertSame('grid', Grid::columns('grid', 2)->id());
    }

    public function testTheBuiltGridCarriesTheIdThroughBuild(): void
    {
        // The entry point returns a builder, so the node's own id()
        // is only reachable past build().
        self::assertSame('toolbar', Grid::columns('toolbar', 2)->build()->id());
    }
}
