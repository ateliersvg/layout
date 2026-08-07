<?php

declare(strict_types=1);

namespace Atelier\Layout\Text;

use Atelier\Layout\Geometry\Rect;

final readonly class TextLayout
{
    /**
     * @param list<TextLineLayout> $lines
     */
    public function __construct(
        public Rect $frame,
        public array $lines,
        public float $contentWidth,
        public float $contentHeight,
        public bool $overflowX,
        public bool $overflowY,
        public bool $truncated = false,
    ) {
    }

    public function hasOverflow(): bool
    {
        return $this->overflowX || $this->overflowY;
    }

    /**
     * Lines were dropped to honour a maxLines cap.
     *
     * Kept separate from overflow: overflow says the text did not fit, this
     * says the layout deliberately stopped. A consumer that wants an ellipsis
     * keys off this, not off overflow.
     */
    public function isTruncated(): bool
    {
        return $this->truncated;
    }

    public function firstBaseline(): ?float
    {
        return $this->lines[0]->baseline ?? null;
    }

    public function lastBaseline(): ?float
    {
        return $this->lines[\count($this->lines) - 1]->baseline ?? null;
    }
}
