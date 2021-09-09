<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use function json_encode;

final class Variation
{
    public string $url;
    public int $width;
    public int $height;

    /** @var array<string, int> */
    public array $coords = ['x' => 0, 'y' => 0];

    public function __toString(): string
    {
        return json_encode($this);
    }
}
