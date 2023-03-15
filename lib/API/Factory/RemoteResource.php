<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Factory;

use Netgen\RemoteMedia\API\Values\RemoteResource as RemoteResourceValue;

interface RemoteResource
{
    /**
     * @throws \Netgen\RemoteMedia\Exception\Factory\InvalidDataException
     */
    public function create(mixed $data): RemoteResourceValue;
}
