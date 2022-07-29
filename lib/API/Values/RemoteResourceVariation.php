<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use function json_encode;
use function property_exists;

final class Variation
{
    public RemoteResource $resource;
    public string $url;

    public function __construct(RemoteResource $resource, string $url)
    {
        $this->resource = $resource;
        $this->url = $url;
    }

    public function __toString(): string
    {
        return json_encode(['url' => $url]);
    }
}
