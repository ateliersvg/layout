---
order: 60
---
# Tracks, Bands And Legends

Three helpers for the recurring furniture of a chart or a board: parallel lanes, an edge strip, and a key. Each takes a rectangle and returns placed frames. None of them draws anything.

## Track Groups

`TrackGroup` divides a canvas into parallel lanes along one axis, with an optional header and footer reserved at the ends.

<div class="figure-grid">
<figure><img src="images/trackgroup-horizontal.svg" alt="A canvas divided into horizontal lanes with a header strip"><figcaption><code>horizontal()</code></figcaption></figure>
<figure><img src="images/trackgroup-vertical.svg" alt="A canvas divided into vertical lanes with a header strip"><figcaption><code>vertical()</code></figcaption></figure>
</div>

```php
use Atelier\Layout\Track\TrackGroup;
use Atelier\Layout\Value\InsetSpec;

$placed = TrackGroup::horizontal('swimlanes')
    ->padding(InsetSpec::px(12))
    ->gap(8)
    ->headerSize(28)
    ->equalTracks()
    ->addTrack('backlog')
    ->addTrack('doing')
    ->addTrack('done')
    ->place($canvas);
```

Three sizing policies decide how the lanes share the main axis:

| Policy | Behaviour |
|---|---|
| `equalTracks()` | every lane gets the same size |
| `contentSizedTracks()` | each lane takes its preferred size |
| `stretchedTracks(float $flex)` | lanes share the leftover space |

`addTrack()` accepts a `Dimension` or a preferred main size to override the group policy for one lane. Kanban columns, swimlanes, and Gantt rows are the usual consumers. When the lanes need two axes rather than one, reach for [Grid](composition/grid.md) instead.

## Edge Bands

`EdgeBand` reserves a strip along the top or bottom of a rectangle and returns both the strip and what remains.

<img src="images/edgeband.svg" alt="A rectangle split into a narrow strip along one edge and the remaining area">

```php
use Atelier\Layout\Band\EdgeBand;

$placed = EdgeBand::top('axis')
    ->bandSize(24)
    ->gap(8)
    ->place($available);
```

It answers the question every chart asks first: where does the axis go, and what is left for the plot. Splitting it out means the plot area is computed once, by one rule, instead of being open-coded next to each renderer.

## Legends

`LegendBlock` lays out entries made of a swatch and a label, in a column or a row.

<div class="figure-grid">
<figure><img src="images/legend-vertical.svg" alt="A column of legend entries, each a small swatch beside a label"><figcaption><code>vertical()</code></figcaption></figure>
<figure><img src="images/legend-horizontal.svg" alt="A row of legend entries, each a small swatch beside a label"><figcaption><code>horizontal()</code></figcaption></figure>
</div>

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Legend\LegendBlock;

$placed = LegendBlock::vertical('series')
    ->swatchSize(10, 10)
    ->labelGap(6)
    ->gap(4)
    ->align(Alignment::Start, Alignment::Start)
    ->add('revenue', labelWidth: 54, labelHeight: 12)
    ->add('cost', labelWidth: 38, labelHeight: 12)
    ->place($available);
```

Label widths are supplied by the caller, measured with whatever measurer the consumer trusts. The legend places; it does not measure text on your behalf. See [Text And Inline Runs](text.md) if you need that measurement first.

`gap()` separates entries, `labelGap()` separates a swatch from its own label. Two different rhythms, two different knobs.
