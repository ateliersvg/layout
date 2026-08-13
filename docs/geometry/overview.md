---
order: 20
---
# Geometry

Geometry values are immutable and renderer-neutral. They do not render, mutate, or depend on SVG.

## Value Types

- `Point`: x/y coordinate.
- `Size`: width/height.
- `Rect`: x/y/width/height, anchors, inset.
- `Insets`: top/right/bottom/left.
- `Bounds`: union and expansion of rectangles. [Groups](../composition/groups.md) computes one around solved frames.
- `RectIndex`: immutable rectangle collision and occupancy queries.

## Frame Model

`BoxModel` turns an outer rectangle into a content rectangle:

```php
use Atelier\Layout\Geometry\BoxModel;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\StrokePlacement;

$content = (new BoxModel(
    outer: new Rect(0, 0, 200, 400),
    padding: new Insets(16, 8, 16, 8),
    strokeWidth: 10,
    strokePlacement: StrokePlacement::Inside,
))->contentRect();
```

This is the answer to fixed-canvas questions like:

> In a `200x400` canvas with `4%` padding and a fixed `10px` contained stroke, what space remains?

Stroke placement is part of the arithmetic because it changes the answer. An inside stroke eats content space, an outside one does not, and a centered one eats half.

## Circle Safe Areas

`Circle` exposes safe areas for labels:

```php
use Atelier\Layout\Geometry\Circle;
use Atelier\Layout\Geometry\Point;

$safe = (new Circle(new Point(100, 200), 80))
    ->safeSquare(Insets::all(8), strokeWidth: 10, strokePlacement: StrokePlacement::Inside);
```

Consumers can lay text into that safe rectangle without knowing how the final renderer draws circles. Venn sets and pie labels use it.

## Rect Index

`RectIndex` answers small collision questions over solved frames:

```php
use Atelier\Layout\Geometry\RectIndex;

$index = RectIndex::from([
    'node.a' => $frameA,
    'node.b' => $frameB,
]);

$hits = $index->intersecting($candidateLabel);
$isFree = $index->isFree($candidateLabel, ignore: ['edge.ab.label']);
```

Edge-touching does not count as an intersection. That keeps dense scenes from rejecting labels that merely line up with an edge.

`ignore` exists because the most common query is "is this position free, apart from the thing I am about to move there".
