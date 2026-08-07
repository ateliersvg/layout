<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Value;

use Atelier\Layout\Geometry\Size;
use Atelier\Layout\Value\InsetSpec;
use Atelier\Layout\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Length::class)]
#[CoversClass(InsetSpec::class)]
final class InsetSpecTest extends TestCase
{
    public function testPercentInsetsResolvePerAxis(): void
    {
        $insets = InsetSpec::percent(4)->resolve(new Size(200, 400));

        $this->assertSame(16.0, $insets->top);
        $this->assertSame(8.0, $insets->right);
        $this->assertSame(16.0, $insets->bottom);
        $this->assertSame(8.0, $insets->left);
    }

    public function testZeroInsetsResolveToZeroOnEveryEdge(): void
    {
        $insets = InsetSpec::zero()->resolve(new Size(200, 400));

        $this->assertSame(0.0, $insets->top);
        $this->assertSame(0.0, $insets->right);
        $this->assertSame(0.0, $insets->bottom);
        $this->assertSame(0.0, $insets->left);
    }

    public function testPxInsetsResolveIndependentlyOfReferenceSize(): void
    {
        $insets = InsetSpec::px(10)->resolve(new Size(200, 400));

        $this->assertSame(10.0, $insets->top);
        $this->assertSame(10.0, $insets->right);
        $this->assertSame(10.0, $insets->bottom);
        $this->assertSame(10.0, $insets->left);
    }
}
