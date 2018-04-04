<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary;

use Netgen\Bundle\RemoteMediaBundle\Exception\MimeCategoryParseException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\UploadFile;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

class CloudinaryProvider extends RemoteMediaProvider
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway */
    protected $gateway;

    protected $noExtensionMimeTypes;

    public function __construct(
        Registry $registry,
        VariationResolver $variationsResolver,
        Gateway $gateway,
        LoggerInterface $logger = null,
        array $noExtensionMimeTypes = array('image', 'video')
    ) {
        $this->gateway = $gateway;
        $this->noExtensionMimeTypes = $noExtensionMimeTypes;

        parent::__construct($registry, $variationsResolver, $logger);
    }

    /**
     * @return bool
     */
    public function supportsContentBrowser()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function supportsFolders()
    {
        return true;
    }

    /**
     * @param File $file
     *
     * @return mixed
     *
     * @throws MimeCategoryParseException
     */
    private function parseMimeCategory(File $file)
    {
        $parsedMime = explode('/', $file->getMimeType());
        if (count($parsedMime) !== 2) {
            throw new MimeCategoryParseException($file->getMimeType());
        }

        return $parsedMime[0];
    }

    /**
     * @param $publicId
     * @param UploadFile $uploadFile
     *
     * @return string
     */
    private function appendExtension($publicId, UploadFile $uploadFile)
    {
        $extension = $uploadFile->originalExtension();

        if (empty($extension)) {
            return $publicId;
        }

        $file = new File($uploadFile->uri());
        $mimeCategory = $this->parseMimeCategory($file);

        // cloudinary handles pdf in a weird way - it is considered an "image" but it delivers it with proper extension on download
        if ($extension !== 'pdf' && !in_array($mimeCategory, $this->noExtensionMimeTypes)) {
            $publicId .= '.' . $extension;
        }

        return $publicId;
    }

    /**
     * Prepares upload options for Cloudinary.
     * Every image with the same name will be overwritten.
     *
     * @param UploadFile $uploadFile
     * @param array $options
     *
     * @return array
     */
    protected function prepareUploadOptions(UploadFile $uploadFile, $options = array())
    {
        $clean = preg_replace("/[^\p{L}|\p{N}]+/u", '_', $uploadFile->originalFilename());
        $cleanFileName = preg_replace("/[\p{Z}]{2,}/u", '_', $clean);
        $fileName = rtrim($cleanFileName, '_');

        // check if overwrite is set, if it is, do not append random string
        $overwrite = isset($options['overwrite']) ? $options['overwrite'] : false;
        $invalidate = isset($options['invalidate']) ? $options['invalidate'] : $overwrite;

        $publicId = $overwrite ? $fileName : $fileName . '_' . base_convert(uniqid(), 16, 36);
        $publicId = $this->appendExtension($publicId, $uploadFile);

        if (!empty($options['folder'])) {
            $publicId = $options['folder'].'/'.$publicId;
        }

        return array(
            'public_id' => $publicId,
            'overwrite' => $overwrite,
            'invalidate' => $invalidate,
            'discard_original_filename' =>
                isset($options['discard_original_filename']) ? $options['discard_original_filename'] : true,
            'context' => array(
                'alt' => !empty($options['alt_text']) ? $options['alt_text'] : '',
                'caption' => !empty($options['caption']) ? $options['caption'] : '',
            ),
            'resource_type' => !empty($options['resource_type']) ? $options['resource_type'] : 'auto'
        );
    }

    /**
     * Uploads the local resource to remote storage and builds the Value from the response.
     *
     * @param UploadFile $uploadFile
     * @param array $options
     *
     * @return Value
     */
    public function upload(UploadFile $uploadFile, $options = array())
    {
        $options = $this->prepareUploadOptions($uploadFile, $options);

        $response = $this->gateway->upload($uploadFile->uri(), $options);

        return Value::createFromCloudinaryResponse($response);
    }

    /**
     * Builds transformation options for the provider to consume.
     *
     * @param Value $value
     * @param string $variationName
     * @param string $contentTypeIdentifier
     *
     * @return array options of the total sum of transformations for the provider to use
     */
    protected function processConfiguredVariation(Value $value, $variationName, $contentTypeIdentifier)
    {
        $configuredVariations = $this->variationResolver->getVariationsForContentType($contentTypeIdentifier);

        $options = array();

        if (!isset($configuredVariations[$variationName])) {
            return $options;
        }

        $variationConfiguration = $configuredVariations[$variationName];
        foreach ($variationConfiguration['transformations'] as $transformationIdentifier => $config) {
            try {
                $transformationHandler = $this->registry->getHandler(
                    $transformationIdentifier, $this->getIdentifier()
                );
            } catch (TransformationHandlerNotFoundException $e) {
                $this->logError($e->getMessage());

                continue;
            }

            try {
                $options[] = $transformationHandler->process($value, $variationName, $config);
            } catch (TransformationHandlerFailedException $e) {
                $this->logError($e->getMessage());

                continue;
            }
        }

        return $options;
    }

    /**
     * Gets the remote media Variation.
     * If $variationName is an array, it is treated as an explicit set of options to build the variation.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $contentTypeIdentifier
     * @param string|array $variationName
     * @param bool $secure
     *
     * @return Variation
     */
    public function buildVariation(Value $value, $contentTypeIdentifier, $variationName, $secure = true)
    {
        $variation = new Variation();
        $url = $secure ? $value->secure_url : $value->url;
        $variation->url = $url;

        if (empty($variationName)) {
            return $variation;
        }

        if (is_array($variationName)) {
            /*
             * This means the 'variationName' is actually an array with all the configuration
             * options provided, and we can pass those directly to the cloudinary
             */
            $options = $variationName;
        } else {
            $options = $this->processConfiguredVariation($value, $variationName, $contentTypeIdentifier);
        }

        $finalOptions['transformation'] = $options;
        $finalOptions['secure'] = $secure;

        $url = $this->gateway->getVariationUrl($value->resourceId, $finalOptions);
        $variation->url = $url;

        return $variation;
    }

    /**
     * Lists all available resources from the remote storage.
     *
     * @param int $limit
     * @param int $offset
     * @param string $resource_type
     *
     * @return array
     */
    public function listResources($limit = 10, $offset = 0, $resource_type = 'image')
    {
        $options = array(
            'tags' => true,
            'context' => true,
            'resource_type' => $resource_type
        );

        return $this->gateway->listResources($options, $limit, $offset);
    }

    /**
     * Counts available resources from the remote storage.
     *
     * @return int
     */
    public function countResources()
    {
        return $this->gateway->countResources();
    }

    /**
     * Lists all available folders.
     *
     * @return array
     */
    public function listFolders()
    {
        return $this->gateway->listFolders();
    }

    /**
     * @param $folder
     *
     * @return int
     */
    public function countResourcesInFolder($folder)
    {
        return $this->gateway->countResourcesInFolder($folder);
    }

    /**
     * Searches for the remote resource containing term in the query.
     *
     *
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @param string $resourceType
     *
     * @return array
     */
    public function searchResources($query, $limit = 10, $offset = 0, $resourceType = 'image')
    {
        $options = array(
            'SearchByTags' => false,
            'type' => 'upload',
            'resource_type' => $resourceType
        );

        return $this->gateway->search($query, $options, $limit, $offset);
    }

    /**
     * Searches for the remote resource tagged with a provided tag.
     *
     * @param string $tag
     * @param string $resourceType
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function searchResourcesByTag($tag, $limit = 10, $offset = 0, $resourceType = 'image')
    {
        $options = array(
            'SearchByTags' => true,
            'resource_type' => $resourceType
        );

        return $this->gateway->search(urlencode($tag), $options, $limit, $offset);
    }

    /**
     * Returns the remote resource with provided id and type.
     *
     * @param mixed $resourceId
     * @param string $resourceType
     *
     * @return Value
     */
    public function getRemoteResource($resourceId, $resourceType = 'image')
    {
        if (empty($resourceId)) {
            return new Value();
        }

        $options = array(
            'resource_type' => $resourceType,
            'max_results' => 1,
            'tags' => true,
            'context' => true,
        );

        $response = $this->gateway->get($resourceId, $options);

        if (empty($response)) {
            return new Value();
        }

        return Value::createFromCloudinaryResponse($response);
    }

    /**
     * Adds tag to remote resource.
     *
     * @param string $resourceId
     * @param string $tag
     *
     * @return mixed
     */
    public function addTagToResource($resourceId, $tag)
    {
        return $this->gateway->addTag($resourceId, $tag);
    }

    /**
     * Removes tag from remote resource.
     *
     * @param string $resourceId
     * @param string $tag
     *
     * @return mixed
     */
    public function removeTagFromResource($resourceId, $tag)
    {
        return $this->gateway->removeTag($resourceId, $tag);
    }

    public function updateTags($resourceId, $tags)
    {
        $options = array(
            'tags' => $tags
        );

        $this->gateway->update($resourceId, $options);
    }

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
    public function updateResourceContext($resourceId, $resourceType, $context)
    {
        $options = array(
            "context" => $context,
            "resource_type" => $resourceType
        );

        $this->gateway->update($resourceId, $options);
    }

    /**
     * Returns thumbnail url for the video with provided id.
     *
     * @param Value $value
     * @param array $options
     *
     * @return string
     */
    public function getVideoThumbnail(Value $value, $options = array())
    {
        if (!empty($options)) {
            $options['start_offset'] = !empty($options['start_offset']) ? $options['start_offset'] : 'auto';
            $options['resource_type'] = 'video';

            return $this->gateway->getVideoThumbnail($value->resourceId, $options);
        }

        $options['start_offset'] = !empty($options['start_offset']) ? $options['start_offset'] : 'auto';

        $options['crop'] = 'fit';
        $options['width'] = 320;
        $options['height'] = 240;
        $options['resource_type'] = 'video';

        return $this->gateway->getVideoThumbnail($value->resourceId, $options);
    }

    /**
     * Generates html5 video tag for the video with provided id.
     *
     * @param Value $value
     * @param string $contentTypeIdentifier
     * @param string $variationName
     *
     * @return string
     */
    public function generateVideoTag(Value $value, $contentTypeIdentifier, $variationName = '')
    {
        $finalOptions = array(
            'fallback_content' => 'Your browser does not support HTML5 video tags'
        );

        if (empty($variationName)) {
            return $this->gateway->getVideoTag($value->resourceId, $finalOptions);
        }

        if (is_array($variationName)) {
            $finalOptions = $variationName + $finalOptions;
        } else {
            $options = $this->processConfiguredVariation($value, $variationName, $contentTypeIdentifier);

            $finalOptions['transformation'] = $options;
            $finalOptions['secure'] = true;
        }

        return $this->gateway->getVideoTag($value->resourceId, $finalOptions);
    }

    /**
     * Generates the link to the remote resource.
     *
     * @param Value $value
     *
     * @return string
     */
    public function generateDownloadLink(Value $value)
    {
        $options = array(
            'type' => $value->metaData['type'],
            'resource_type' => $value->metaData['resource_type'],
            'flags' => 'attachment'
        );

        return $this->gateway->getDownloadLink($value->resourceId, $options);
    }

    /**
     * Removes the resource from the remote.
     *
     * @param $resourceId
     */
    public function deleteResource($resourceId)
    {
        $this->gateway->delete($resourceId);
    }

    /**
     * Returns unique identifier of the provided
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'cloudinary';
    }
}
