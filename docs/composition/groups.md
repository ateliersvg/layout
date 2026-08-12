---
order: 80
---
# Groups

`Group` places children in the same local region under one shared alignment policy. Unlike a stack it does not lay children out in sequence, and unlike an overlay it does not snap them to anchors: every child gets the same box and the same alignment.

<img src="../images/group.svg" alt="Several boxes sharing one region under a common alignment">

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Element\Group;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Value\InsetSpec;

$group = Group::of('badges')
    ->padding(InsetSpec::px(8))
    ->align(Alignment::End, Alignment::Start)
    ->add($primary)
    ->add($secondary);

$solved = $group->solve(new LayoutContext(), Rect::fromSize(160, 96));
```

Use it for badges, grouped labels, and overlays that are not anchor-specific.

## Reading Solved Frames

Every solved tree returns a `PlacedNode`. Ids are the bridge between layout and renderers: you name a node when you build it, and you ask for it by that name afterwards.

```php
$solved->frameOf('primary');   // the Rect, or null
$solved->find('primary');      // the PlacedNode, or null
```

Nothing downstream needs to know how the frame was computed. A renderer that receives `frameOf('primary')` draws a rectangle; whether it came from a grid slot, a stack child, or a group is not its concern.

## Bounds Around Solved Children

`Bounds` computes the rectangle that contains a set of rectangles. It is a pure value helper, not a node: give it frames you already solved.

<img src="../images/group-bounds.svg" alt="A dashed rectangle enclosing several solved boxes">

```php
use Atelier\Layout\Geometry\Bounds;
use Atelier\Layout\Geometry\Insets;

$box = Bounds::of($a, $b, $c);
$padded = Bounds::expand($box, Insets::all(12));
```

`Bounds::fromRects(iterable $rects)` takes any iterable and returns `null` for an empty one, which is the form to use when the set is built at runtime and may be empty.

<img src="../images/bounds-union.svg" alt="Two overlapping rectangles and the single rectangle that contains both">

Use it to frame a cluster, to compute a background plate behind a group of nodes, or to size a viewBox around everything that was solved.
