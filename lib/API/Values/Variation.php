<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use function json_encode;
use function property_exists;

final class Variation
{
    public ?string $url = null;
    public int $width = 0;
    public int $height = 0;

    /** @var array<string, int> */
    public array $coords = ['x' => 0, 'y' => 0];

    public function __construct(array $properties = [])
    {
        foreach ($properties as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    public function __toString(): string
    {
        return json_encode($this);
    }
}
