<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API;

use Netgen\RemoteMedia\API\Search\Result;

interface SearchResultFactoryInterface
{
    /**
     * @param mixed $data
     *
     * @throws \Netgen\RemoteMedia\Exception\Factory\InvalidDataException
     */
    public function create($data): Result;
}
