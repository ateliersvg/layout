<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Result;

use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\IntrinsicSize;
use Atelier\Layout\Result\PlacedNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlacedNode::class)]
final class PlacedNodeTest extends TestCase
{
    public function testFindReturnsSelfWhenIdMatches(): void
    {
        $node = $this->leaf('root', new Rect(0, 0, 10, 10));

        $this->assertSame($node, $node->find('root'));
    }

    public function testFindLocatesNestedChild(): void
    {
        $grandchild = $this->leaf('grandchild', new Rect(2, 2, 4, 4));
        $child = new PlacedNode('child', new Rect(1, 1, 8, 8), $this->measure(8, 8), [$grandchild]);
        $root = new PlacedNode('root', new Rect(0, 0, 10, 10), $this->measure(10, 10), [$child]);

        $this->assertSame($grandchild, $root->find('grandchild'));
        $this->assertSame($child, $root->find('child'));
    }

    public function testFindReturnsNullWhenAbsent(): void
    {
        $child = $this->leaf('child', new Rect(1, 1, 8, 8));
        $root = new PlacedNode('root', new Rect(0, 0, 10, 10), $this->measure(10, 10), [$child]);

        $this->assertNull($root->find('missing'));
    }

    public function testFrameOfReturnsMatchingFrameOrNull(): void
    {
        $child = $this->leaf('child', new Rect(1, 2, 3, 4));
        $root = new PlacedNode('root', new Rect(0, 0, 10, 10), $this->measure(10, 10), [$child]);

        $frame = $root->frameOf('child');
        $this->assertNotNull($frame);
        $this->assertSame(1.0, $frame->x);
        $this->assertSame(2.0, $frame->y);
        $this->assertSame(3.0, $frame->width);
        $this->assertSame(4.0, $frame->height);
        $this->assertNull($root->frameOf('missing'));
    }

    private function leaf(string $id, Rect $frame): PlacedNode
    {
        return new PlacedNode($id, $frame, new IntrinsicSize($frame->size()));
    }

    private function measure(float $width, float $height): IntrinsicSize
    {
        return new IntrinsicSize(new Size($width, $height));
    }

    public function testOverflowsReportsChildrenSpillingOutOfTheFrame(): void
    {
        $inside = new PlacedNode('inside', new Rect(10, 10, 20, 20), new IntrinsicSize(new Size(20, 20)));
        $spilling = new PlacedNode('spilling', new Rect(90, 10, 30, 20), new IntrinsicSize(new Size(30, 20)));

        $contained = new PlacedNode('root', new Rect(0, 0, 100, 100), new IntrinsicSize(new Size(100, 100)), [$inside]);
        $overflowing = new PlacedNode('root', new Rect(0, 0, 100, 100), new IntrinsicSize(new Size(100, 100)), [$inside, $spilling]);

        self::assertFalse($contained->overflows());
        self::assertSame([], $contained->overflowingChildren());

        self::assertTrue($overflowing->overflows());
        self::assertSame(['spilling'], array_map(
            static fn (PlacedNode $node): string => $node->id,
            $overflowing->overflowingChildren(),
        ));
    }

    public function testOverflowLooksAtDirectChildrenOnly(): void
    {
        $deep = new PlacedNode('deep', new Rect(200, 200, 10, 10), new IntrinsicSize(new Size(10, 10)));
        $child = new PlacedNode('child', new Rect(0, 0, 100, 100), new IntrinsicSize(new Size(100, 100)), [$deep]);
        $root = new PlacedNode('root', new Rect(0, 0, 100, 100), new IntrinsicSize(new Size(100, 100)), [$child]);

        // The grandchild is far outside, but that is the child's problem to
        // report, not the root's.
        self::assertFalse($root->overflows());
        self::assertTrue($child->overflows());
    }
}
