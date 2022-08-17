<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

final class CropSettings
{
    private string $variationName;

    private int $x = 0;

    private int $y = 0;

    private int $width = 0;

    private int $height = 0;

    public function __construct(
        string $variationName,
        int $x,
        int $y,
        int $width,
        int $height
    ) {
        $this->variationName = $variationName;
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    public static function fromArray(string $transformationName, array $coords): self
    {
        return new self(
            $transformationName,
            $coords['x'] ?? 0,
            $coords['y'] ?? 0,
            $coords['width'] ?? 0,
            $coords['height'] ?? 0,
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
