<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\API\RemoteResourceFactoryInterface;
use Netgen\RemoteMedia\API\Values\SearchResult;
use Netgen\RemoteMedia\API\SearchResultFactoryInterface;

final class SearchResultFactory implements SearchResultFactoryInterface
{
    private RemoteResourceFactoryInterface $remoteResourceFactory;

    public function __construct(RemoteResourceFactoryInterface $remoteResourceFactory)
    {
        $this->remoteResourceFactory = $remoteResourceFactory;
    }

    public function create($data): SearchResult
    {
        $resources = [];
        foreach ($data['resources'] ?? [] as $resourceData) {
            $resources[] = $this->remoteResourceFactory->create($resourceData);
        }

        return new SearchResult(
            (int) ($data['total_count'] ?? null),
            $data['next_cursor'] ?? null,
            $resources,
        );
    }
}
