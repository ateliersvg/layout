<?php

declare(strict_types=1);

namespace Atelier\Layout\Track;

use Atelier\Layout\Axis;
use Atelier\Layout\Geometry\Rect;

final readonly class PlacedTrackGroup
{
    /**
     * @param list<PlacedTrack> $tracks
     */
    public function __construct(
        public string $id,
        public Axis $axis,
        public Rect $frame,
        public array $tracks,
    ) {
    }

    public function track(string $id): ?PlacedTrack
    {
        foreach ($this->tracks as $track) {
            if ($track->id === $id) {
                return $track;
            }
        }

        return null;
    }
}
