# Atelier Layout

Renderer-agnostic layout primitives.

`atelier/layout` answers one question before anything is rendered:

> Given a box and some constraints, where does every rectangle, label, link, and child frame go?

It knows nothing about any output format, markup, or colour. It returns geometry that a renderer can draw.

## Why It Exists

Visual libraries repeat the same spatial work:

- Frame `4%` canvas padding and fixed `10px` strokes.
- Stack rows or columns with gaps and alignment.
- Share space across grid tracks.
- Span slots.
- Keep text centered, bottom-snapped, wrapped, or overflow-aware.
- Compute a group frame around solved children.
- Draw links between boxes.
- Keep all of that deterministic enough to test.

Without a shared layer, every renderer eventually grows its own incompatible mini-layout engine.

## Installation

```bash
composer require atelier/layout
```

Requires PHP 8.3+.

## Status

The package is usable as a shared spatial layer. It includes geometry values,
constraints, stack/grid/overlay/group composition, deterministic text layout,
shape-aware fitting, track/item/legend/band helpers, and link geometry between
boxes.

It deliberately stops before rendering and semantics: markup output, parsing,
domain models, themes, markers, colours, and graph-ranking algorithms belong in
consumers.

## Conventions

Three promises the package makes, so nothing here is left to guess.

### The y axis points down

`Rect(x, y, width, height)` uses screen coordinates: `x` grows right, `y` grows
**down**, and the origin is the top-left corner. `y = 100` is below `y = 0`.
That is the SVG and Canvas convention, and it is what `Anchor::TopLeft`,
`Insets(top, right, bottom, left)` and `Rect::bottom()` all mean.

This is a decision, not an oversight, and it will not change. The alternative
convention, `y` growing up with the origin at the bottom left, is the one
mathematical charts use. A consumer working that way flips in its **scale**, the
function that turns a data value into a pixel, which every chart library already
has. Carrying an orientation inside `Rect` would make `TopLeft` ambiguous and
`bottom()` wrong half the time, for a flip that belongs one layer up.

### Zero in, zero out

A rectangle with zero width or height is an ordinary state, not a caller
mistake: a collapsed panel, an empty list, a track that received no space, a
percentage of nothing. Every primitive accepts one and returns empty geometry
rather than raising.

What matters is not that nothing crashes, but that nothing produces `NaN`.
Dividing by a zero dimension does not raise; it seeds a `NaN` that spreads
through every later computation and surfaces at the far end as coordinates no
renderer can draw, arbitrarily far from its cause. Negative dimensions are still
rejected, because those are caller mistakes.

### Where the CSS vocabulary stops

The package borrows CSS words on purpose: `gap`, `padding`, `align`, `stretch`,
`fr`, `Grid`, `Stack`. Reading `->gap(12)` should need no documentation.

Borrowing the word means promising the behaviour, so here is where it does not
hold. These are simplifications, not bugs, but they will surprise you if nobody
says so.

| Concept | CSS | Here |
|---|---|---|
| `align-items: stretch` | Leaves an item alone when its cross size is definite | `Alignment::Stretch` stretches unconditionally, ignoring a declared size |
| `1fr` track | Has an automatic minimum of the track's min-content, so content can force it wider | Fraction tracks split the available space and ignore content entirely |
| Percentage padding | Resolves **all four** sides against the inline size (the width) | Resolves top and bottom against the height, left and right against the width |

Verified as matching CSS: `gap` adds no space at the edges, fraction-based `flex`
distributes free space proportionally, and `auto` tracks size to their content.

Beyond that, `atelier/layout` is not a browser layout engine. CSS parity is not
a goal; see Deferred at the end.

## Core Concepts

The package has a few layers. You rarely touch all of them at once.

### Geometry

`Rect`, `Point`, `Size`, `Insets`, `Bounds`, `Circle`, `BoxModel`, `Fit` -- immutable values, plus shape-aware fitting and safe areas.

### Constraints and solving

`BoxConstraints` and `IntrinsicSize` give the min/preferred/max sizing vocabulary; `LayoutContext`, `LayoutSolver`, and `PlacedNode` solve a node tree into queryable frames.

### Composition

`Stack`, `Grid`, `Group`, `Overlay`, `Spacer`, `TextBlock` are the composable layout nodes. A horizontal `Stack` can align its children on a shared text baseline with `alignToBaseline()` instead of on their box edges. `PlacedGrid`/`GridSlot` expose placed track, slot, and named-area metadata when consumers need debugging, snapshots, or annotations.

### Higher-level spatial helpers

Named primitives for recurring visual structures: `TrackGroup`, `InlineGroup`, `LegendBlock`, `EdgeBand`, `AspectFrame`, `GroupBounds`. Each places frames a renderer can draw directly.

### Connections

`OrthogonalConnector` turns two boxes into a link you can draw. `RectIndex` answers collision and free-space queries for labels and badges.

## Stack And Grid

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;

