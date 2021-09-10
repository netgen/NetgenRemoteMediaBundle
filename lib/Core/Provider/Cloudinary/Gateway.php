<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\Search\Query;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Search\Result;

/**
 * @internal
 */
abstract class Gateway
{
    /**
     * Returns various parameters from the provider such as
     * API rate limits, number of assets, and other stats.
     */
    abstract public function usage(): array;

    abstract public function upload(string $fileUri, array $options): array;

    abstract public function getVariationUrl(string $source, array $options): string;

    abstract public function search(Query $query): Result;

    abstract public function searchCount(Query $query): int;

    abstract public function listFolders(): array;

    abstract public function listSubFolders(string $parentFolder): array;

    abstract public function createFolder(string $path): void;

    abstract public function countResources(): int;

    abstract public function countResourcesInFolder(string $folder): int;

    abstract public function get(string $id, string $type): array;

    abstract public function listTags(): array;

    abstract public function addTag(string $id, string $type, string $tag): void;

    abstract public function removeTag(string $id, string $type, string $tag): void;

    abstract public function removeAllTags(string $id, string $type): void;

    abstract public function update(string $id, string $type, array $options): void;

    /**
     * @return string which contains video thumbnail image URL
     */
    abstract public function getVideoThumbnail(string $id, array $options = []): string;

    /**
     * @return string which contains HTML5 video tag
     */
    abstract public function getVideoTag(string $id, array $options = []): string;

    abstract public function getDownloadLink(string $id, string $type, array $options): string;

    abstract public function delete(string $id, string $type): void;
}
