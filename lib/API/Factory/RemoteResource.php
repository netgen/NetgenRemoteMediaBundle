<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API;

use Netgen\RemoteMedia\API\Values\RemoteResource;

interface RemoteResourceFactoryInterface
{
    /**
     * @param mixed $data
     *
     * @throws \Netgen\RemoteMedia\Exception\Factory\InvalidDataException
     */
    public function create($data): RemoteResource;
}