$grid = Grid::tracks('toolbar', [
    TrackSize::fixed(80),
    TrackSize::fr(),
    TrackSize::fixed(40),
], [
    TrackSize::fixed(32),
])
    ->gap(12)
    ->align(Alignment::Stretch, Alignment::Center)
    ->add(Frame::fixed('back', 80, 24))
    ->add(Frame::stretch('title'), alignX: Alignment::Center)
    ->add(Frame::fixed('menu', 40, 24));

$result = $grid->solve(new LayoutContext(), Rect::fromSize(320, 48));

$bandFrame = $result->frameOf('title');

$layout = $grid->layout(new LayoutContext(), Rect::fromSize(320, 48));
$layout->column(0);
$layout->row(0);
$layout->slot(1, 0);
$layout->slots();
$layout->item('title');
$layout->namedArea('title');
$layout->frameOf('title');
```

`Grid` supports fixed, auto, and fraction tracks; row tracks; column/row spans; global alignment; and per-item alignment overrides.
`PlacedGrid` exposes the placed track, slot, and named-area metadata when consumers need debugging, snapshots, or annotations.

## Track Groups

```php
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Track\TrackGroup;
use Atelier\Layout\Value\InsetSpec;

$layout = TrackGroup::horizontal('board')
    ->gap(10)
    ->padding(InsetSpec::px(10))
    ->headerSize(20)
    ->footerSize(10)
    ->equalTracks()
    ->addTrack('todo')
    ->addTrack('doing')
    ->place(Rect::fromSize(300, 120));

$todo = $layout->track('todo');
$todo?->headerFrame;
$todo?->bodyFrame;
$todo?->footerFrame;
```

`TrackGroup` is for placed track geometry, not item placement. It gives a deterministic track frame plus header/body/footer bands, so renderers can place their own content without recomputing the track scaffold.

## Inline Groups

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Inline\InlineGroup;

$row = InlineGroup::contentSized('browse.tasks')
    ->gap(10)
    ->align(Alignment::Center)
    ->add('choose-product', preferredWidth: 50, minWidth: 40)
    ->add('enter-item', preferredWidth: 60, minWidth: 40)
    ->place(Rect::fromSize(200, 40));

$row->item('choose-product');
$row->overflowX;
```

`InlineGroup` is the companion to `TrackGroup`: it turns a horizontal run of items into placed frames, keeps the row aligned inside the available body, and reports overflow instead of letting items collide.

## Legend Layout

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Legend\LegendBlock;
use Atelier\Layout\Value\InsetSpec;

$legend = LegendBlock::vertical('legend')
    ->gap(8)
    ->labelGap(6)
    ->swatchSize(10, 10)
    ->padding(InsetSpec::px(4))
    ->align(Alignment::Center, Alignment::Start)
    ->add('api', 40, 12)
    ->add('worker', 60, 12)
    ->place(Rect::fromSize(120, 100));

$legend->entry('api');
$legend->frame;
```

`LegendBlock` keeps the geometry of swatches and labels together so renderers can place legend rows without repeating the same packing math.

## Edge Bands

```php
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Band\EdgeBand;
use Atelier\Layout\Value\InsetSpec;

$band = EdgeBand::top('surface')
    ->bandSize(24)
    ->gap(8)
    ->padding(InsetSpec::px(4))
    ->place(Rect::fromSize(120, 100));

$band->bandFrame;
$band->contentFrame;
```

`EdgeBand` reserves a band frame and a content frame in one pass, which is useful for renderers that want band spacing separated from the rest of the surface without carrying painting policy into `layout`.

## Aspect Frames

```php
use Atelier\Layout\Aspect\AspectFrame;
use Atelier\Layout\Fit\FitMode;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Value\InsetSpec;

$slot = AspectFrame::of('fitted frame', 16, 9)
    ->padding(InsetSpec::px(4))
    ->fitMode(FitMode::Contain)
    ->place(Rect::fromSize(200, 120));

$slot->fittedFrame;
```

`AspectFrame` keeps a fitted area at a stable ratio inside the available frame, with optional padding and fit behaviour.

## Overflow

Every solved node can report children that do not fit, whatever container placed them:

```php
$placed = $stack->solve(new LayoutContext(), Rect::fromSize(200, 80));

$placed->overflows();             // bool
$placed->overflowingChildren();   // list<PlacedNode>
```

Overflow is derived from the frames rather than stored by each container, so an anchored child pushed out by an offset, a fixed child larger than its slot, and a track that outgrew its band all surface the same way. It looks at direct children only: a node reports its own content, not its grandchildren's.

## Text Layout

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Text\FontWeight;

$layout = TextBlock::of('label', 'Two line label', 12)
    ->weight(FontWeight::Bold)
    ->align(Alignment::Center, Alignment::End)
    ->breakWords()
    ->layout(new LayoutContext(), new Rect(0, 0, 80, 48));

foreach ($layout->lines as $line) {
    $line->text;
    $line->frame;
    $line->baseline;
}

$layout->hasOverflow();
```

`maxLines()` caps the rendered lines: extra lines are dropped, `isTruncated()`
reports it, and vertical alignment uses the height actually rendered. Adding an
ellipsis is a rendering decision and stays with the consumer.

