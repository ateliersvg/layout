<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Connection;

use Atelier\Layout\Connection\ConnectionEndpointBadge;
use Atelier\Layout\Connection\ConnectionEndpointBadgePlacement;
use Atelier\Layout\Connection\OrthogonalConnector;
use Atelier\Layout\Connection\PlacedConnectionEndpointBadge;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\RectIndex;
use Atelier\Layout\Geometry\Size;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConnectionEndpointBadge::class)]
#[CoversClass(ConnectionEndpointBadgePlacement::class)]
#[CoversClass(PlacedConnectionEndpointBadge::class)]
final class ConnectionEndpointBadgeTest extends TestCase
{
    public function testPlacesStartBadgeBeforeHorizontalConnection(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 20, 50, 30),
        );

        $badge = ConnectionEndpointBadge::for($connection, ConnectionEndpointBadgePlacement::Start)
            ->size(new Size(20, 10))
            ->padding(Insets::all(6))
            ->place();

        $this->assertSame(0, $badge->segmentIndex);
        $this->assertSame(24.0, $badge->frame->x);
        $this->assertSame(30.0, $badge->frame->y);
        $this->assertSame(34.0, $badge->anchor->x);
        $this->assertSame(35.0, $badge->anchor->y);
    }

    public function testPlacesEndBadgeAfterVerticalConnection(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(10, 100, 40, 30),
        );

        $badge = ConnectionEndpointBadge::for($connection, ConnectionEndpointBadgePlacement::End)
            ->size(new Size(20, 10))
            ->padding(Insets::all(6))
            ->place();

        $this->assertSame(0, $badge->segmentIndex);
        $this->assertSame(20.0, $badge->frame->x);
        $this->assertSame(106.0, $badge->frame->y);
        $this->assertSame(30.0, $badge->anchor->x);
        $this->assertSame(111.0, $badge->anchor->y);
    }

    public function testPlacesStartBadgeBeforeVerticalConnection(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(10, 100, 40, 30),
        );

        $badge = ConnectionEndpointBadge::for($connection, ConnectionEndpointBadgePlacement::Start)
            ->size(new Size(20, 10))
            ->padding(Insets::all(6))
            ->place();

        $this->assertSame(0, $badge->segmentIndex);
        $this->assertSame(20.0, $badge->frame->x);
        $this->assertSame(34.0, $badge->frame->y);
        $this->assertSame(30.0, $badge->anchor->x);
        $this->assertSame(39.0, $badge->anchor->y);
    }

    public function testPlacesEndBadgeAfterHorizontalConnection(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 20, 50, 30),
        );

        $badge = ConnectionEndpointBadge::for($connection, ConnectionEndpointBadgePlacement::End)
            ->size(new Size(20, 10))
            ->padding(Insets::all(6))
            ->place();

        $this->assertSame(0, $badge->segmentIndex);
        $this->assertSame(106.0, $badge->frame->x);
        $this->assertSame(30.0, $badge->frame->y);
        $this->assertSame(116.0, $badge->anchor->x);
        $this->assertSame(35.0, $badge->anchor->y);
    }

    public function testZeroSizedBadgeUsesSingleAnchorWithoutStepping(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 20, 50, 30),
        );

        $badge = ConnectionEndpointBadge::for($connection, ConnectionEndpointBadgePlacement::Start)->place();

        $this->assertSame(0, $badge->segmentIndex);
        $this->assertSame(50.0, $badge->frame->x);
        $this->assertSame(35.0, $badge->frame->y);
        $this->assertSame(0.0, $badge->frame->width);
        $this->assertSame(0.0, $badge->frame->height);
    }

    public function testFallsBackToBaseAnchorWhenEveryCandidateIsOccupied(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 20, 50, 30),
        );
        $occupied = RectIndex::from([
            'wall' => new Rect(-1000, -1000, 4000, 4000),
        ]);

        $badge = ConnectionEndpointBadge::for($connection, ConnectionEndpointBadgePlacement::Start)
            ->size(new Size(20, 10))
            ->padding(Insets::all(6))
            ->avoid($occupied)
            ->place();

        $this->assertSame(0, $badge->segmentIndex);
        $this->assertSame(24.0, $badge->frame->x);
        $this->assertSame(30.0, $badge->frame->y);
        $this->assertSame(34.0, $badge->anchor->x);
        $this->assertSame(35.0, $badge->anchor->y);
    }

    public function testEndPlacementFallsBackToBaseAnchorWhenEveryCandidateIsOccupied(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 20, 50, 30),
        );
        $occupied = RectIndex::from([
            'wall' => new Rect(-1000, -1000, 4000, 4000),
        ]);

        $badge = ConnectionEndpointBadge::for($connection, ConnectionEndpointBadgePlacement::End)
            ->size(new Size(20, 10))
            ->padding(Insets::all(6))
            ->avoid($occupied)
            ->place();

        $this->assertSame(0, $badge->segmentIndex);
        $this->assertSame(106.0, $badge->frame->x);
        $this->assertSame(30.0, $badge->frame->y);
        $this->assertSame(116.0, $badge->anchor->x);
        $this->assertSame(35.0, $badge->anchor->y);
    }

    public function testAvoidsOccupiedEndpointBadgeFramesByPushingFurtherAway(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 20, 50, 30),
        );
        $occupied = RectIndex::from([
            'badge.start' => new Rect(24, 30, 20, 10),
        ]);

        $badge = ConnectionEndpointBadge::for($connection, ConnectionEndpointBadgePlacement::Start)
            ->size(new Size(20, 10))
            ->padding(Insets::all(6))
            ->avoid($occupied)
            ->place();

        $this->assertSame(0, $badge->segmentIndex);
        $this->assertSame(-8.0, $badge->frame->x);
        $this->assertSame(30.0, $badge->frame->y);
        $this->assertSame(2.0, $badge->anchor->x);
        $this->assertSame(35.0, $badge->anchor->y);
    }
}
