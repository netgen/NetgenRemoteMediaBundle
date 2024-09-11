<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory;

use Netgen\RemoteMedia\API\Factory\RemoteResource as RemoteResourceFactory;
use Netgen\RemoteMedia\API\Factory\SearchResult as SearchResultFactoryInterface;
use Netgen\RemoteMedia\API\Search\Result;

final class SearchResult implements SearchResultFactoryInterface
{
    public function __construct(
        private RemoteResourceFactory $remoteResourceFactory,
    ) {}

    public function create(mixed $data): Result
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
