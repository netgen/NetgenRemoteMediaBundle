<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

final class CropSettings
{
    public function __construct(
        private string $variationName,
        private int $x = 0,
        private int $y = 0,
        private int $width = 0,
        private int $height = 0
    ) {
    }

    public static function fromArray(string $transformationName, array $coords): self
    {
        return new self(
            $transformationName,
            $coords['x'] ?? 0,
            $coords['y'] ?? 0,
            $coords['width'] ?? $coords['w'] ?? 0,
            $coords['height'] ?? $coords['h'] ?? 0,
        );
    }

    public function getVariationName(): string
    {
        return $this->variationName;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}
