# Changelog

Public API and behaviour changes only. Versions follow Semantic Versioning.

## Unreleased

### Added

- `WrapsText`, the greedy line breaking of `TextMeasurerInterface::wrap()` as a trait

## 0.7.0 - 2026-08-07

### Added

- Geometry values: `Rect`, `Point`, `Size`, `Insets`, `Bounds`, `Circle`, `BoxModel`, `GroupBounds`, `StrokePlacement`
- `RectIndex`, collision and free-space queries
- `Fit` and `FitMode`, shape-aware fitting
- Sizing vocabulary: `BoxConstraints`, `IntrinsicSize`, `Dimension`, `Length`, `InsetSpec`
- Node solving: `LayoutNodeInterface`, `FlexibleLayoutNodeInterface`, `LayoutContext`, `LayoutSolver`, `PlacedNode`, `PlacedTree`
- Composition nodes with their builders: `Stack`, `Grid`, `Group`, `Overlay`, `Spacer`, `TextBlock`
- `Stack::alignToBaseline()`, shared-baseline alignment on a horizontal stack
- Grid tracks, fixed, auto and fraction, with column and row spans
- `PlacedGrid`, `GridSlot`, `GridItem`, `TrackSize`, exposing placed track, slot and named-area metadata
- Spatial helpers: `TrackGroup`, `InlineGroup`, `LegendBlock`, `EdgeBand`, `AspectFrame`
- Link geometry: `OrthogonalConnector`, `OrthogonalConnection`, `ConnectionSegment`, `Port`, `PortSide`, `ConnectionLabel`, `ConnectionEndpointBadge`
- Text layout: `TextLayout`, `TextMeasurerInterface`, `CharWidthTextMeasurer`, `TextMetrics`, `TextBlockMetrics`, `TextLineLayout`, `FontWeight`
- Enumerations: `Alignment`, `Anchor`, `Axis`, `Distribution`
- Typed exceptions under `LayoutExceptionInterface`
- `ext-mbstring` as a requirement
