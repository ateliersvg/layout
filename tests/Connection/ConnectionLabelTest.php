<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Connection;

use Atelier\Layout\Connection\ConnectionLabel;
use Atelier\Layout\Connection\ConnectionLabelPlacement;
use Atelier\Layout\Connection\OrthogonalConnector;
use Atelier\Layout\Connection\PlacedConnectionLabel;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\RectIndex;
use Atelier\Layout\Geometry\Size;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConnectionLabel::class)]
#[CoversClass(ConnectionLabelPlacement::class)]
#[CoversClass(PlacedConnectionLabel::class)]
final class ConnectionLabelTest extends TestCase
{
    public function testCentersLabelOnMiddleSegmentByDefault(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 35, 50, 30),
        );

        $label = ConnectionLabel::for($connection)
            ->size(new Size(20, 10))
            ->place();

        $this->assertSame(ConnectionLabelPlacement::Centered, $label->placement);
        $this->assertSame(1, $label->segmentIndex);
        $this->assertSame(65.0, $label->frame->x);
        $this->assertSame(37.5, $label->frame->y);
        $this->assertSame(75.0, $label->anchor->x);
        $this->assertSame(42.5, $label->anchor->y);
    }

    public function testOffsetsAboveVerticalSegmentWithPadding(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 35, 50, 30),
        );

        $label = ConnectionLabel::for($connection)
            ->size(new Size(20, 10))
            ->padding(Insets::all(6))
            ->placement(ConnectionLabelPlacement::Above)
            ->place();

        $this->assertSame(1, $label->segmentIndex);
        $this->assertSame(49.0, $label->frame->x);
        $this->assertSame(37.5, $label->frame->y);
        $this->assertSame(59.0, $label->anchor->x);
        $this->assertSame(42.5, $label->anchor->y);
    }

    public function testAnchorsToTheFirstSegmentWhenRequested(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 35, 50, 30),
        );

        $label = ConnectionLabel::for($connection)
            ->size(new Size(20, 10))
            ->placement(ConnectionLabelPlacement::EndpointStart)
            ->place();

        $this->assertSame(0, $label->segmentIndex);
        $this->assertSame(40.0, $label->frame->x);
        $this->assertSame(30.0, $label->frame->y);
        $this->assertSame(50.0, $label->anchor->x);
        $this->assertSame(35.0, $label->anchor->y);
    }

    public function testFallsBackToRequestedPlacementWhenEveryCandidateIsOccupied(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 20, 50, 30),
        );
        $occupied = RectIndex::from([
            'wall' => new Rect(-1000, -1000, 4000, 4000),
        ]);

        $label = ConnectionLabel::for($connection)
            ->size(new Size(20, 10))
            ->avoid($occupied)
            ->place();

        $this->assertSame(ConnectionLabelPlacement::Centered, $label->placement);
        $this->assertSame(0, $label->segmentIndex);
        $this->assertSame(65.0, $label->frame->x);
        $this->assertSame(30.0, $label->frame->y);
        $this->assertSame(75.0, $label->anchor->x);
        $this->assertSame(35.0, $label->anchor->y);
    }

    public function testAvoidsOccupiedFramesByTryingAlternativePlacements(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 20, 50, 30),
        );
        $occupied = RectIndex::from([
            'edge.label' => new Rect(72, 32, 4, 4),
        ]);

        $label = ConnectionLabel::for($connection)
            ->size(new Size(20, 10))
            ->padding(Insets::all(6))
            ->avoid($occupied)
            ->place();

        $this->assertSame(ConnectionLabelPlacement::Above, $label->placement);
        $this->assertSame(65.0, $label->frame->x);
        $this->assertSame(19.0, $label->frame->y);
        $this->assertSame(75.0, $label->anchor->x);
        $this->assertSame(24.0, $label->anchor->y);
    }
}
