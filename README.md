<h1 align="center">Atelier Layout</h1>

<p align="center">Spatial primitives that answer where things go, and return geometry rather than markup.</p>

<p align="center">
  <img alt="PHP Version" src="https://img.shields.io/badge/PHP-8.3%2B-f4a34b?labelColor=14141c">
  <img alt="Tests" src="https://img.shields.io/github/actions/workflow/status/ateliersvg/layout/CI.yml?branch=main&label=Tests&labelColor=14141c&color=f4a34b">
  <img alt="PHPUnit" src="https://img.shields.io/badge/PHPUnit-13-f4a34b?labelColor=14141c">
  <img alt="PHPStan" src="https://img.shields.io/badge/PHPStan-max-f4a34b?labelColor=14141c">
  <img alt="Stable" src="https://img.shields.io/github/v/release/ateliersvg/layout?label=Stable&labelColor=14141c&color=f4a34b">
  <img alt="License" src="https://img.shields.io/github/license/ateliersvg/layout?label=License&labelColor=14141c&color=f4a34b">
</p>

Describe boxes, tracks, text, padding and constraints; get back frames any renderer can consume.
Nothing here emits SVG, HTML, or a DOM, and nothing here names a consumer.

```php
$result = Grid::tracks('row', [TrackSize::fixed(48), TrackSize::fr(), TrackSize::fixed(48)])
    ->gap(8)
    ->add(Frame::fixed('left', 32, 32))
    ->add(Frame::stretch('middle'))
    ->add(Frame::fixed('right', 32, 32))
    ->solve(new LayoutContext(), Rect::fromSize(240, 48));

$result->frameOf('middle');   // Rect(56, 0, 128, 48)
```

Separating the geometry from the drawing is what lets the same solver serve a chart legend, a
diagram node, and a page header. It is the layer `atelier/diagram` sits on. Backed by an
extensive test suite and PHPStan at its highest level.

**[Composition](#composition) · [Geometry](#geometry) · [Text](#text) ·
[Links between boxes](#links-between-boxes) · [Charts and boards](#charts-and-boards) ·
[Documentation](#documentation)**

## Installation

```bash
composer require atelier/layout
```

Requires PHP 8.3 or later. No dependencies.

## Quick start

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;

$text = TextBlock::of('caption', 'A label that wraps', 12)
    ->align(Alignment::Center, Alignment::End)
    ->layout(new LayoutContext(), new Rect(0, 0, 90, 48));

$text->lines;          // one frame and one baseline per line
$text->hasOverflow();  // reported, never hidden
```

See [Getting started](docs/getting-started.md).

## Composition

Six primitives, each answering one question about where a child ends up: stacks along one axis,
grids on two, alignment inside a box, distribution of what is left over, groups that carry a
bounding box, and overlays anchored to a corner.

Track sizes are fixed, content-sized, or flexible, and a grid auto-flows past its column count.
See [Stacks](docs/composition/stacks.md) and [Grid](docs/composition/grid.md).

## Geometry

Immutable values with no behaviour beyond their own maths: `Rect`, `Point`, `Size`, `Insets`,
`Bounds`, `Circle`, `BoxModel`, and a `RectIndex` for collision and occupancy queries.

Box constraints travel down a node tree during measurement and sizes travel back up, which is
what makes a layout solvable in one pass. See [Geometry](docs/geometry/overview.md).

## Text

Measurement, wrapping, per-line frames, baselines, and overflow, because all five change
geometry and none of them are the renderer's business.

The default measurer is deterministic and approximate on purpose: server-side rendering and
tests both need stable numbers, and a font engine gives neither. A more precise one is injected
through `LayoutContext`. See [Text](docs/text.md).

## Links between boxes

Where a connection leaves one rectangle, where it enters another, and the right-angle path in
between, as points rather than path data.

```php
use Atelier\Layout\Connection\OrthogonalConnector;

$connection = (new OrthogonalConnector())->connect($from, $to);

$connection->points;      // 100,40 -> 140,40 -> 140,80 -> 180,80
$connection->labelPoint;  // where an edge label belongs
$connection->tipTangent;  // the final direction, for an arrowhead
```

Label placement avoids solved rectangles deterministically. Graph ranking, obstacle avoidance
and edge bundling are deliberately not here. See [Links between boxes](docs/connections.md).

## Charts and boards

The recurring furniture: parallel lanes with a reserved header, an edge strip for an axis, and a
key made of swatches and labels. Each takes a rectangle and returns placed frames.

See [Tracks, bands and legends](docs/tracks-bands-legends.md).

## Documentation

- [Getting started](docs/getting-started.md): solve a grid, some text, and a link.
- Composition: [stacks](docs/composition/stacks.md), [grid](docs/composition/grid.md), [alignment](docs/composition/alignment.md), [distribution](docs/composition/distribution.md), [groups](docs/composition/groups.md), [overlays](docs/composition/overlays.md).
- [Geometry](docs/geometry/overview.md): the value types, constraints, and fitting.
- [Text](docs/text.md): measuring, wrapping, baselines, overflow.
- [Links between boxes](docs/connections.md): ports, connectors, labels.
- [Tracks, bands and legends](docs/tracks-bands-legends.md): lanes, axis strips, keys.

The full documentation is published at [ateliersvg.com/layout](https://ateliersvg.com/layout/).

## Contributing

Contributions are welcome. Visit the [project on GitHub](https://github.com/ateliersvg/layout)
to [report a bug](https://github.com/ateliersvg/layout/issues/new),
[suggest a feature](https://github.com/ateliersvg/layout/issues/new), or
[open a pull request](https://github.com/ateliersvg/layout/pulls).

Before submitting code, run:

```bash
composer qa   # PHP-CS-Fixer, PHPStan at level max, and PHPUnit
```

A new public primitive needs exact numeric tests before a consumer depends on it.

## Support

Bug reports, security disclosures, and contribution guidelines are collected at
[ateliersvg.com/support](https://ateliersvg.com/support/).

Atelier is maintained by Simon André. Sharing the package or
[starring it on GitHub](https://github.com/ateliersvg/layout) helps more than you would think.

## License

Atelier Layout is released under the [MIT License](LICENSE).