Text layout returns wrapped lines, per-line frames, absolute baselines, and overflow state. The default measurer is deterministic and approximate; consumers can provide a more precise measurer through `LayoutContext`.
`CharWidthTextMeasurer` supports `FontWeight::Normal` / `FontWeight::Bold` and configurable line height/ascent/bold-width factors, so consumers can keep their renderer baselines stable while sharing the same measurement contract.

## Links Between Boxes

Connect two boxes with a single call. You give it two rectangles; it gives you geometry -- a polyline, a label anchor, an arrowhead direction -- that a renderer draws. `atelier/layout` never emits SVG itself.

```php
use Atelier\Layout\Connection\OrthogonalConnector;

$link = (new OrthogonalConnector())->connect($boxA, $boxB);

$link->points;       // list<Point> -- the polyline to draw
$link->labelPoint;   // where an edge label fits
$link->tipTangent;   // arrowhead direction at the end
```

The connector picks which sides to leave from based on the boxes' relative position and returns a deterministic right-angle (elbow) path. It is intentionally small: graph ranking, obstacle avoidance, and bundling belong in consumers until repeated use cases justify shared APIs.

### Advanced: explicit sides, labels, badges

When you need to pin the exact attachment side, place a collision-aware label, or add an endpoint badge:

```php
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\RectIndex;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\Connection\OrthogonalConnector;
use Atelier\Layout\Connection\Port;
use Atelier\Layout\Connection\PortSide;
use Atelier\Layout\Connection\ConnectionEndpointBadge;
use Atelier\Layout\Connection\ConnectionEndpointBadgePlacement;
use Atelier\Layout\Connection\ConnectionLabel;
use Atelier\Layout\Connection\ConnectionLabelPlacement;

$link = (new OrthogonalConnector())->connectPorts(
    Port::on($boxA, PortSide::Right),
    Port::on($boxB, PortSide::Left),
);

$label = ConnectionLabel::for($link)
    ->size(new Size(20, 10))
    ->avoid(RectIndex::from(['node.a' => $boxA]))
    ->padding(Insets::all(6))
    ->placement(ConnectionLabelPlacement::Centered)
    ->place();

$badge = ConnectionEndpointBadge::for($link, ConnectionEndpointBadgePlacement::End)
    ->size(new Size(20, 10))
    ->padding(Insets::all(6))
    ->place();
```

`RectIndex` is the shared collision index: `RectIndex::from([...])->isFree($candidate, ignore: [...])` reports whether a label or badge would overlap existing boxes. `ConnectionLabel` places text along the link; `ConnectionEndpointBadge` places a badge at the start or end. Neither embeds any domain semantics.

## Shape-Aware Layout

```php
use Atelier\Layout\Geometry\BoxModel;
use Atelier\Layout\Geometry\Circle;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\StrokePlacement;

$content = (new BoxModel(
    outer: new Rect(0, 0, 200, 400),
    padding: new Insets(16, 8, 16, 8),
    strokeWidth: 10,
    strokePlacement: StrokePlacement::Inside,
))->contentRect();

$safeLabelBox = (new Circle(new Point(100, 200), 80))
    ->safeSquare(Insets::all(8), strokeWidth: 10, strokePlacement: StrokePlacement::Inside);
```

This is the path for target canvas sizing, percent padding, fixed contained strokes, and safe label areas inside a non-rectangular shape.

## Group Bounds

```php
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\GroupBounds;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;

$frame = GroupBounds::fromFrames('subgraph.auth', [
    'login' => new Rect(20, 40, 80, 24),
    'session' => new Rect(120, 52, 90, 24),
])
    ->padding(Insets::all(12))
    ->topReserve(28)
    ->minSize(new Size(240, 120))
    ->frame();
```

`GroupBounds` is a small helper for consumers that already solved child frames and need a deterministic group frame, optional label reserve, and optional canvas clamp without introducing a full group-layout engine.

## Relationship To Consumers

A consumer owns its own semantics, parsers, models, themes, and rendering. It
uses `atelier/layout` for the spatial math those layers keep re-deriving.

The boundary runs one way: a consumer reads solved frames, and layout never
learns anything about the consumer. That is what keeps the package reusable
across unrelated renderers.

## Documentation

- [Getting started](docs/getting-started.md)
- [Composition primitives](docs/composition.md)
- [Geometry and fitting](docs/geometry.md)
- [Text layout](docs/text-layout.md)
- [Links between boxes](docs/connections.md)
- [Demos](docs/demos.md)

## Development

```bash
composer install
composer test          # phpunit
composer sa            # phpstan, level max
composer cs            # php-cs-fixer --dry-run --diff
composer cs:fix
composer qa            # cs + sa + test
composer docs:images   # regenerate docs/images (needs the sibling atelier/svg checkout)
composer validate --strict
php examples/smoke.php
php examples/composition-demo.php
php examples/composition-gallery.php
php examples/connections-demo.php
```

The package is deliberately small and test-driven. New public primitives should have exact numeric tests before another package depends on them.

## Deferred

`atelier/layout` is not a browser layout engine and not a full constraint solver. CSS parity, Cassowary-style constraints, force-directed graph layout, font shaping, and obstacle-aware link routing are deferred until multiple consumers need them.

## License

MIT.
