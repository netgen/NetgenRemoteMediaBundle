<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;

use function array_filter;
use function array_map;
use function array_unique;
use function count;
use function implode;
use function is_string;
use function sprintf;

final class SearchExpression
{
    private ResourceTypeConverter $resourceTypeConverter;

    public function __construct(ResourceTypeConverter $resourceTypeConverter)
    {
        $this->resourceTypeConverter = $resourceTypeConverter;
    }

    public function resolve(Query $query): string
    {
        $expressions = [];

        $expressions[] = $this->resolveResourceTypes($query);
        $expressions[] = $this->resolveSearchQuery($query);
        $expressions[] = $this->resolveFolders($query);
        $expressions[] = $this->resolveTags($query);
        $expressions[] = $this->resolveResourceIds($query);

        return implode(' AND ', array_filter($expressions));
    }

    private function resolveResourceTypes(Query $query): ?string
    {
        if (count($query->getTypes()) === 0) {
            return null;
        }

        $resourceTypes = array_unique(
            array_map(
                fn ($type) => $this->resourceTypeConverter->toCloudinaryType($type),
                $query->getTypes(),
            ),
        );

        $resourceTypes = array_map(static fn ($value) => sprintf('resource_type:"%s"', $value), $resourceTypes);

        return '(' . implode(' OR ', $resourceTypes) . ')';
    }

    private function resolveSearchQuery(Query $query): ?string
    {
        if (!is_string($query->getQuery()) || $query->getQuery() === '') {
            return null;
        }

        return sprintf('%s*', $query->getQuery());
    }

    private function resolveFolders(Query $query): ?string
    {
        if (count($query->getFolders()) === 0) {
            return null;
        }

        $folders = array_map(static fn ($value) => sprintf('folder:"%s"', $value), $query->getFolders());

        return '(' . implode(' OR ', $folders) . ')';
    }

    private function resolveTags(Query $query): ?string
    {
        if (count($query->getTags()) === 0) {
            return null;
        }

        $tags = array_map(static fn ($value) => sprintf('tags:"%s"', $value), $query->getTags());

        return '(' . implode(' OR ', $tags) . ')';
    }

    private function resolveResourceIds(Query $query): ?string
    {
        if (count($query->getRemoteIds()) === 0) {
            return null;
        }

        $resourceIds = array_unique(
            array_map(
                static fn ($remoteId) => CloudinaryRemoteId::fromRemoteId($remoteId)->getResourceId(),
                $query->getRemoteIds(),
            ),
        );

        $resourceIds = array_map(static fn ($value) => sprintf('public_id:"%s"', $value), $resourceIds);

        return '(' . implode(' OR ', $resourceIds) . ')';
    }
}
