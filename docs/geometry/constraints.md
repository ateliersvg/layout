---
order: 100
---
# Constraints

`BoxConstraints` is what a parent tells a child before asking it how big it wants to be. It travels down the tree during measurement; sizes travel back up.

<div class="figure-grid">
<figure><img src="../images/constraints-tight.svg" alt="A box forced to exactly the size of its container"><figcaption><code>tight()</code></figcaption></figure>
<figure><img src="../images/constraints-loose.svg" alt="A box smaller than its container, free to keep its own size"><figcaption><code>unconstrained()</code></figcaption></figure>
</div>

```php
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Geometry\Size;

BoxConstraints::tight(200, 48)->constrain(new Size(320, 40));   // 200 x 48
BoxConstraints::unconstrained()->constrain(new Size(320, 40));  // 320 x 40
```

A tight constraint admits exactly one size and the child has no say. An unconstrained one lets the child report its intrinsic size. `constrain()` is the operation that turns a wish into an allowed size.

## Where They Come From

You rarely build constraints by hand. Containers derive them: a grid slot constrains its child to the slot, a stack constrains on the cross axis and lets the main axis flow, a fixed frame reports the same size whatever it is asked.

Constraints matter when you implement `LayoutNodeInterface` yourself. `measure()` receives them and must return an `IntrinsicSize` that respects them; `solve()` receives the final `Rect` and places content in it.

```php
public function measure(LayoutContext $context, BoxConstraints $constraints): IntrinsicSize;
public function solve(LayoutContext $context, Rect $rect): PlacedNode;
```

The split is deliberate. Measurement may run several times as a parent explores sizes; solving runs once, on the answer. Keep `measure()` free of side effects.

## Flexible Children

`FlexibleLayoutNodeInterface` adds `flex(): float`. A node that reports a non-zero flex asks for a share of the leftover main-axis space instead of its measured size. `Frame::stretch()` and `Spacer` both implement it.

Flex is resolved after fixed children are measured, which is why a stretch child never squeezes a fixed one: it only ever receives what is left.

[Grid](../composition/grid.md) track sizes are the other place where fixed and flexible meet, and [Distribution](../composition/distribution.md) decides what happens to the leftover space when no child is flexible at all.
