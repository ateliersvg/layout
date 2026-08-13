---
order: 50
---
# Text And Inline Runs

`TextBlock` gives renderers enough information to draw wrapped text without becoming a browser text engine. Measurement, wrapping, per-line frames, baselines, overflow, and font weight are layout concerns because they all change geometry.

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Text\FontWeight;

$layout = TextBlock::of('label', 'A long label that wraps', 12)
    ->weight(FontWeight::Bold)
    ->lineHeight(1.2)
    ->breakWords()
    ->align(Alignment::Center, Alignment::End)
    ->layout(new LayoutContext(), new Rect(0, 0, 120, 48));
```

The result contains:

- `frame`: the target rectangle.
- `lines`: `TextLineLayout` values with text, frame, and absolute baseline.
- `contentWidth` / `contentHeight`.
- `overflowX` / `overflowY`.
- `hasOverflow()`.
- `firstBaseline()` / `lastBaseline()`.

## Measurement

The default `CharWidthTextMeasurer` is deterministic and approximate. That is intentional: tests and server-side rendering need stable numbers, and a font engine gives neither. A more precise measurer can be injected through `LayoutContext`.

`measureLine()` and `wrap()` accept `FontWeight::Normal` or `FontWeight::Bold`. The built-in measurer applies a deterministic bold width factor instead of consulting a font. Its constructor accepts `heightFactor`, `ascentFactor`, and `boldFactor`, so a consumer can preserve established baselines while still using the shared measurement API:

```php
use Atelier\Layout\Text\CharWidthTextMeasurer;

$context = new LayoutContext(
    textMeasurer: new CharWidthTextMeasurer(
        heightFactor: 1.4,
        ascentFactor: 0.92,
    ),
);
```

## Alignment

Horizontal alignment positions each line inside the text frame. Vertical alignment positions the block inside the target rect. Both take the four [`Alignment` cases](composition/alignment.md), and in both `Stretch` behaves like `Start`: layout does not justify text.

Use `End` vertical alignment for bottom-snapped multiline labels, which is the common case for captions under a figure.

## Overflow

Overflow is reported, never hidden. `overflowX` and `overflowY` are the amounts by which the content exceeds its frame, and `hasOverflow()` is the shortcut. A renderer decides what to do: clip, shrink the font, add an ellipsis, or grow the box.

Leaving that decision to the consumer is why the package can serve a chart legend and a diagram node label with the same type.

## Inline Runs

`InlineGroup` lays out a row of items that share a baseline and a gap, when each item is a measured width rather than a full layout node. It is the lightweight form used for legend entries, key-value chips, and label runs.

<div class="figure-grid">
<figure><img src="images/inlinegroup-content.svg" alt="A row of items whose widths follow their content"><figcaption><code>contentSized()</code></figcaption></figure>
<figure><img src="images/inlinegroup-equal.svg" alt="A row of items sharing one common width"><figcaption><code>equal()</code></figcaption></figure>
</div>

```php
use Atelier\Layout\Alignment;
use Atelier\Layout\Inline\InlineGroup;

$row = InlineGroup::contentSized('tags')
    ->gap(6)
    ->align(Alignment::Center)
    ->add('draft', preferredWidth: 40)
    ->add('review', preferredWidth: 52)
    ->place($frame);
```

`equal()` gives every item the same width, which keeps a run of chips on a regular rhythm. `contentSized()` lets each item keep its measured width. `add()` also accepts `minWidth` and `fixedWidth` when one item must not shrink.
