<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Factory;

use Netgen\RemoteMedia\API\Search\Result;

interface SearchResult
{
    /**
     * @throws \Netgen\RemoteMedia\Exception\Factory\InvalidDataException
     */
    public function create(mixed $data): Result;
}
