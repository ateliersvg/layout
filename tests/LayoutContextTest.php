<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests;

use Atelier\Layout\LayoutContext;
use Atelier\Layout\Text\CharWidthTextMeasurer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LayoutContext::class)]
final class LayoutContextTest extends TestCase
{
    public function testDefaultsToCharWidthMeasurerAndNoSnapping(): void
    {
        $context = new LayoutContext();

        $this->assertInstanceOf(CharWidthTextMeasurer::class, $context->textMeasurer);
        $this->assertNull($context->snapStep);
        $this->assertSame(5.3, $context->snap(5.3));
    }

    public function testSnapRoundsToNearestStep(): void
    {
        $context = new LayoutContext(snapStep: 5.0);

        $this->assertSame(15.0, $context->snap(13.0));
        $this->assertSame(10.0, $context->snap(12.4));
    }

    public function testNonPositiveSnapStepReturnsValueUnchanged(): void
    {
        $context = new LayoutContext(snapStep: 0.0);

        $this->assertSame(7.25, $context->snap(7.25));
    }
}
