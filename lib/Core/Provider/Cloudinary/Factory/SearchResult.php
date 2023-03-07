<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory;

use Netgen\RemoteMedia\API\Factory\RemoteResource as RemoteResourceFactory;
use Netgen\RemoteMedia\API\Factory\SearchResult as SearchResultFactoryInterface;
use Netgen\RemoteMedia\API\Search\Result;

final class SearchResult implements SearchResultFactoryInterface
{
    private RemoteResourceFactory $remoteResourceFactory;

    public function __construct(RemoteResourceFactory $remoteResourceFactory)
    {
        $this->remoteResourceFactory = $remoteResourceFactory;
    }

    public function create($data): Result
    {
        $resources = [];
        foreach ($data['resources'] ?? [] as $resourceData) {
            $resources[] = $this->remoteResourceFactory->create($resourceData);
        }

        return new Result(
            (int) ($data['total_count'] ?? 0),
            $data['next_cursor'] ?? null,
            $resources,
        );
    }
}
