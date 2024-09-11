<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Factory;

use Netgen\RemoteMedia\API\Values\RemoteResource as RemoteResourceValue;
use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;

interface RemoteResource
{
    /**
     * @throws InvalidDataException
     */
    public function create(mixed $data): RemoteResourceValue;
}
