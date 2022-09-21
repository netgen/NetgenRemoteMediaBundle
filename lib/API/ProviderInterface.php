<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
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

    public function status(): StatusData;

    public function getSupportedTypes(): array;

    public function listFolders(?Folder $parent = null): array;

    public function createFolder(string $name, ?Folder $parent = null): Folder;

    public function count(): int;

    public function countInFolder(Folder $folder): int;

    public function listTags(): array;

    public function load(int $id): RemoteResource;

    public function loadByRemoteId(string $remoteId): RemoteResource;

    public function loadFromRemote(string $remoteId): RemoteResource;

    public function store(RemoteResource $resource): RemoteResource;

    public function remove(RemoteResource $resource): void;

    public function deleteFromRemote(RemoteResource $resource): void;

    public function loadLocation(int $id): RemoteResourceLocation;

    public function storeLocation(RemoteResourceLocation $location): RemoteResourceLocation;

    public function removeLocation(RemoteResourceLocation $resourceLocation): void;

    public function search(Query $query): Result;

    public function searchCount(Query $query): int;

    public function upload(ResourceStruct $resourceStruct): RemoteResource;

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

    public function generateHtmlTag(RemoteResource $resource, array $htmlAttributes = [], bool $forceVideo = false): string;

    public function generateVariationHtmlTag(
        RemoteResourceLocation $location,
        string $variationGroup,
        string $variationName,
        array $htmlAttributes = [],
        bool $forceVideo = false
    ): string;

    public function generateRawVariationHtmlTag(
        RemoteResource $resource,
        array $transformations = [],
        array $htmlAttributes = [],
        bool $forceVideo = false
    ): string;

    public function generateDownloadLink(RemoteResource $resource): string;
}
