<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\API\Values\AuthToken;

/**
 * @internal
 */
interface GatewayInterface
{
    public function usage(): StatusData;

    public function countResources(): int;

    public function countResourcesInFolder(string $folder): int;

    /**
     * @return string[]
     */
    public function listFolders(): array;

    /**
     * @return string[]
     *
     * @throws \Netgen\RemoteMedia\Exception\FolderNotFoundException
     */
    public function listSubFolders(string $parentFolder): array;

    public function createFolder(string $path): void;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function get(CloudinaryRemoteId $remoteId): RemoteResource;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceExistsException
     */
    public function upload(string $fileUri, array $options): RemoteResource;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function update(CloudinaryRemoteId $remoteId, array $options): void;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function removeAllTagsFromResource(CloudinaryRemoteId $remoteId): void;

    public function delete(CloudinaryRemoteId $remoteId): void;

    public function getAuthenticatedUrl(CloudinaryRemoteId $remoteId, AuthToken $token, array $transformations = []): string;

    public function getVariationUrl(CloudinaryRemoteId $remoteId, array $transformations): string;

    public function search(Query $query): Result;

    public function searchCount(Query $query): int;

    /**
     * @return string[]
     */
    public function listTags(): array;

    public function getVideoThumbnail(CloudinaryRemoteId $remoteId, array $options = []): string;

    public function getImageTag(CloudinaryRemoteId $remoteId, array $options = []): string;

    public function getVideoTag(CloudinaryRemoteId $remoteId, array $options = []): string;

    public function getDownloadLink(CloudinaryRemoteId $remoteId): string;
}
