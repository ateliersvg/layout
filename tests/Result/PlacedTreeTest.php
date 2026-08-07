<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Result;

use Atelier\Layout\Anchor;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\IntrinsicSize;
use Atelier\Layout\Result\PlacedNode;
use Atelier\Layout\Result\PlacedTree;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlacedTree::class)]
final class PlacedTreeTest extends TestCase
{
    public function testNodeFrameAndAnchorQueries(): void
    {
        $child = new PlacedNode('child', new Rect(10, 20, 40, 30), new IntrinsicSize(new Size(40, 30)));
        $root = new PlacedNode('root', new Rect(0, 0, 100, 100), new IntrinsicSize(new Size(100, 100)), [$child]);
        $result = new PlacedTree($root);

        $this->assertSame($child, $result->node('child'));
        $this->assertNull($result->node('missing'));

        $frame = $result->frameOf('child');
        $this->assertNotNull($frame);
        $this->assertSame(10.0, $frame->x);

        $anchor = $result->anchorOf('child', Anchor::Center);
        $this->assertNotNull($anchor);
        $this->assertSame(30.0, $anchor->x);
        $this->assertSame(35.0, $anchor->y);
        $this->assertNull($result->anchorOf('missing', Anchor::Center));
    }
}
