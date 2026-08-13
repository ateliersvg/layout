---
order: 10
---
# Getting Started

`atelier/layout` solves geometry before rendering. You describe boxes, tracks, text, padding, and constraints; it returns frames that any renderer can consume.

## Install

```bash
composer require atelier/layout
```

## Solve A Grid

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;

$layout = Grid::tracks('row', [
    TrackSize::fixed(48),
    TrackSize::fr(),
    TrackSize::fixed(48),
])
    ->gap(8)
    ->align(Alignment::Center, Alignment::Center)
    ->add(Frame::fixed('left', 32, 32))
    ->add(Frame::stretch('middle'), alignX: Alignment::Stretch)
    ->add(Frame::fixed('right', 32, 32));

$result = $layout->solve(new LayoutContext(), Rect::fromSize(240, 48));

$result->frameOf('middle');
```

## Solve Text

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;

$text = TextBlock::of('caption', 'Wrapped bottom label', 12)
    ->align(Alignment::Center, Alignment::End)
    ->layout(new LayoutContext(), new Rect(0, 0, 90, 48));

$text->lines;
$text->hasOverflow();
```

## Use Geometry Helpers

```php
use Atelier\Layout\Geometry\Bounds;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Connection\OrthogonalConnector;

$bounds = Bounds::expand(Bounds::of($a, $b, $c), Insets::all(12));
$connection = (new OrthogonalConnector())->connect($a, $b);
```

Geometry helpers are pure values: they do not render, mutate, or depend on SVG.

## Where To Go Next

| If you need to | Read |
|---|---|
| place things along one axis | [Stacks And Spacing](composition/stacks.md) |
| place things on two axes | [Grid](composition/grid.md) |
| position a child inside its box | [Alignment](composition/alignment.md) |
| share leftover space | [Distribution](composition/distribution.md) |
| attach a badge to a corner | [Overlays And Badges](composition/overlays.md) |
| fit an image or a ratio | [Fit](geometry/fit.md) |
| draw an arrow between two boxes | [Connections](connections.md) |
| wrap and measure a label | [Text And Inline Runs](text.md) |
| build lanes, an axis strip, or a key | [Tracks, Bands And Legends](tracks-bands-legends.md) |

## Demos

The scripts in `examples/` are executable, small, and print concrete numbers, which makes solved geometry easy to inspect.

```bash
php examples/composition-demo.php
php examples/connections-demo.php
php examples/composition-gallery.php
```

`composition-demo.php` combines a fixed canvas, an outer margin, grid padding, row and column tracks, a spanning title slot, bottom-aligned text, bounds around two solved items, and a link between them.

`connections-demo.php` isolates links between boxes, printing the source rect, the target rect, the connection points, the label point, and the final tangent for arrowheads.

`composition-gallery.php` writes a dashboard, an overlay board, and a connections board as SVG. They are deliberately renderer-light: the script asks `atelier/layout` for geometry, then writes plain SVG elements from the solved frames. Which part is layout and which part is rendering stays visible.

