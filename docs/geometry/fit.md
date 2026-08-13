---
order: 30
---
# Fit

`Fit` places a source of known size inside a target rectangle and returns the rectangle it should occupy. It is the geometry behind image placement, viewBox positioning, and fixed-aspect tiles.

<div class="figure-grid">
<figure><img src="../images/fit-contain.svg" alt="A wide source scaled down until it fits entirely inside the target, leaving bands"><figcaption><code>Contain</code></figcaption></figure>
<figure><img src="../images/fit-cover.svg" alt="A wide source scaled up until it covers the target, overflowing on two sides"><figcaption><code>Cover</code></figcaption></figure>
<figure><img src="../images/fit-fill.svg" alt="A source stretched on both axes to match the target exactly"><figcaption><code>Fill</code></figcaption></figure>
<figure><img src="../images/fit-scale-down.svg" alt="A small source kept at its natural size inside a larger target"><figcaption><code>ScaleDown</code></figcaption></figure>
</div>

```php
use Atelier\Layout\Anchor;
use Atelier\Layout\Fit\Fit;
use Atelier\Layout\Fit\FitMode;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;

$placed = Fit::rect(
    new Size(1600, 900),
    Rect::fromSize(320, 320),
    FitMode::Contain,
    Anchor::Center,
);
```

## The Five Modes

| Mode | Aspect ratio | Result |
|---|---|---|
| `Contain` | kept | the largest size that fits entirely inside the target |
| `Cover` | kept | the smallest size that covers the target entirely |
| `Fill` | broken | exactly the target, stretched on both axes |
| `None` | kept | the source size, untouched |
| `ScaleDown` | kept | `None` when the source already fits, `Contain` otherwise |

`Contain` never overflows and usually leaves empty bands. `Cover` never leaves bands and usually overflows. Which one you want depends on whether the empty space or the lost content is the lesser problem.

`ScaleDown` is the one to reach for when a source may be either smaller or larger than its target and should never be enlarged: thumbnails, logos, icons of mixed provenance.

## Anchoring The Result

The anchor decides where the fitted rectangle sits when it does not fill the target, and which part survives when it overflows.

```php
Fit::rect($source, $target, FitMode::Cover, Anchor::TopCenter);
```

With `Cover`, a `TopCenter` anchor keeps the top of the source visible and crops the bottom. That is usually what portraits want and what landscapes do not.

## Aspect Frames

`AspectFrame` is the reusable form of the same question: reserve a rectangle of a given ratio inside available space.

<img src="../images/aspectframe.svg" alt="A rectangle of fixed proportion centered inside a larger available area">

```php
use Atelier\Layout\Aspect\AspectFrame;
use Atelier\Layout\Value\InsetSpec;

$frame = AspectFrame::of('media', 16, 9)
    ->padding(InsetSpec::px(8))
    ->fitMode(FitMode::Contain)
    ->anchor(Anchor::Center)
    ->place($available);
```

Use it for media slots, chart canvases, and any tile whose proportion is part of the design rather than a consequence of its content. When the ratio should come from the tracks instead of from the tile, size it with [Grid](../composition/grid.md).
