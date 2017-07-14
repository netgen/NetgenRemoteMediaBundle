<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use eZ\Publish\SPI\Persistence\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\HandlerInterface;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use Psr\Log\LoggerInterface;

abstract class RemoteMediaProvider
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry  */
    protected $registry;

    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver  */
    protected $variationResolver;

    /** @var \Psr\Log\LoggerInterface  */
    protected $logger;

    public function __construct(Registry $registry, VariationResolver $variationsResolver, LoggerInterface $logger = null)
    {
        $this->registry = $registry;
        $this->variationResolver = $variationsResolver;
        $this->logger = $logger;
    }

    /**
     * Logs the error if the logger is available
     *
     * @param $message
     */
    protected function logError($message)
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->error($message);
        }
    }

    /**
     * @return bool
     */
    abstract public function supportsContentBrowser();

    /**
     * @return bool
     */
    abstract public function supportsFolders();

    /**
     * Uploads the local resource to remote storage and builds the Value from the response.
     *
     * @param string $fileUri
     * @param string $fileName
     * @param array $options
     *
     * @return Value
     */
    abstract public function upload($fileUri, $fileName, $options = array());

    /**
     * Gets the remote media Variation.
     * If the remote media does not support variations, this method should return the Variation
     * with the url set to original resource.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $contentTypeIdentifier
     * @param string $format
     * @param bool $secure
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    abstract public function buildVariation(Value $value, $contentTypeIdentifier, $format, $secure = true);

    /**
     * Lists all available resources from the remote storage.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    abstract public function listResources($limit = 10, $offset = 0);

    /**
     * Lists all available folders.
     * If folders are not supported, should return empty array.
     *
     * @return array
     */
    abstract public function listFolders();

    /**
     * Counts available resources from the remote storage.
     *
     * @return int
     */
    abstract public function countResources();

    /**
     * Searches for the remote resource containing term in the query.
     *
     * @param string $query
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    abstract public function searchResources($query, $limit = 10, $offset = 0);

    /**
     * Searches for the remote resource tagged with a provided tag.
     *
     * @param string $tag
     *
     * @return array
     */
    abstract public function searchResourcesByTag($tag);

    /**
     * Returns the remote resource with provided id and type.
     *
     * @param mixed $resourceId
     * @param string $resourceType
     *
     * @return Value
     */
    abstract public function getRemoteResource($resourceId, $resourceType);

    /**
     * Adds tag to remote resource.
     *
     * @param string $resourceId
     * @param string $tag
     *
     * @return mixed
     */
    abstract public function addTagToResource($resourceId, $tag);

    /**
     * Removes tag from remote resource.
     *
     * @param string $resourceId
     * @param string $tag
     *
     * @return mixed
     */
    abstract public function removeTagFromResource($resourceId, $tag);

    /**
     * Updates the resource context.
     * eg. alt text and caption:
     * context = array(
     *      'caption' => 'new caption'
     *      'alt' => 'alt text'
     * );
     *
     * @param mixed $resourceId
     * @param string $resourceType
     * @param array $context
     *
     * @return mixed
     */
    abstract public function updateResourceContext($resourceId, $resourceType, $context);

    /**
     * Returns thumbnail url for the video with provided id.
     *
     * @param Value $value
     * @param array $options
     *
     * @return string
     */
    abstract public function getVideoThumbnail(Value $value, $options = array());

    /**
     * Generates html5 video tag for the video with provided id.
     *
     * @param Value $value
     * @param string $contentTypeIdentifier
     * @param string $format
     *
     * @return string
     */
    abstract public function generateVideoTag(Value $value, $contentTypeIdentifier, $format = '');

    /**
     * Removes the resource from the remote.
     *
     * @param $resourceId
     */
    abstract public function deleteResource($resourceId);

    /**
     * Generates the link to the remote resource.
     *
     * @param Value $value
     *
     * @return string
     */
    abstract public function generateDownloadLink(Value $value);

    /**
     * Returns unique identifier of the provided
     *
     * @return string
     */
    abstract public function getIdentifier();
}
