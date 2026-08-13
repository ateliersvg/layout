---
order: 90
---
# Overlays And Badges

`Overlay` places children in the same outer rectangle by snapping anchors. Each child declares which of its own anchors meets which anchor of the host, plus an optional offset.

<div class="figure-grid">
<figure><img src="../images/overlay-center.svg" alt="A small box centered on top of a larger one"><figcaption>centered on the host</figcaption></figure>
<figure><img src="../images/overlay-badge.svg" alt="A small box snapped to the top right corner of a larger one, overhanging it"><figcaption>a corner badge</figcaption></figure>
</div>

```php
use Atelier\Layout\Anchor;
use Atelier\Layout\Element\Overlay;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;

$overlay = Overlay::of('card')
    ->add($body)
    ->add($badge, subject: Anchor::Center, target: Anchor::TopRight);

$solved = $overlay->solve(new LayoutContext(), Rect::fromSize(200, 120));
```

`subject` is the anchor on the child, `target` the anchor on the host. Making them equal pins the child inside the host; making them opposite lets it overhang, which is how a notification badge sits half outside its button.

## The Nine Anchors

`Anchor` names the same nine positions on any rectangle: `TopLeft`, `TopCenter`, `TopRight`, `CenterLeft`, `Center`, `CenterRight`, `BottomLeft`, `BottomCenter`, `BottomRight`.

`offsetX` and `offsetY` shift the child after snapping, in that order, and are applied unconditionally: a negative offset moves it back toward the host.

## Alignment Or Anchoring

Both position a child in a box, and choosing between them is a question of what stays true when sizes change.

[Alignment](alignment.md) keeps the child inside the box. An `End`-aligned child touches the right edge and never crosses it. Anchoring computes the meeting point of two anchors, so the child can sit astride the edge or fully outside. Use alignment for content, anchoring for decoration attached to content.

## Badges On A Connection

The same idea applies to the ends of a connection, where the host is not a rectangle but the start or end of a path.

<div class="figure-grid">
<figure><img src="../images/badge-start.svg" alt="A small marker placed at the start of a connector"><figcaption><code>EndpointStart</code></figcaption></figure>
<figure><img src="../images/badge-end.svg" alt="A small marker placed at the end of a connector"><figcaption><code>EndpointEnd</code></figcaption></figure>
</div>

```php
use Atelier\Layout\Connection\ConnectionEndpointBadge;
use Atelier\Layout\Connection\ConnectionEndpointBadgePlacement;
use Atelier\Layout\Geometry\Size;

$badge = ConnectionEndpointBadge::for($connection, ConnectionEndpointBadgePlacement::EndpointEnd)
    ->size(new Size(16, 16))
    ->avoid($index)
    ->place();
```

`avoid()` takes a `RectIndex` and skips occupied placements deterministically before falling back to the requested one. Cardinality markers on an ER relation and multiplicities on a class association are the usual consumers.
