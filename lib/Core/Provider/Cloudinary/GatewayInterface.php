<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Exception\FolderNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceExistsException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;

/**
 * @internal
 */
interface GatewayInterface
{
    public function usage(): StatusData;

    public function isEncryptionEnabled(): bool;

    public function countResources(): int;

    public function countResourcesInFolder(string $folder): int;

    /**
     * @return string[]
     */
    public function listFolders(): array;

    /**
     * @return string[]
     *
     * @throws FolderNotFoundException
     */
    public function listSubFolders(string $parentFolder): array;

    public function createFolder(string $path): void;

    /**
     * @throws RemoteResourceNotFoundException
     */
    public function get(CloudinaryRemoteId $remoteId): RemoteResource;

    /**
     * @throws RemoteResourceExistsException
     */
    public function upload(string $fileUri, array $options): RemoteResource;

    /**
     * @throws RemoteResourceNotFoundException
     */
    public function update(CloudinaryRemoteId $remoteId, array $options): void;

    /**
     * @throws RemoteResourceNotFoundException
     */
    public function removeAllTagsFromResource(CloudinaryRemoteId $remoteId): void;

    public function delete(CloudinaryRemoteId $remoteId): void;

    public function getAuthenticatedUrl(CloudinaryRemoteId $remoteId, AuthToken $token): string;

    public function getVariationUrl(CloudinaryRemoteId $remoteId, array $transformations, ?AuthToken $token = null): string;

    public function search(Query $query): Result;

    public function searchCount(Query $query): int;

    /**
     * @return string[]
     */
    public function listTags(): array;

    public function getVideoThumbnail(CloudinaryRemoteId $remoteId, array $options = [], ?AuthToken $token = null): string;

    public function getImageTag(CloudinaryRemoteId $remoteId, array $options = [], ?AuthToken $token = null): string;

    public function getVideoTag(CloudinaryRemoteId $remoteId, array $options = [], ?AuthToken $token = null): string;

    public function getDownloadLink(CloudinaryRemoteId $remoteId, array $options = [], ?AuthToken $token = null): string;
}
