<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Anchor;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Overlay;
use Atelier\Layout\Element\OverlayBuilder;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OverlayBuilder::class)]
final class OverlayBuilderTest extends TestCase
{
    public function testBuilderProducesTheSameGeometryAsTheImmutableApi(): void
    {
        $ctx = new LayoutContext();
        $rect = Rect::fromSize(100, 100);

        $built = OverlayBuilder::of('o')
            ->add(Frame::preferred('base', 60, 60))
            ->add(Frame::fixed('badge', 20, 20), Anchor::TopRight, Anchor::TopRight, -6, 6)
            ->solve($ctx, $rect);

        $manual = (new Overlay('o', [
            ['node' => Frame::preferred('base', 60, 60), 'subject' => Anchor::Center, 'target' => Anchor::Center, 'offsetX' => 0.0, 'offsetY' => 0.0],
            ['node' => Frame::fixed('badge', 20, 20), 'subject' => Anchor::TopRight, 'target' => Anchor::TopRight, 'offsetX' => -6.0, 'offsetY' => 6.0],
        ]))->solve($ctx, $rect);

        self::assertEquals($manual->frameOf('base'), $built->frameOf('base'));
        self::assertEquals($manual->frameOf('badge'), $built->frameOf('badge'));
    }
}
