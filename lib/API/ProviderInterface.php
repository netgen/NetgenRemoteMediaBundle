<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\API\Values\StatusData;

interface ProviderInterface
{
    public function getIdentifier(): string;

    public function supportsFolders(): bool;

    public function supportsDelete(): bool;

    public function supportsTags(): bool;

    public function supportsProtectedResources(): bool;

    public function status(): StatusData;

    public function getSupportedTypes(): array;

    public function getSupportedVisibilities(): array;

    /**
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
     */
    public function listFolders(?Folder $parent = null): array;

    /**
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
     */
    public function createFolder(string $name, ?Folder $parent = null): Folder;

    public function count(): int;

    public function countInFolder(Folder $folder): int;

    /**
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
     */
    public function listTags(): array;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function load(int $id): RemoteResource;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function loadByRemoteId(string $remoteId): RemoteResource;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function loadFromRemote(string $remoteId): RemoteResource;

    public function store(RemoteResource $resource): RemoteResource;

    public function remove(RemoteResource $resource): void;

    public function deleteFromRemote(RemoteResource $resource): void;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException
     */
    public function loadLocation(int $id): RemoteResourceLocation;

    public function storeLocation(RemoteResourceLocation $location): RemoteResourceLocation;

    public function removeLocation(RemoteResourceLocation $resourceLocation): void;

    /**
     * @throws \Netgen\RemoteMedia\Exception\NamedRemoteResourceNotFoundException
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function loadNamedRemoteResource(string $name): RemoteResource;

    /**
     * @throws \Netgen\RemoteMedia\Exception\NamedRemoteResourceLocationNotFoundException
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function loadNamedRemoteResourceLocation(string $name): RemoteResourceLocation;

    public function search(Query $query): Result;

    public function searchCount(Query $query): int;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceExistsException
     */
    public function upload(ResourceStruct $resourceStruct): RemoteResource;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function updateOnRemote(RemoteResource $resource): void;

    /**
     * Gets the remote media Variation from configuration.
     * If the remote media does not support variations, this method should return the Variation
     * with the url set to original resource.
     */
    public function buildVariation(RemoteResourceLocation $location, string $variationGroup, string $variationName): RemoteResourceVariation;

    /**
     * Gets the remote media Variation based on provided RAW transformations.
     * If the remote media does not support variations, this method should return the Variation
     * with the url set to original resource.
     */
    public function buildRawVariation(RemoteResource $resource, array $transformations): RemoteResourceVariation;

    public function buildVideoThumbnail(RemoteResource $resource, ?int $startOffset = null): RemoteResourceVariation;

    /**
     * Gets the remote video variation from configuration.
     * If the remote media does not support variations, this method should return the Variation
     * with the url set to original thumbnail.
     */
    public function buildVideoThumbnailVariation(
        RemoteResourceLocation $location,
        string $variationGroup,
        string $variationName,
        ?int $startOffset = null
    ): RemoteResourceVariation;

    /**
     * Gets the remote video variation based on RAW format.
     * If the remote media does not support variations, this method should return the Variation
     * with the url set to original thumbnail.
     */
    public function buildVideoThumbnailRawVariation(RemoteResource $resource, array $transformations = [], ?int $startOffset = null): RemoteResourceVariation;

    public function generateHtmlTag(RemoteResource $resource, array $htmlAttributes = [], bool $forceVideo = false, bool $useThumbnail = false): string;

    public function generateVariationHtmlTag(
        RemoteResourceLocation $location,
        string $variationGroup,
        string $variationName,
        array $htmlAttributes = [],
        bool $forceVideo = false,
        bool $useThumbnail = false
    ): string;

    public function generateRawVariationHtmlTag(
        RemoteResource $resource,
        array $transformations = [],
        array $htmlAttributes = [],
        bool $forceVideo = false,
        bool $useThumbnail = false
    ): string;

    public function generateDownloadLink(RemoteResource $resource): string;

    public function authenticateRemoteResource(RemoteResource $resource, AuthToken $token): AuthenticatedRemoteResource;

    public function authenticateRemoteResourceVariation(RemoteResourceVariation $variation, AuthToken $token): AuthenticatedRemoteResource;
}
