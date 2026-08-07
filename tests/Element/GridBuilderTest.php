<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Element\GridBuilder;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Grid\GridItem;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GridBuilder::class)]
final class GridBuilderTest extends TestCase
{
    public function testBuilderProducesTheSameGeometryAsTheImmutableApi(): void
    {
        $ctx = new LayoutContext();
        $rect = Rect::fromSize(100, 100);

        $tracks = static fn (): array => [TrackSize::fr(), TrackSize::fr()];

        $built = GridBuilder::tracks('g', $tracks(), $tracks())
            ->gap(8)
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::preferred('a', 10, 10), columnSpan: 2)
            ->add(Frame::preferred('b', 10, 10))
            ->add(Frame::preferred('c', 10, 10))
            ->solve($ctx, $rect);

        $manual = (new Grid(
            'g',
            2,
            [
                new GridItem(Frame::preferred('a', 10, 10), 2),
                new GridItem(Frame::preferred('b', 10, 10)),
                new GridItem(Frame::preferred('c', 10, 10)),
            ],
            8.0,
            8.0,
            Alignment::Stretch,
            Alignment::Stretch,
            null,
            $tracks(),
            $tracks(),
        ))->solve($ctx, $rect);

        foreach (['a', 'b', 'c'] as $id) {
            self::assertEquals($manual->frameOf($id), $built->frameOf($id));
        }
    }

    public function testRejectsColumnSpanWiderThanTheGrid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GridBuilder::columns('g', 2)->add(Frame::preferred('a', 10, 10), columnSpan: 3);
    }

    public function testRowsDeclaresRowTracksAfterConstruction(): void
    {
        $ctx = new LayoutContext();
        $rect = Rect::fromSize(100, 100);

        $built = GridBuilder::columns('g', 2)
            ->rows([TrackSize::fixed(30), TrackSize::fixed(70)])
            ->add(Frame::stretch('a'))
            ->add(Frame::stretch('b'))
            ->add(Frame::stretch('c'))
            ->solve($ctx, $rect);

        // Fixed row tracks: the first row is 30 tall, the second starts at 30.
        self::assertSame(30.0, $built->frameOf('a')?->height);
        self::assertSame(30.0, $built->frameOf('b')?->height);
        self::assertSame(30.0, $built->frameOf('c')?->y);
        self::assertSame(70.0, $built->frameOf('c')?->height);
    }
}
