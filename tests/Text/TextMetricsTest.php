<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Text;

use Atelier\Layout\Text\TextMetrics;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextMetrics::class)]
final class TextMetricsTest extends TestCase
{
    public function testExposesWidthHeightAndAscent(): void
    {
        $metrics = new TextMetrics(48.5, 14.4, 9.6);

        $this->assertSame(48.5, $metrics->width);
        $this->assertSame(14.4, $metrics->height);
        $this->assertSame(9.6, $metrics->ascent);
    }
}
