---
order: 40
---
# Stacks And Spacing

`Stack` is one-dimensional flow: children are placed along one axis, separated by a fixed gap, inside optional padding.

<div class="figure-grid">
<figure><img src="../images/stack-row.svg" alt="Three boxes placed side by side along a horizontal axis"><figcaption><code>Stack::row()</code></figcaption></figure>
<figure><img src="../images/stack-column.svg" alt="Three boxes placed one under another along a vertical axis"><figcaption><code>Stack::column()</code></figcaption></figure>
</div>

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Stack;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Value\InsetSpec;

$row = Stack::row('toolbar')
    ->gap(8)
    ->padding(InsetSpec::px(12))
    ->alignItems(Alignment::Center)
    ->add(Frame::fixed('back', 24, 24))
    ->add(Frame::stretch('title'))
    ->add(Frame::fixed('menu', 24, 24));

$solved = $row->solve(new LayoutContext(), Rect::fromSize(320, 48));
```

Use it for toolbars, legends, vertical sections, and repeated rows.

## Gap

`gap()` is the space between adjacent children. It is never applied before the first child or after the last one, so a stack of one child has no gap at all.

<div class="figure-grid">
<figure><img src="../images/stack-gap-none.svg" alt="Three boxes touching, with no space between them"><figcaption><code>gap(0)</code></figcaption></figure>
<figure><img src="../images/stack-gap-loose.svg" alt="Three boxes separated by wide even spaces"><figcaption>a larger <code>gap()</code></figcaption></figure>
</div>

Gap is fixed. To spread children across leftover space instead, see [Distribution](distribution.md).

## Padding

`padding()` insets the content rectangle before children are placed. It is available on every container, not just stacks.

<img src="../images/insets-padding.svg" alt="An outer rectangle with an inner rectangle inset on all four sides">

```php
use Atelier\Layout\Value\InsetSpec;

InsetSpec::zero();
InsetSpec::px(12);
InsetSpec::percent(4);
```

`InsetSpec::percent()` resolves against the container size, so a `4%` padding on a `200x400` box is not the same number of pixels on each axis. `resolve(Size $size)` returns the concrete `Insets`.

## Spacer

`Spacer` consumes flexible space inside a stack. Use it when a layout needs a flexible gap rather than a fixed-size empty box.

<img src="../images/spacer.svg" alt="Two boxes pushed to opposite ends of a row by a flexible gap between them">

```php
use Atelier\Layout\Element\Spacer;

Stack::row('bar')
    ->add(Frame::fixed('logo', 32, 32))
    ->add(new Spacer('push'))
    ->add(Frame::fixed('avatar', 32, 32));
```

A spacer takes part in flex distribution exactly like `Frame::stretch()`, but carries no identity of its own in the rendered output.

## Baseline Alignment

`alignToBaseline()` aligns children on the first text baseline instead of the box edge. It affects children that expose a baseline, such as [`TextBlock`](../text.md); other children keep their top edge.
