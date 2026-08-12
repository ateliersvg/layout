---
order: 70
---
# Distribution

Distribution decides what happens to the space a stack has left over on its main axis once every child has been measured and every gap applied.

It only matters when there is leftover space. A stack whose children fill the axis exactly, or that contains a flexible child such as `Frame::stretch()` or a `Spacer`, has nothing left to distribute.

## The Six Modes

<div class="figure-grid">
<figure><img src="../images/distribute-start.svg" alt="Three boxes packed at the start of the axis, free space after them"><figcaption><code>Start</code></figcaption></figure>
<figure><img src="../images/distribute-center.svg" alt="Three boxes packed in the middle, free space on both sides"><figcaption><code>Center</code></figcaption></figure>
<figure><img src="../images/distribute-end.svg" alt="Three boxes packed at the end of the axis, free space before them"><figcaption><code>End</code></figcaption></figure>
<figure><img src="../images/distribute-space-between.svg" alt="Three boxes with equal gaps between them and none at the edges"><figcaption><code>SpaceBetween</code></figcaption></figure>
<figure><img src="../images/distribute-space-around.svg" alt="Three boxes each surrounded by equal space, half-sized at the edges"><figcaption><code>SpaceAround</code></figcaption></figure>
<figure><img src="../images/distribute-space-evenly.svg" alt="Three boxes with equal space between them and at both edges"><figcaption><code>SpaceEvenly</code></figcaption></figure>
</div>

```php
use Atelier\Layout\Distribution;
use Atelier\Layout\Element\Stack;

Stack::row('cards')
    ->gap(8)
    ->distribute(Distribution::SpaceBetween)
    ->add($a)
    ->add($b)
    ->add($c);
```

## Reading The Three Space Modes

The three packing modes are obvious. The three spacing modes differ only in what they do at the edges, and that is the whole distinction:

- `SpaceBetween` puts nothing at the edges. With `n` children it creates `n - 1` equal gaps.
- `SpaceAround` gives every child an equal share of space on both sides, so the edge space is half of the space between two children.
- `SpaceEvenly` makes every space equal, edges included. With `n` children it creates `n + 1` identical gaps.

`gap()` is added on top in every mode. A stack with both a gap and `SpaceBetween` keeps the gap as a minimum and shares only what remains.

## Distribution Or Spacer

Two ways exist to push things apart, and they are not interchangeable.

Distribution is a property of the container: it treats every child the same way. A `Spacer` is a child: it takes the space at one precise position in the list. Use distribution when the rhythm is uniform, a spacer when only one seam should open up.

```php
// Uniform: three cards spread across the row.
Stack::row('cards')->distribute(Distribution::SpaceEvenly);

// Positional: logo on the left, avatar on the right, nothing in between.
Stack::row('bar')->add($logo)->add(new Spacer('push'))->add($avatar);
```
