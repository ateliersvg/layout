<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Grid;

use Atelier\Layout\Element\Frame;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Grid\GridItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GridItem::class)]
final class GridItemTest extends TestCase
{
    public function testRejectsSpanBelowOne(): void
    {
        $node = Frame::fixed('a', 10, 10);

        $this->expectException(InvalidArgumentException::class);

        new GridItem($node, columnSpan: 0);
    }
}
