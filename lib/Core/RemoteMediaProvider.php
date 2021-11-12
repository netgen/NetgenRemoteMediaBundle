<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core;

use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\Variation;
use Netgen\RemoteMedia\Core\Transformation\Registry as TransformationRegistry;
use Psr\Log\LoggerInterface;

abstract class RemoteMediaProvider
{
    protected TransformationRegistry $registry;

    protected VariationResolver $variationResolver;

    protected ?LoggerInterface $logger;

    public function __construct(
        TransformationRegistry $registry,
        VariationResolver $variationsResolver,
        ?LoggerInterface $logger = null
    ) {
        $this->registry = $registry;
        $this->variationResolver = $variationsResolver;
        $this->logger = $logger;
    }

    abstract public function getIdentifier(): string;

    abstract public function usage(): array;

    abstract public function supportsFolders(): bool;

    abstract public function listFolders(): array;

    abstract public function listSubFolders(string $parentFolder): array;

    abstract public function createFolder(string $path): void;

    abstract public function countResources(): int;

    abstract public function countResourcesInFolder(string $folder): int;

    abstract public function listTags(): array;

    abstract public function upload(UploadFile $uploadFile, ?array $options = []): RemoteResource;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    abstract public function getRemoteResource(string $resourceId, string $resourceType = 'image'): RemoteResource;

    abstract public function searchResources(Query $query): Result;

    abstract public function searchResourcesCount(Query $query): int;

    abstract public function deleteResource(RemoteResource $resource): void;

    /**
     * Gets the remote media Variation.
     * If the remote media does not support variations, this method should return the Variation
     * with the url set to original resource.
     *
     * @param mixed $format
     */
    abstract public function buildVariation(RemoteResource $resource, string $variationGroup, $format, ?bool $secure = true): Variation;

    abstract public function addTagToResource(RemoteResource $resource, string $tag): void;

    abstract public function removeTagFromResource(RemoteResource $resource, string $tag): void;

    abstract public function removeAllTagsFromResource(RemoteResource $resource): void;

    /**
     * @param string[] $tags
     */
    abstract public function updateTags(RemoteResource $resource, array $tags): void;

    /**
     * Updates the resource context.
     * eg. alt text and caption:
     * context = [
     *      'caption' => 'new caption'
     *      'alt' => 'alt text'
     * ];.
     */
    abstract public function updateResourceContext(RemoteResource $resource, array $context): void;

    abstract public function getVideoThumbnail(RemoteResource $resource, ?array $options = []): string;

    abstract public function generateVideoTag(RemoteResource $resource, string $variationGroup, $format = []): string;

    abstract public function generateDownloadLink(RemoteResource $resource): string;

    protected function logError(string $message): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->error($message);
        }
    }
}