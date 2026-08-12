---
order: 60
---
# Alignment

Alignment answers one question: where does a child sit inside the space it was given, when it does not fill that space.

`Alignment` has four cases, and they mean the same thing on both axes.

| Case | Horizontal | Vertical |
|---|---|---|
| `Start` | left | top |
| `Center` | centered | centered |
| `End` | right | bottom |
| `Stretch` | fill the width | fill the height |

## The Nine Positions

`Group` and `Grid` take a horizontal and a vertical alignment, which together give the nine positions below.

<div class="figure-grid">
<figure><img src="../images/align-top-left.svg" alt="Content packed against the top left corner"><figcaption><code>Start</code>, <code>Start</code></figcaption></figure>
<figure><img src="../images/align-top-center.svg" alt="Content centered horizontally against the top edge"><figcaption><code>Center</code>, <code>Start</code></figcaption></figure>
<figure><img src="../images/align-top-right.svg" alt="Content packed against the top right corner"><figcaption><code>End</code>, <code>Start</code></figcaption></figure>
<figure><img src="../images/align-middle-left.svg" alt="Content against the left edge, centered vertically"><figcaption><code>Start</code>, <code>Center</code></figcaption></figure>
<figure><img src="../images/align-middle-center.svg" alt="Content centered on both axes"><figcaption><code>Center</code>, <code>Center</code></figcaption></figure>
<figure><img src="../images/align-middle-right.svg" alt="Content against the right edge, centered vertically"><figcaption><code>End</code>, <code>Center</code></figcaption></figure>
<figure><img src="../images/align-bottom-left.svg" alt="Content packed against the bottom left corner"><figcaption><code>Start</code>, <code>End</code></figcaption></figure>
<figure><img src="../images/align-bottom-center.svg" alt="Content centered horizontally against the bottom edge"><figcaption><code>Center</code>, <code>End</code></figcaption></figure>
<figure><img src="../images/align-bottom-right.svg" alt="Content packed against the bottom right corner"><figcaption><code>End</code>, <code>End</code></figcaption></figure>
</div>

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Element\Group;

Group::of('root')
    ->align(Alignment::End, Alignment::Start)
    ->add($content);
```

`Grid::align()` takes the same pair and applies it to every slot, and `GridBuilder::add()` accepts `alignX` and `alignY` to override it for one item.

## Cross-Axis Alignment In A Stack

A stack aligns its children on the cross axis only: the main axis is governed by gap and [distribution](distribution.md). `alignItems()` takes a single `Alignment`.

<div class="figure-grid">
<figure><img src="../images/align-cross-start.svg" alt="Boxes of different heights aligned on their top edges"><figcaption><code>Start</code></figcaption></figure>
<figure><img src="../images/align-cross-center.svg" alt="Boxes of different heights centered on a common axis"><figcaption><code>Center</code></figcaption></figure>
<figure><img src="../images/align-cross-end.svg" alt="Boxes of different heights aligned on their bottom edges"><figcaption><code>End</code></figcaption></figure>
<figure><img src="../images/align-cross-stretch.svg" alt="Boxes stretched to a common height"><figcaption><code>Stretch</code></figcaption></figure>
</div>

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Element\Stack;

Stack::row('legend')
    ->gap(8)
    ->alignItems(Alignment::Stretch)
    ->add($swatch)
    ->add($label);
```

`Stretch` only has an effect on a child that accepts being resized. A `Frame::fixed()` keeps its size and falls back to `Start`.

## Alignment In Text

`TextBlock::align()` takes the same enum, with one difference worth knowing: `Stretch` behaves like `Start`. Layout does not justify ](../text, so there is nothing to stretch. See [Text](../text.md).
