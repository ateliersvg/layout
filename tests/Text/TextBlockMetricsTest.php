<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Text;

use Atelier\Layout\Text\TextBlockMetrics;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextBlockMetrics::class)]
final class TextBlockMetricsTest extends TestCase
{
    public function testExposesLinesDimensionsAndBaselines(): void
    {
        $metrics = new TextBlockMetrics(['first', 'second'], 60.0, 28.8, 9.6, 24.0);

        $this->assertSame(['first', 'second'], $metrics->lines);
        $this->assertSame(60.0, $metrics->width);
        $this->assertSame(28.8, $metrics->height);
        $this->assertSame(9.6, $metrics->firstBaseline);
        $this->assertSame(24.0, $metrics->lastBaseline);
    }
}
