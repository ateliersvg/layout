---
order: 50
---
# Grid

`Grid` is track-based placement on two axes. You declare column and row tracks, then add children that occupy one or more slots.

<div class="figure-grid">
<figure><img src="../images/grid-2x2.svg" alt="A grid of two columns and two rows, each cell filled"><figcaption>two columns, two rows</figcaption></figure>
<figure><img src="../images/grid-3-columns.svg" alt="A grid of three equal columns in a single row"><figcaption>three columns</figcaption></figure>
</div>

```php
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;

$grid = Grid::tracks('items', [TrackSize::fr(), TrackSize::fr()])
    ->rows([TrackSize::fixed(40), TrackSize::fr()])
    ->gap(12)
    ->add($header, columnSpan: 2)
    ->add($left)
    ->add($right);

$solved = $grid->solve(new LayoutContext(), Rect::fromSize(400, 240));
```

`Grid::columns('id', 3)` is the short form when every column is an equal fraction.

## Track Sizes

| Constructor | Meaning |
|---|---|
| `TrackSize::fixed(float $px)` | an exact number of pixels |
| `TrackSize::fr(float $fraction = 1.0)` | a share of the space left after fixed and auto tracks |
| `TrackSize::auto()` | sized to the content it holds |

Fixed tracks are resolved first, auto tracks next, and fraction tracks share whatever remains in proportion to their fraction. A grid narrower than the sum of its fixed tracks does not shrink them.

## Spans

`add()` takes `columnSpan` and `rowSpan`. A spanning child covers the slots plus the gaps between them, so a title spanning two columns is wider than the two columns it covers by exactly one gap.

<div class="figure-grid">
<figure><img src="../images/grid-column-span.svg" alt="A grid where one cell stretches across two columns"><figcaption><code>columnSpan: 2</code></figcaption></figure>
<figure><img src="../images/grid-row-span.svg" alt="A grid where one cell stretches down across two rows"><figcaption><code>rowSpan: 2</code></figcaption></figure>
</div>

## Empty Slots

Children fill slots in the order they are added, left to right then top to bottom. There is no explicit slot addressing: to leave a hole, add a child that draws nothing, or reorder the tracks so the hole falls at the end.

<img src="../images/grid-dashed-slot.svg" alt="A grid with one slot left empty, outlined with a dashed border">

## Alignment Inside Slots

`align()` sets the alignment used by every slot; `add()` overrides it per item through `alignX` and `alignY`.

```php
use Atelier\Layout\Alignment;

Grid::tracks('items', [TrackSize::fr(), TrackSize::fr()])
    ->align(Alignment::Center, Alignment::Center)
    ->add($left)
    ->add($right, alignX: Alignment::End);
```

A child aligned `Stretch` fills its slot. A `Frame::fixed()` ignores `Stretch` and keeps its declared size.
