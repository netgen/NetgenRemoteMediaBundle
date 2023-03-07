<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway;

use Cloudinary;
use Cloudinary\Api as CloudinaryApi;
use Cloudinary\Search as CloudinarySearch;
use Cloudinary\Uploader as CloudinaryUploader;
use Netgen\RemoteMedia\API\Factory\RemoteResource as RemoteResourceFactoryInterface;
use Netgen\RemoteMedia\API\Factory\SearchResult as SearchResultFactoryInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\AuthToken as AuthTokenResolver;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\SearchExpression as SearchExpressionResolver;
use Netgen\RemoteMedia\Exception\FolderNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceExistsException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;

use function array_map;
use function array_merge;
use function cl_image_tag;
use function cl_video_tag;
use function cl_video_thumbnail_path;
use function cloudinary_url_internal;
use function count;
use function date;
use function explode;
use function floor;
use function implode;
use function is_array;
use function log;
use function max;
use function min;
use function round;
use function sprintf;
use function urlencode;

final class CloudinaryApiGateway implements GatewayInterface
{
    private Cloudinary $cloudinary;

    private CloudinaryApi $cloudinaryApi;

    private CloudinaryUploader $cloudinaryUploader;

    private CloudinarySearch $cloudinarySearch;

    private RemoteResourceFactoryInterface $remoteResourceFactory;

    private SearchResultFactoryInterface $searchResultFactory;

    private SearchExpressionResolver $searchExpressionResolver;

    private AuthTokenResolver $authTokenResolver;

    public function __construct(
        Cloudinary $cloudinary,
        RemoteResourceFactoryInterface $remoteResourceFactory,
        SearchResultFactoryInterface $searchResultFactory,
        SearchExpressionResolver $searchExpressionResolver,
        AuthTokenResolver $authTokenResolver
    ) {
        $this->cloudinary = $cloudinary;
        $this->cloudinaryUploader = new CloudinaryUploader();
        $this->cloudinaryApi = new CloudinaryApi();
        $this->cloudinarySearch = new CloudinarySearch();
        $this->remoteResourceFactory = $remoteResourceFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->searchExpressionResolver = $searchExpressionResolver;
        $this->authTokenResolver = $authTokenResolver;
    }

    public function setServices(
        Cloudinary $cloudinary,
        CloudinaryUploader $cloudinaryUploader,
        CloudinaryApi $cloudinaryApi,
        CloudinarySearch $cloudinarySearch
    ): void {
        $this->cloudinary = $cloudinary;
        $this->cloudinaryUploader = $cloudinaryUploader;
        $this->cloudinaryApi = $cloudinaryApi;
        $this->cloudinarySearch = $cloudinarySearch;
    }

    public function usage(): StatusData
    {
        $usage = $this->cloudinaryApi->usage();

        return new StatusData([
            'plan' => $usage['plan'],
            'rate_limit_allowed' => $usage->rate_limit_allowed,
            'rate_limit_remaining' => $usage->rate_limit_remaining,
            'rate_limit_reset_at' => date('d.m.Y H:i:s', $usage->rate_limit_reset_at),
            'objects' => $usage['objects']['usage'],
            'resources' => $usage['resources'],
            'derived_resources' => $usage['derived_resources'],
            'transformations_usage' => $usage['transformations']['usage'],
            'transformations_credit_usage' => $usage['transformations']['credits_usage'] ?? null,
            'storage_usage' => $this->formatBytes($usage['storage']['usage']),
            'storage_credit_usage' => $usage['storage']['credits_usage'] ?? null,
            'bandwidth_usage' => $this->formatBytes($usage['bandwidth']['usage']),
            'bandwidth_credit_usage' => $usage['bandwidth']['credits_usage'] ?? null,
            'credits_usage' => $usage['credits']['usage'] ?? null,
            'credits_limit' => $usage['credits']['limit'] ?? null,
            'credits_usage_percent' => $usage['credits']['used_percent'] ?? null,
        ]);
    }

    public function isEncryptionEnabled(): bool
    {
        return $this->authTokenResolver->hasEncryptionKey();
    }

    public function countResources(): int
    {
        $usage = $this->cloudinaryApi->usage();

        return (int) $usage['resources'];
    }

    public function countResourcesInFolder(string $folder): int
    {
        $expression = sprintf('folder:%s/*', $folder);

        $search = $this->cloudinarySearch
            ->expression($expression)
            ->max_results(0);

        $response = $search->execute();

        return (int) $response['total_count'];
    }

    public function listFolders(): array
    {
        return array_map(
            static fn ($value) => $value['path'],
            $this->cloudinaryApi
                ->root_folders()
                ->getArrayCopy()['folders'],
        );
    }

    public function listSubFolders(string $parentFolder): array
    {
        try {
            return array_map(
                static fn ($value) => $value['path'],
                $this->cloudinaryApi
                    ->subfolders($parentFolder)
                    ->getArrayCopy()['folders'],
            );
        } catch (CloudinaryApi\NotFound $e) {
            throw new FolderNotFoundException(Folder::fromPath($parentFolder));
        }
    }

