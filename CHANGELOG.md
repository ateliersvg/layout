# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

### Changed

### Fixed

## [0.7.0] - 2026-08-07

First tagged release. The package returns geometry only: no markup, no DOM, no
rendered output.

### Added

- Geometry values: `Rect`, `Point`, `Size`, `Insets`, `Bounds`, `Circle`,
  `BoxModel`, `GroupBounds`, `StrokePlacement`.
- `RectIndex` for collision and free-space queries.
- Shape-aware fitting: `Fit` and `FitMode`.
- Sizing vocabulary: `BoxConstraints`, `IntrinsicSize`, `Dimension`, `Length`,
  `InsetSpec`.
- Node solving: `LayoutNodeInterface`, `FlexibleLayoutNodeInterface`,
  `LayoutContext`, `LayoutSolver`, `PlacedNode`, `PlacedTree`.
- Composition nodes: `Stack`, `Grid`, `Group`, `Overlay`, `Spacer`, `TextBlock`,
  with their builders. Horizontal `Stack` supports shared-baseline alignment via
  `alignToBaseline()`.
- Grid support: fixed, auto and fraction tracks, row tracks, column and row
  spans, global alignment and per-item overrides. `PlacedGrid`, `GridSlot`,
  `GridItem` and `TrackSize` expose placed track, slot and named-area metadata.
- Higher-level spatial helpers: `TrackGroup`, `InlineGroup`, `LegendBlock`,
  `EdgeBand`, `AspectFrame`.
- Link geometry between boxes: `OrthogonalConnector`, `OrthogonalConnection`,
  `ConnectionSegment`, `Port`, `PortSide`, `ConnectionLabel`,
  `ConnectionEndpointBadge` and their placement types.
- Deterministic text layout: `TextLayout`, `TextMeasurerInterface`,
  `CharWidthTextMeasurer`, `TextMetrics`, `TextBlockMetrics`, `TextLineLayout`,
  `FontWeight`.
- Enumerations: `Alignment`, `Anchor`, `Axis`, `Distribution`.
- Typed exceptions under `LayoutExceptionInterface`.
- `ext-mbstring` declared as a requirement.

### Documented

- The y axis points down, matching the SVG and Canvas convention. Consumers that
  need y-up flip in their own scale.
- Zero-sized rectangles are a valid state and return empty geometry. Negative
  dimensions are rejected. No operation produces `NaN`.
- Where the borrowed CSS vocabulary stops: `Alignment::Stretch` stretches
  unconditionally, fraction tracks ignore content, and percentage padding
  resolves against the matching axis rather than the inline size.
