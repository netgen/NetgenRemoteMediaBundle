<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Result;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry;
use Psr\Log\LoggerInterface;

abstract class RemoteMediaProvider
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry */
    protected $registry;

    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver */
    protected $variationResolver;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    public function __construct(Registry $registry, VariationResolver $variationsResolver, LoggerInterface $logger = null)
    {
        $this->registry = $registry;
        $this->variationResolver = $variationsResolver;
        $this->logger = $logger;
    }

    abstract public function supportsContentBrowser(): bool;

    /**
     * @return bool
     */
    abstract public function supportsFolders();

    /**
     * Uploads the local resource to remote storage and builds the Value from the response.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\UploadFile $uploadFile
     * @param array $options
     */
    abstract public function upload(UploadFile $uploadFile, ?array $options = []): Value;

    /**
     * Gets the remote media Variation.
     * If the remote media does not support variations, this method should return the Variation
     * with the url set to original resource.
     *
     * @param string|array $format
     * @param bool $secure
     */
    abstract public function buildVariation(Value $value, string $contentTypeIdentifier, $format, ?bool $secure = true): Variation;

    /**
     * Lists all available folders.
     * If folders are not supported, should return empty array.
     */
    abstract public function listFolders(): array;

    /**
     * @param $folder
     */
    abstract public function countResourcesInFolder(string $folder): int;

    /**
     * Counts available resources from the remote storage.
     */
    abstract public function countResources(): int;

    /**
     * Searches for the remote resource containing term in the query.
     */
    abstract public function searchResources(Query $query): Result;

    /**
     * Returns the remote resource with provided id and type.
     *
     * @param mixed $resourceId
     * @param string $resourceType
     */
    abstract public function getRemoteResource(string $resourceId, ?string $resourceType = 'image'): Value;

    /**
     * Lists all available tags.
     */
    abstract public function listTags(): array;

    /**
     * Adds tag to remote resource.
     *
     * @return mixed
     */
    abstract public function addTagToResource(string $resourceId, string $tag);

    /**
     * Removes tag from remote resource.
     *
     * @return mixed
     */
    abstract public function removeTagFromResource(string $resourceId, string $tag);

    /**
     * Removes all tags from remote resource.
     *
     * @return mixed
     */
    abstract public function removeAllTagsFromResource(string $resourceId);

    /**
     * @param $resourceId
     * @param $tags
     *
     * @return mixed
     */
    abstract public function updateTags(string $resourceId, string $tags);

    /**
     * Updates the resource context.
     * eg. alt text and caption:
     * context = [
     *      'caption' => 'new caption'
     *      'alt' => 'alt text'
     * ];.
     *
     * @return mixed
     */
    abstract public function updateResourceContext(string $resourceId, string $resourceType, array $context);

    /**
     * Returns thumbnail url for the video with provided id.
     *
     * @param array $options
     */
    abstract public function getVideoThumbnail(Value $value, ?array $options = []): string;

    /**
     * Generates html5 video tag for the video with provided id.
     *
     *
     * @param string|array $format
     */
    abstract public function generateVideoTag(Value $value, string $contentTypeIdentifier, $format = ''): string;

    /**
     * Removes the resource from the remote.
     */
    abstract public function deleteResource(string $resourceId);

    /**
     * Generates the link to the remote resource.
     */
    abstract public function generateDownloadLink(Value $value): string;

    /**
     * Returns unique identifier of the provided.
     */
    abstract public function getIdentifier(): string;

    /**
     * Logs the error if the logger is available.
     *
     * @param $message
     */
    protected function logError(string $message)
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->error($message);
        }
    }
}
