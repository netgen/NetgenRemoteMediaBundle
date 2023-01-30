<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;

use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_string;
use function mb_substr;
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
        $expressions[] = $this->resolveFormats($query);
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

        foreach ($resourceTypes as $key => $resourceType) {
            $newResourceTypes = array_values(explode('|', $resourceType));

            if (count($newResourceTypes) > 1) {
                unset($resourceTypes[$key]);
            }

            if (count($newResourceTypes) > 0) {
                $resourceTypes = array_merge(array_values($resourceTypes), $newResourceTypes);
            }
        }

        $resourceTypes = array_map(static fn ($value) => sprintf('resource_type:"%s"', $value), array_unique($resourceTypes));

        return '(' . implode(' OR ', $resourceTypes) . ')';
    }

    private function resolveFormats(Query $query): ?string
    {
        if (count($query->getTypes()) === 0) {
            return null;
        }

        $formats = [];
        foreach ($query->getTypes() as $type) {
            switch ($type) {
                case RemoteResource::TYPE_IMAGE:
                    $formats = array_merge($formats, $this->resolveImageFormats($query));

                    break;

                case RemoteResource::TYPE_DOCUMENT:
                    $formats = array_merge($formats, $this->resolveDocumentFormats($query));

                    break;

                case RemoteResource::TYPE_VIDEO:
                    $formats = array_merge($formats, $this->resolveVideoFormats($query));

                    break;

                case RemoteResource::TYPE_AUDIO:
                    $formats = array_merge($formats, $this->resolveAudioFormats($query));

                    break;

                default:
                    $formats = array_merge($formats, $this->resolveOtherFormats($query));
            }
        }

        $formats = array_unique($formats);
        $notFormats = [];
        foreach ($formats as $key => $format) {
            if (mb_substr($format, 0, 1) === '!') {
                unset($formats[$key]);
                $notFormats[] = mb_substr($format, 1);
            }
        }

        $formats = array_map(static fn ($value) => sprintf('format="%s"', $value), $formats);
        $notFormats = array_map(static fn ($value) => sprintf('(!format="%s")', $value), $notFormats);

        $parts = [];

        if (count($formats) > 0) {
            $parts[] = '(' . implode(' OR ', $formats) . ')';
        }

        if (count($notFormats) > 0) {
            $parts[] = '(' . implode(' AND ', $notFormats) . ')';
        }

        if (count($parts) === 0) {
            return null;
        }

        return '(' . implode(' AND ', $parts) . ')';
    }

    private function resolveImageFormats($query): array
    {
        if (in_array(RemoteResource::TYPE_DOCUMENT, $query->getTypes(), true)) {
            return [];
        }

        return array_map(
            static fn ($format) => '!' . $format,
            $this->resourceTypeConverter->getDocumentFormats(),
        );
    }

    private function resolveDocumentFormats($query): array
    {
        if (in_array(RemoteResource::TYPE_IMAGE, $query->getTypes(), true)) {
            return [];
        }

        return $this->resourceTypeConverter->getDocumentFormats();
    }

    private function resolveVideoFormats($query): array
    {
        if (in_array(RemoteResource::TYPE_AUDIO, $query->getTypes(), true)) {
            return [];
        }

        return array_map(
            static fn ($format) => '!' . $format,
            $this->resourceTypeConverter->getAudioFormats(),
        );
    }

    private function resolveAudioFormats($query): array
    {
        if (in_array(RemoteResource::TYPE_VIDEO, $query->getTypes(), true)) {
            return [];
        }

        return $this->resourceTypeConverter->getAudioFormats();
    }

    private function resolveOtherFormats($query): array
    {
        if (in_array(RemoteResource::TYPE_DOCUMENT, $query->getTypes(), true)) {
            return [];
        }

        return array_map(
            static fn ($format) => '!' . $format,
            $this->resourceTypeConverter->getDocumentFormats(),
        );
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