    public function createFolder(string $path): void
    {
        $this->cloudinaryApi->create_folder($path);
    }

    public function get(CloudinaryRemoteId $remoteId): RemoteResource
    {
        try {
            $response = $this->cloudinaryApi->resource(
                $remoteId->getResourceId(),
                [
                    'type' => $remoteId->getType(),
                    'resource_type' => $remoteId->getResourceType(),
                ],
            );

            return $this->remoteResourceFactory->create((array) $response);
        } catch (CloudinaryApi\NotFound $e) {
            throw new RemoteResourceNotFoundException($remoteId->getRemoteId());
        }
    }

    public function upload(string $fileUri, array $options): RemoteResource
    {
        $response = $this->cloudinaryUploader->upload($fileUri, $options);
        $resource = $this->remoteResourceFactory->create((array) $response);

        if ($response['existing'] ?? false) {
            throw new RemoteResourceExistsException($resource);
        }

        return $resource;
    }

    public function update(CloudinaryRemoteId $remoteId, array $options): void
    {
        $options['type'] = $remoteId->getType();
        $options['resource_type'] = $remoteId->getResourceType();

        try {
            $this->cloudinaryUploader->explicit($remoteId->getResourceId(), $options);
        } catch (CloudinaryApi\NotFound $e) {
            throw new RemoteResourceNotFoundException($remoteId->getRemoteId());
        }
    }

    public function removeAllTagsFromResource(CloudinaryRemoteId $remoteId): void
    {
        $options['type'] = $remoteId->getType();
        $options['resource_type'] = $remoteId->getResourceType();

        try {
            $this->cloudinaryUploader->remove_all_tags([$remoteId->getResourceId()], $options);
        } catch (CloudinaryApi\NotFound $e) {
            throw new RemoteResourceNotFoundException($remoteId->getRemoteId());
        }
    }

    public function delete(CloudinaryRemoteId $remoteId): void
    {
        $options = [
            'invalidate' => true,
            'type' => $remoteId->getType(),
            'resource_type' => $remoteId->getResourceType(),
        ];

        $this->cloudinaryUploader->destroy($remoteId->getResourceId(), $options);
    }

    public function getAuthenticatedUrl(CloudinaryRemoteId $remoteId, AuthToken $token, array $transformations = []): string
    {
        $options = array_merge(
            [
                'type' => $remoteId->getType(),
                'resource_type' => $remoteId->getResourceType(),
                'transformation' => $transformations,
                'secure' => true,
            ],
            $this->authTokenResolver->resolve($token),
        );

        return cloudinary_url_internal($remoteId->getResourceId(), $options);
    }

    public function getVariationUrl(CloudinaryRemoteId $remoteId, array $transformations): string
    {
        $options = [
            'type' => $remoteId->getType(),
            'resource_type' => $remoteId->getResourceType(),
            'transformation' => $transformations,
            'secure' => true,
        ];

        return cloudinary_url_internal($remoteId->getResourceId(), $options);
    }

    public function search(Query $query): Result
    {
        $search = $this->cloudinarySearch
            ->expression($this->searchExpressionResolver->resolve($query))
            ->max_results($query->getLimit())
            ->with_field('context')
            ->with_field('tags');

        if ($query->getNextCursor() !== null) {
            $search->next_cursor($query->getNextCursor());
        }

        $response = $search->execute();

        return $this->searchResultFactory->create($response);
    }

    public function searchCount(Query $query): int
    {
        $search = $this->cloudinarySearch
            ->expression($this->searchExpressionResolver->resolve($query))
            ->max_results(0);

        $response = $search->execute();

        return $response['total_count'] ?? 0;
    }

    public function listTags(): array
    {
        $options = [
            'max_results' => 500,
        ];

        $tags = [];
        do {
            $result = $this->cloudinaryApi->tags($options);
            $tags = array_merge($tags, $result['tags']);
            $nextCursor = $result['next_cursor'] ?? null;

            if ($nextCursor) {
                $options['next_cursor'] = $nextCursor;
            }
        } while ($nextCursor);

        return $tags;
    }

    public function getVideoThumbnail(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        $options['type'] = $remoteId->getType();
        $options['resource_type'] = $remoteId->getResourceType();
        $options['secure'] = true;

        return cl_video_thumbnail_path($remoteId->getResourceId(), $options);
    }

    public function getImageTag(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        $options['type'] = $remoteId->getType();
        $options['resource_type'] = $remoteId->getResourceType();
        $options['secure'] = true;

        return cl_image_tag($remoteId->getResourceId(), $options);
    }

    public function getVideoTag(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        $options['type'] = $remoteId->getType();
        $options['resource_type'] = $remoteId->getResourceType();
        $options['secure'] = true;

        return cl_video_tag($remoteId->getResourceId(), $options);
    }

    public function getDownloadLink(CloudinaryRemoteId $remoteId, array $options = []): string
    {
        $options['type'] = $remoteId->getType();
        $options['resource_type'] = $remoteId->getResourceType();
        $options['secure'] = true;

        return $this->cloudinary->cloudinary_url($remoteId->getResourceId(), $options);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'YB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
