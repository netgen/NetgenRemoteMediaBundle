<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\API\RemoteResource;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Factory\SearchResult as SearchResultFactoryInterface;

final class SearchResultFactory implements SearchResultFactoryInterface
{
    private RemoteResource $remoteResourceFactory;

    public function __construct(RemoteResource $remoteResourceFactory)
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
