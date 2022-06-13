<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway;

use Cloudinary;
use Cloudinary\Api as CloudinaryApi;
use Cloudinary\Search as CloudinarySearch;
use Cloudinary\Uploader as CloudinaryUploader;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Gateway;
use function array_key_exists;
use function array_map;
use function array_merge;
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

final class CloudinaryApiGateway extends Gateway
{
    protected Cloudinary $cloudinary;

    protected CloudinaryApi $cloudinaryApi;

    protected CloudinaryUploader $cloudinaryUploader;

    protected CloudinarySearch $cloudinarySearch;

    protected int $internalLimit;

    public function initCloudinary(string $cloudName, string $apiKey, string $apiSecret, bool $useSubdomains = false)
    {
        $this->cloudinary = new Cloudinary();
        $this->cloudinary->config(
            [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
                'cdn_subdomain' => $useSubdomains,
            ],
        );

        $this->cloudinaryUploader = new CloudinaryUploader();
        $this->cloudinaryApi = new CloudinaryApi();
        $this->cloudinarySearch = new CloudinarySearch();
    }

    public function setServices(
        Cloudinary $cloudinary,
        CloudinaryUploader $uploader,
        CloudinaryApi $api,
        CloudinarySearch $search
    ): void {
        $this->cloudinary = $cloudinary;
        $this->cloudinaryUploader = $uploader;
        $this->cloudinaryApi = $api;
        $this->cloudinarySearch = $search;
    }

    public function setInternalLimit(int $internalLimit): void
    {
        $this->internalLimit = $internalLimit;
    }

    public function usage(): array
    {
        $usage = $this->cloudinaryApi->usage();

        return [
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
        ];
    }

    public function upload(string $fileUri, array $options): array
    {
        return $this->cloudinaryUploader->upload($fileUri, $options);
    }

    public function getVariationUrl(string $source, array $options): string
    {
        return cloudinary_url_internal($source, $options);
    }

    public function search(Query $query): Result
    {
        $expression = $this->buildSearchExpression($query);

        $search = $this->cloudinarySearch
            ->expression($expression)
            ->max_results($query->getLimit())
            ->with_field('context')
            ->with_field('tags');

        if ($query->getNextCursor() !== null) {
            $search->next_cursor($query->getNextCursor());
        }

        $response = $search->execute();

        return Result::fromResponse($response);
    }

    public function searchCount(Query $query): int
    {
        $expression = $this->buildSearchExpression($query);

        $search = $this->cloudinarySearch
            ->expression($expression)
            ->max_results(0);

        $response = $search->execute();

        return Result::fromResponse($response)->getTotalCount();
    }

    public function listFolders(): array
    {
        return $this->cloudinaryApi
            ->root_folders()
            ->getArrayCopy()['folders'];
    }

    public function listSubFolders(string $parentFolder): array
    {
        return $this->cloudinaryApi
            ->subfolders($parentFolder)
            ->getArrayCopy()['folders'];
    }

    public function createFolder(string $path): void
    {
        $this->cloudinaryApi->create_folder($path);
    }

    public function countResources(): int
    {
        $usage = $this->cloudinaryApi->usage();

        return $usage['resources'];
    }

    public function countResourcesInFolder(string $folder): int
    {
        $expression = sprintf('folder:%s/*', $folder);

        $search = $this->cloudinarySearch
            ->expression($expression)
            ->max_results(0);

        $response = $search->execute();

        return $response['total_count'];
    }

    public function get(string $id, string $type): array
    {
        try {
            $id = array_map(function (string $part) {
                return urlencode($part);
            }, explode('/', $id));

            $id = implode('/', $id);

            return (array) $this->cloudinaryApi->resource(
                $id,
                [
                    'resource_type' => $type,
                ],
            );
        } catch (Cloudinary\Error $e) {
            return [];
        }
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

            if (array_key_exists('next_cursor', $result)) {
                $options['next_cursor'] = $result['next_cursor'];
            }
        } while (array_key_exists('next_cursor', $result));

        return $tags;
    }

    public function addTag(string $id, string $type, string $tag): void
    {
        $this->cloudinaryUploader->add_tag(
            $tag,
            [$id],
            [
                'resource_type' => $type,
            ],
        );
    }

    public function removeTag(string $id, string $type, string $tag): void
    {
        $this->cloudinaryUploader->remove_tag(
            $tag,
            [$id],
            [
                'resource_type' => $type,
            ],
        );
    }

    public function removeAllTags(string $id, string $type): void
    {
        $this->cloudinaryUploader->remove_all_tags([$id], ['resource_type' => $type]);
    }

    public function update(string $id, string $type, array $options): void
    {
        $options['resource_type'] = $type;

        $this->cloudinaryApi->update($id, $options);
    }

    public function getVideoThumbnail(string $id, array $options = []): string
    {
        return cl_video_thumbnail_path($id, $options);
    }

    public function getVideoTag(string $id, array $options = []): string
    {
        return cl_video_tag($id, $options);
    }

    public function getDownloadLink(string $id, string $type, array $options): string
    {
        $options['resource_type'] = $type;

        return $this->cloudinary->cloudinary_url($id, $options);
    }

    public function delete(string $id, string $type): void
    {
        $options = [
            'invalidate' => true,
            'resource_type' => $type,
        ];

        $this->cloudinaryUploader->destroy($id, $options);
    }

    private function buildSearchExpression(Query $query): string
    {
        $expressions = [];

        $resourceTypes = $query->getResourceType() ?? [];
        if (!is_array($resourceTypes)) {
            $resourceTypes = [$resourceTypes];
        }

        if (count($resourceTypes) > 0) {
            $resourceTypes = array_map(static fn ($value) => sprintf('resource_type:"%s"', $value), $resourceTypes);

            $expressions[] = '(' . implode(' OR ', $resourceTypes) . ')';
        }

        if ($query->getQuery() !== '') {
            $expressions[] = sprintf('%s*', $query->getQuery());
        }

        if ($query->getTag()) {
            $expressions[] = sprintf('tags:%s', $query->getTag());
        }

        if ($query->getFolder() !== null) {
            $expressions[] = sprintf('folder:"%s"', $query->getFolder());
        }

        $resourceIds = $query->getResourceIds();
        if (count($resourceIds) > 0) {
            $resourceIds = array_map(static fn ($value) => sprintf('public_id:"%s"', $value), $resourceIds);

            $expressions[] = '(' . implode(' OR ', $resourceIds) . ')';
        }

        return implode(' AND ', $expressions);
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
