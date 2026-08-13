---
order: 40
---
# Links Between Boxes

Link primitives describe connection geometry without producing SVG path data.

In this package, linking boxes means:

1. choose where a connection leaves one rectangle;
2. choose where it enters another rectangle;
3. compute the intermediate points of the line;
4. expose helper points for labels and arrowheads.

It is the geometry behind any arrow, relation, or connector drawn between two boxes.

## Ports

`Port::on($rect, PortSide::Right)` returns a point on a rectangle edge plus its side.

Sides:

- `Top`
- `Right`
- `Bottom`
- `Left`

## Orthogonal Links

The connector chooses sides from the relative centers of the two rectangles and returns one of three shapes: a straight segment when the boxes line up, an L when they do not, or a Z when the approach has to cross back.

<div class="figure-grid">
<figure><img src="images/connection-straight.svg" alt="Two aligned boxes joined by a single straight segment"><figcaption>straight</figcaption></figure>
<figure><img src="images/connection-l.svg" alt="Two offset boxes joined by a connector with one right-angle turn"><figcaption>one turn</figcaption></figure>
<figure><img src="images/connection-z.svg" alt="Two boxes joined by a connector with two right-angle turns"><figcaption>two turns</figcaption></figure>
</div>

```php
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\RectIndex;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\Connection\OrthogonalConnector;
use Atelier\Layout\Connection\ConnectionLabel;
use Atelier\Layout\Connection\ConnectionLabelPlacement;

$from = new Rect(20, 20, 80, 40);
$to = new Rect(180, 60, 90, 40);

$connection = (new OrthogonalConnector())->connect($from, $to);

$connection->points;
$connection->segments;
$connection->labelPoint;
$connection->tipTangent;
$connection->isStraight();

$label = ConnectionLabel::for($connection)
    ->size(new Size(20, 10))
    ->avoid(RectIndex::from(['node.a' => $from]))
    ->placement(ConnectionLabelPlacement::Centered)
    ->place();
```

For the example above, the connection leaves the right side of `$from`, enters the left side of `$to`, and may return points like:

```text
100,40 -> 140,40 -> 140,80 -> 180,80
```

A renderer can turn those points into SVG path data:

```text
M 100 40 L 140 40 L 140 80 L 180 80
```

`labelPoint` is the suggested midpoint for an edge label. `tipTangent` is the final direction vector, useful for drawing an arrowhead at the target.

`segments` exposes immutable straight pieces with stable indices and axes, so consumers can reason about the first leg, the middle turn, or the final approach without scanning the point list again.

## Labels

`ConnectionLabel` turns a connection plus a measured label size into a deterministic label frame, anchor point, and chosen segment index. It is still renderer-neutral.

<div class="figure-grid">
<figure><img src="images/connectionlabel-above.svg" alt="A label frame sitting above a connector segment"><figcaption><code>Above</code></figcaption></figure>
<figure><img src="images/connectionlabel-centered.svg" alt="A label frame straddling a connector segment"><figcaption><code>Centered</code></figcaption></figure>
<figure><img src="images/connectionlabel-below.svg" alt="A label frame sitting below a connector segment"><figcaption><code>Below</code></figcaption></figure>
</div>

`ConnectionLabelPlacement` also offers `Start` and `End` for labels pinned near one extremity rather than the middle.

If you already have solved rectangles, `ConnectionLabel::avoid()` skips occupied placements deterministically before falling back to the requested placement. See [Geometry](geometry/overview.md) for the `RectIndex` it takes.

## What Links Between Boxes Do Not Do

This package does not currently solve:

- graph ranking
- obstacle avoidance
- edge bundling
- self-loop semantics
- label collision avoidance
- arrowhead drawing

Those are still consumer responsibilities. A consumer takes the returned points and turns them into whatever its renderer draws.

## Demo

```bash
php examples/connections-demo.php
```

The demo prints the source rect, target rect, connection points, label point, and arrowhead tangent for left-to-right, top-to-bottom, and right-to-left cases.
