<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Result;

use Atelier\Layout\Anchor;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Stack;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutSolver;
use Atelier\Layout\Result\PlacedTree;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LayoutSolver::class)]
#[CoversClass(PlacedTree::class)]
final class LayoutSolverTest extends TestCase
{
    public function testSolverReturnsQueryableResult(): void
    {
        $layout = Stack::row('root')
            ->add(Frame::fixed('item', 20, 10));

        $result = (new LayoutSolver())->solve($layout, Rect::fromSize(100, 50));

        $this->assertInstanceOf(PlacedTree::class, $result);
        $this->assertSame(20.0, $result->frameOf('item')?->width);
        $this->assertSame(10.0, $result->anchorOf('item', Anchor::BottomRight)?->y);
    }
}
