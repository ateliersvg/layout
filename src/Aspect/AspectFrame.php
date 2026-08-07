<?php

declare(strict_types=1);

namespace Atelier\Layout\Aspect;

use Atelier\Layout\Anchor;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Fit\Fit;
use Atelier\Layout\Fit\FitMode;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\Value\InsetSpec;

final readonly class AspectFrame
{
    private function __construct(
        private string $id,
        private float $ratioWidth,
        private float $ratioHeight,
        private ?InsetSpec $padding = null,
        private FitMode $mode = FitMode::Contain,
        private Anchor $anchor = Anchor::Center,
    ) {
        if ($ratioWidth <= 0.0 || $ratioHeight <= 0.0) {
            throw new InvalidArgumentException('Aspect frame ratio must be positive.');
        }
    }

    public static function of(string $id, float $ratioWidth, float $ratioHeight): self
    {
        return new self($id, $ratioWidth, $ratioHeight);
    }

    public function padding(InsetSpec $padding): self
    {
        return new self($this->id, $this->ratioWidth, $this->ratioHeight, $padding, $this->mode, $this->anchor);
    }

    public function fitMode(FitMode $mode): self
    {
        return new self($this->id, $this->ratioWidth, $this->ratioHeight, $this->padding, $mode, $this->anchor);
    }

    public function anchor(Anchor $anchor): self
    {
        return new self($this->id, $this->ratioWidth, $this->ratioHeight, $this->padding, $this->mode, $anchor);
    }

    public function place(Rect $available): PlacedAspectFrame
    {
        $content = $available->inset($this->paddingSpec()->resolve($available->size()));
        $fitted = Fit::rect(new Size($this->ratioWidth, $this->ratioHeight), $content, $this->mode, $this->anchor);

        return new PlacedAspectFrame(
            $this->id,
            $available,
            $content,
            $fitted,
            $fitted->x < $content->x || $fitted->right() > $content->right(),
            $fitted->y < $content->y || $fitted->bottom() > $content->bottom(),
        );
    }

    private function paddingSpec(): InsetSpec
    {
        return $this->padding ?? InsetSpec::zero();
    }
}
