<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API;

use Netgen\RemoteMedia\API\Values\SearchResult;

interface SearchResultFactoryInterface
{
    /**
     * @param mixed $data
     */
    public function create($data): SearchResult;
}
