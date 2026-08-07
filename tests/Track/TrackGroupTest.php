<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Track;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Track\PlacedTrack;
use Atelier\Layout\Track\PlacedTrackGroup;
use Atelier\Layout\Track\TrackGroup;
use Atelier\Layout\Value\Dimension;
use Atelier\Layout\Value\DimensionKind;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TrackGroup::class)]
#[CoversClass(PlacedTrackGroup::class)]
#[CoversClass(PlacedTrack::class)]
#[CoversClass(Dimension::class)]
#[CoversClass(DimensionKind::class)]
#[CoversClass(Insets::class)]
#[CoversClass(Rect::class)]
#[CoversClass(InvalidArgumentException::class)]
final class TrackGroupTest extends TestCase
{
    public function testHorizontalEqualLanesResolveHeaderBodyAndFooterFrames(): void
    {
        $layout = TrackGroup::horizontal('board')
            ->gap(10)
            ->padding(InsetSpec::px(10))
            ->headerSize(20)
            ->footerSize(10)
            ->equalTracks()
            ->addTrack('todo')
            ->addTrack('doing')
            ->place(Rect::fromSize(300, 120));

        $todo = $layout->track('todo');
        $doing = $layout->track('doing');

        $this->assertNotNull($todo);
        $this->assertNotNull($doing);
        $this->assertSame(10.0, $todo->frame->x);
        $this->assertSame(10.0, $todo->frame->y);
        $this->assertSame(135.0, $todo->frame->width);
        $this->assertSame(100.0, $todo->frame->height);
        $this->assertSame(20.0, $todo->headerFrame->height);
        $this->assertSame(70.0, $todo->bodyFrame->height);
        $this->assertSame(10.0, $todo->footerFrame->height);
        $this->assertSame(155.0, $doing->frame->x);
        $this->assertSame(10.0, $doing->headerFrame->y);
        $this->assertSame(30.0, $doing->bodyFrame->y);
        $this->assertSame(100.0, $doing->footerFrame->y);
    }

    public function testVerticalContentSizedLanesUsePreferredMainSizes(): void
    {
        $layout = TrackGroup::vertical('timeline')
            ->gap(6)
            ->padding(InsetSpec::px(4))
            ->headerSize(24)
            ->footerSize(12)
            ->contentSizedTracks()
            ->addTrack('phase.a', preferredMainSize: 80)
            ->addTrack('phase.b', preferredMainSize: 50)
            ->place(Rect::fromSize(140, 200));

        $phaseA = $layout->track('phase.a');
        $phaseB = $layout->track('phase.b');

        $this->assertNotNull($phaseA);
        $this->assertNotNull($phaseB);
        $this->assertSame(4.0, $phaseA->frame->x);
        $this->assertSame(4.0, $phaseA->frame->y);
        $this->assertSame(132.0, $phaseA->frame->width);
        $this->assertSame(80.0, $phaseA->frame->height);
        $this->assertSame(24.0, $phaseA->headerFrame->width);
        $this->assertSame(96.0, $phaseA->bodyFrame->width);
        $this->assertSame(12.0, $phaseA->footerFrame->width);
        $this->assertSame(4.0, $phaseB->frame->x);
        $this->assertSame(90.0, $phaseB->frame->y);
        $this->assertSame(50.0, $phaseB->frame->height);
    }

    public function testStretchedLanesDistributeRemainingMainSpaceByFlex(): void
    {
        $layout = TrackGroup::horizontal('rail')
            ->stretchedTracks(1.0)
            ->addTrack('a')
            ->addTrack('b', Dimension::stretch(3.0))
            ->place(Rect::fromSize(80, 40));

        $a = $layout->track('a');
        $b = $layout->track('b');

        $this->assertNotNull($a);
        $this->assertNotNull($b);
        $this->assertSame(20.0, $a->frame->width);
        $this->assertSame(60.0, $b->frame->width);
        $this->assertNull($layout->track('missing'));
    }

    public function testRejectsNegativeHeaderSize(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TrackGroup::horizontal('bad')->headerSize(-1.0);
    }

    public function testRejectsNegativeFooterSize(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TrackGroup::horizontal('bad')->footerSize(-1.0);
    }

    public function testRejectsNegativeGap(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TrackGroup::horizontal('bad')->gap(-1);
    }

    public function testRejectsNegativeGapFloat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TrackGroup::horizontal('p')->gap(-1.0);
    }
}
