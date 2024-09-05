<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway;

use Cloudinary\Asset\Image;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Search\SearchApi;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Asset\Media;
use Cloudinary\Tag\ImageTag;
use Cloudinary\Tag\VideoTag;
use Cloudinary\Api\Exception\NotFound as CloudinaryNotFound;
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
use function count;
use function date;
use function floor;
use function log;
use function max;
use function min;
use function round;
use function sprintf;

final class CloudinaryApiGateway implements GatewayInterface
{
    private AdminApi $adminApi;

    private UploadApi $uploadApi;

    private SearchApi $searchApi;

    public function __construct(
        private Cloudinary $cloudinary,
        private RemoteResourceFactoryInterface $remoteResourceFactory,
        private SearchResultFactoryInterface $searchResultFactory,
        private SearchExpressionResolver $searchExpressionResolver,
        private AuthTokenResolver $authTokenResolver
    ) {
        $this->adminApi = new AdminApi();
        $this->uploadApi = new UploadApi();
        $this->searchApi = new SearchApi();
    }

    public function setServices(
        Cloudinary $cloudinary,
        UploadApi $uploadApi,
        AdminApi $adminApi,
        SearchApi $searchApi,
    ): void {
        $this->cloudinary = $cloudinary;
        $this->uploadApi = $uploadApi;
        $this->adminApi = $adminApi;
        $this->searchApi = $searchApi;
    }

    public function usage(): StatusData
    {
        $usage = $this->adminApi->usage();

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
        $usage = $this->adminApi->usage();

        return (int) $usage['resources'];
    }

    public function countResourcesInFolder(string $folder): int
    {
        $expression = sprintf('folder:%s/*', $folder);

        $search = $this->searchApi
            ->expression($expression)
            ->maxResults(0);

        $response = $search->execute();

        return (int) $response['total_count'];
    }

    public function listFolders(): array
    {
        return array_map(
            static fn ($value) => $value['path'],
            $this->adminApi
                ->rootFolders()
                ->getArrayCopy()['folders'],
        );
    }

    public function listSubFolders(string $parentFolder): array
    {
        try {
            return array_map(
                static fn ($value) => $value['path'],
                $this->adminApi
                    ->subFolders($parentFolder)
                    ->getArrayCopy()['folders'],
            );
        } catch (CloudinaryNotFound $e) {
            throw new FolderNotFoundException(Folder::fromPath($parentFolder));
        }
    }

    public function createFolder(string $path): void
    {
        $this->adminApi->createFolder($path);
    }

    public function get(CloudinaryRemoteId $remoteId): RemoteResource
    {
        try {
            $response = $this->adminApi->asset(
                $remoteId->getResourceId(),
                [
                    'type' => $remoteId->getType(),
                    'resource_type' => $remoteId->getResourceType(),
                    'media_metadata' => true,
                    'image_metadata' => true,
                    'exif' => true,
                ],
            );

            return $this->remoteResourceFactory->create((array) $response);
        } catch (CloudinaryNotFound $e) {
            throw new RemoteResourceNotFoundException($remoteId->getRemoteId());
        }
    }

    public function upload(string $fileUri, array $options): RemoteResource
    {
        $response = $this->uploadApi->upload($fileUri, $options);
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
            $this->uploadApi->explicit($remoteId->getResourceId(), $options);
        } catch (CloudinaryNotFound $e) {
            throw new RemoteResourceNotFoundException($remoteId->getRemoteId());
        }
    }

    public function removeAllTagsFromResource(CloudinaryRemoteId $remoteId): void
    {
        $options = [
            'type' => $remoteId->getType(),
            'resource_type' => $remoteId->getResourceType(),
        ];

        try {
            $this->uploadApi->removeAllTags([$remoteId->getResourceId()], $options);
        } catch (CloudinaryNotFound $e) {
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

        $this->uploadApi->destroy($remoteId->getResourceId(), $options);
    }

    public function getAuthenticatedUrl(CloudinaryRemoteId $remoteId, AuthToken $token): string
    {
        $options = array_merge(
            [
                'type' => $remoteId->getType(),
                'resource_type' => $remoteId->getResourceType(),
                'secure' => true,
            ],
            $this->authTokenResolver->resolve($token),
        );

        return Media::fromParams($remoteId->getResourceId(), $options)->toUrl();
    }

    public function getVariationUrl(CloudinaryRemoteId $remoteId, array $transformations, ?AuthToken $token = null): string
    {
        $options = [
            'type' => $remoteId->getType(),
            'resource_type' => $remoteId->getResourceType(),
            'transformation' => $transformations,
            'secure' => true,
        ];

        if ($token instanceof AuthToken) {
            $options = array_merge(
                $options,
                $this->authTokenResolver->resolve($token),
            );
        }

        return Media::fromParams($remoteId->getResourceId(), $options)->toUrl();
    }

    public function search(Query $query): Result
    {
        $search = $this->searchApi
            ->expression($this->searchExpressionResolver->resolve($query))
            ->maxResults($query->getLimit())
            ->withField('context')
            ->withField('tags');

        if ($query->getNextCursor() !== null) {
            $search->nextCursor($query->getNextCursor());
        }

        $response = $search->execute();

        return $this->searchResultFactory->create($response);
    }

    public function searchCount(Query $query): int
    {
        $search = $this->searchApi
            ->expression($this->searchExpressionResolver->resolve($query))
            ->maxResults(0);

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
            $result = $this->adminApi->tags($options);
            $tags = array_merge($tags, $result['tags']);
            $nextCursor = $result['next_cursor'] ?? null;

            if ($nextCursor !== null) {
                $options['next_cursor'] = $nextCursor;
            }
        } while ($nextCursor);

        return $tags;
    }

    public function getVideoThumbnail(CloudinaryRemoteId $remoteId, array $options = [], ?AuthToken $token = null): string
    {
        $options['type'] = $remoteId->getType();
        $options['resource_type'] = $remoteId->getResourceType();
        $options['secure'] = true;

        if ($token instanceof AuthToken) {
            $options = array_merge(
                $options,
                $this->authTokenResolver->resolve($token),
            );
        }

        return Image::fromParams($remoteId->getResourceId(), $options)->toUrl();
    }

    public function getImageTag(CloudinaryRemoteId $remoteId, array $options = [], ?AuthToken $token = null): string
    {
        $options['type'] = $remoteId->getType();
        $options['resource_type'] = $remoteId->getResourceType();
        $options['secure'] = true;

        if ($token instanceof AuthToken) {
            $options = array_merge(
                $options,
                $this->authTokenResolver->resolve($token),
            );
        }

        return ImageTag::fromParams($remoteId->getResourceId(), $options)->toTag();
    }

    public function getVideoTag(CloudinaryRemoteId $remoteId, array $options = [], ?AuthToken $token = null): string
    {
        $options['type'] = $remoteId->getType();
        $options['resource_type'] = $remoteId->getResourceType();
        $options['secure'] = true;

        if ($token instanceof AuthToken) {
            $options = array_merge(
                $options,
                $this->authTokenResolver->resolve($token),
            );
        }

        return VideoTag::fromParams($remoteId->getResourceId(), $options)->toTag();
    }

    public function getDownloadLink(CloudinaryRemoteId $remoteId, array $options = [], ?AuthToken $token = null): string
    {
        $options['type'] = $remoteId->getType();
        $options['resource_type'] = $remoteId->getResourceType();
        $options['secure'] = true;

        if ($token instanceof AuthToken) {
            $options = array_merge(
                $options,
                $this->authTokenResolver->resolve($token),
            );
        }

        return Media::fromParams($remoteId->getResourceId(), $options)->toUrl();
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
