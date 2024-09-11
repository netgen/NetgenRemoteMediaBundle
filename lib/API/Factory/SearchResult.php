<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Factory;

use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\Exception\Factory\InvalidDataException;

interface SearchResult
{
    /**
     * @throws InvalidDataException
     */
    public function create(mixed $data): Result;
}
