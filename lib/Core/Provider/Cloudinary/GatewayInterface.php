<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;

/**
 * @internal
 */
interface GatewayInterface
{
    public function usage(): StatusData;

    public function countResources(): int;

    public function countResourcesInFolder(string $folder): int;

    public function listFolders(): array;

    public function listSubFolders(string $parentFolder): array;

    public function createFolder(string $path): void;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function get(CloudinaryRemoteId $remoteId): RemoteResource;

    public function upload(string $fileUri, array $options): RemoteResource;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function update(CloudinaryRemoteId $remoteId, array $options): void;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function delete(CloudinaryRemoteId $remoteId): void;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function getVariationUrl(CloudinaryRemoteId $remoteId, array $transformations): string;

    public function search(Query $query): Result;

    public function searchCount(Query $query): int;

    public function listTags(): array;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function getVideoThumbnail(CloudinaryRemoteId $remoteId, array $options = []): string;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function getImageTag(CloudinaryRemoteId $remoteId, array $options = []): string;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function getVideoTag(CloudinaryRemoteId $remoteId, array $options = []): string;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function getDownloadLink(CloudinaryRemoteId $remoteId): string;
}
