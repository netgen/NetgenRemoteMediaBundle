<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary;

use \Cloudinary;
use \Cloudinary\Uploader;
use \Cloudinary\Api;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Psr\Log\LoggerInterface;


class CloudinaryProvider extends RemoteMediaProvider
{
    /**
     * @var \Cloudinary
     */
    protected $cloudinary;

    /**
     * @var \Cloudinary\Api
     */
    protected $cloudinaryApi;

    /**
     * @var \Cloudinary\Uploader
     */
    protected $cloudinaryUploader;

    protected $folderName = '';

    public function initCloudinary($cloudName, $apiKey, $apiSecret)
    {
        $this->cloudinary = new Cloudinary();
        $this->cloudinary->config(
            array(
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            )
        );

        $this->cloudinaryUploader = new Uploader();
        $this->cloudinaryApi = new Api();
    }

//    public function setFolderName($folderName = null)
//    {
//        $this->folderName = $folderName;
//    }

    /**
     * Prepares upload options for Cloudinary.
     * Every image with the same name will be overwritten.
     *
     * @param string $fileName
     * @param array $options
     *
     * @return array
     */
    protected function prepareUploadOptions($fileName, $options = array())
    {
        // @todo: folders should be handled differently, not through siteacess parameter
        //$id = $this->folderName ? $this->folderName . '/' . $fileName : $fileName;

        $publicId = $fileName . '_' . base_convert(uniqid(), 16, 36);

        return array(
            'public_id' => $publicId,
            'overwrite' => false,
            'discard_original_filename' => true,
            'context' => array(
                'alt' => !empty($options['alt_text']) ? $options['alt_text'] : '',
                'caption' => !empty($options['caption']) ? $options['caption'] : '',
            ),
            'resource_type' => !empty($options['resource_type']) ? $options['resource_type'] : 'auto'
        );
    }

    /**
     * Cleans up the file name for uploading.
     *
     * @param string $fileName
     *
     * @return string
     */
    protected function filenameCleanUp($fileName)
    {
        $clean = preg_replace("/[^\p{L}|\p{N}]+/u", '_', $fileName);
        $cleanFileName = preg_replace("/[\p{Z}]{2,}/u", '_', $clean);

        return rtrim($cleanFileName, '_');
    }

    /**
     * Uploads the local resource to remote storage and builds the Value from the response.
     *
     * @param string $fileUri
     * @param string $fileName
     * @param array $options
     *
     * @return Value
     */
    public function upload($fileUri, $fileName, $options = array())
    {
        $fileName = $this->filenameCleanUp($fileName);
        $options = $this->prepareUploadOptions($fileName, $options);
        $response = $this->cloudinaryUploader->upload($fileUri, $options);

        return $this->getValueFromResponse($response);
    }

    /**
     * Gets the absolute url of the remote resource formatted according to options provided.
     *
     * @param string $source
     * @param array $options
     *
     * @return string
     */
    protected function getFormattedUrl($source, $options = array())
    {
        return cloudinary_url_internal($source, $options);
    }

    /**
     * Transforms response from the remote storage to field type value.
     *
     * @param mixed $response
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    protected function getValueFromResponse($response)
    {
        $metaData = array(
            'version' => !empty($response['version']) ? $response['version'] : '',
            'width' => !empty($response['width']) ? $response['width'] : '',
            'height' => !empty($response['height']) ? $response['height'] : '',
            'format' => !empty($response['format']) ? $response['format'] : '',
            'resource_type' => !empty($response['resource_type']) ? $response['resource_type'] : '',
            'created' => !empty($response['created_at']) ? $response['created_at'] : '',
            'tags' => $response['tags'] ?: array(),
            'signature' => !empty($response['signature']) ? $response['signature'] : '',
            'type' => !empty($response['type']) ? $response['type'] : '',
            'etag' => !empty($response['etag']) ? $response['etag'] : '',
            'overwritten' => !empty($response['overwritten']) ? $response['overwritten'] : '',
            'alt_text' => !empty($response['context']['custom']['alt']) ? $response['context']['custom']['alt'] : '',
            'caption' => !empty($response['context']['custom']['caption']) ? $response['context']['custom']['caption'] : '',
        );

        $value = new Value();
        $value->resourceId = $response['public_id'];
        $value->url = $response['url'];
        $value->secure_url = $response['secure_url'];
        $value->size = $response['bytes'];
        $value->metaData = $metaData;
        $value->variations = !empty($response['variations']) ? $response['variations'] : array();

        if ($response['resource_type'] == 'video') {
            $value->mediaType = Value::TYPE_VIDEO;
        } else if ($response['resource_type'] == 'image' && !in_array($response['format'], array('pdf', 'doc', 'docx'))) {
            $value->mediaType = Value::TYPE_IMAGE;
        } else {
            $value->mediaType = Value::TYPE_OTHER;
        }

        return $value;
    }

    /**
     * Enables simple variation without defining proper configuration.
     * Only supported format is WIDTHxHEIGHT
     *
     * @param Value $value
     * @param $variationConfig
     * @param $secure
     *
     * @return Variation
     */
    protected function processManualVariation(Value $value, $variationConfig, $secure)
    {
        $variation = new Variation();
        $url = $secure ? $value->secure_url : $value->url;
        $variation->url = $url;

        $variationConfig['secure'] = $secure;

        $url = $this->getFormattedUrl(
            $value->resourceId, $variationConfig
        );

        $variation->url = $url;

        return $variation;
    }

    /**
     * Builds transformation options for the provider to consume.
     *
     * @param Value $value
     * @param $variationName
     *
     * @return array options of the total sum of transformations for the provider to use
     */
    protected function processConfiguredVariation(Value $value, $variationName)
    {
        $configuredVariations = $this->variationResolver->getVariationsForContentType($contentTypeIdentifier);

        $options = array();
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
            return $this->processManualVariation($value, $variationName, $secure);
        }

        $options = $this->processConfiguredVariation($value, $variationName);

        $finalOptions['transformation'] = $options;
        $finalOptions['secure'] = $secure;
        $url = $this->getFormattedUrl(
            $value->resourceId, $finalOptions
        );

        $variation->url = $url;

        return $variation;
    }

    /**
     * Lists all available resources from the remote storage.
     *
     * @param int $limit
     *
     * @return array
     */
    public function listResources($limit = 10)
    {
        $resources = $this->cloudinaryApi->resources(
            array(
                'tags' => true,
                'context' => true,
                'max_results' => $limit,
            )
        )->getArrayCopy();

        if (!empty($resources['resources'])) {
            return $resources['resources'];
        }

        return array();
    }

    /**
     * Counts available resources from the remote storage.
     *
     * @return int
     */
    public function countResources()
    {
        $usage = $this->cloudinaryApi->usage();

        return $usage['resources'];
    }

    /**
     * Searches for the remote resource containing term in the query.
     *
     * @param string $query
     * @param int $limit
     *
     * @return array
     */
    public function searchResources($query, $limit = 10)
    {
        $result = $this->cloudinaryApi->resources(
            array(
                'prefix' => $query,
                'type' => 'upload',
                'tags' => true,
                'max_results' => $limit
            )
        )->getArrayCopy();

        if (!empty($result['resources'])) {
            return $result['resources'];
        }

        return array();
    }

    /**
     * Searches for the remote resource tagged with a provided tag.
     *
     * @param string $tag
     *
     * @return array
     */
    public function searchResourcesByTag($tag)
    {
        $result = $this->cloudinaryApi->resources_by_tag(
            urlencode($tag),
            array(
                'tags' => true,
                'context' => true,
            )
        );

        if (!empty($result['resources'])) {
            return $result['resources'];
        }

        return array();
    }

    /**
     * Returns the remote resource with provided id and type.
     *
     * @param mixed $resourceId
     * @param string $resourceType
     *
     * @return Value
     */
    public function getRemoteResource($resourceId, $resourceType)
    {
        $response = $this->cloudinaryApi->resources_by_ids(
            array($resourceId),
            array(
                'resource_type' => $resourceType,
                'max_results' => 1,
                'tags' => true,
                'context' => true,
            )
        )->getIterator()->current();

        return $this->getValueFromResponse($response[0]);
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
        return $this->cloudinaryUploader->add_tag($tag, array($resourceId));
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
        return $this->cloudinaryUploader->remove_tag($tag, array($resourceId));
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
        $this->cloudinaryApi->update(
            $resourceId,
            array(
                "context" => $context,
                "resource_type" => $resourceType
            )
        );
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

            return cl_video_tag($value->resourceId, $options);
        }

        $options['start_offset'] = !empty($options['start_offset']) ? $options['start_offset'] : 'auto';

        $options['crop'] = 'fit';
        $options['width'] = 320;
        $options['height'] = 240;
        $options['resource_type'] = 'video';

        return cl_video_thumbnail_path($value->resourceId, $options);
    }

    /**
     * Builds video tag based on manually entered configuration.
     * Look up cloudinary php API for reference what options are
     * accepted.
     *
     * @param Value $value
     * @param $variationConfig
     * @param $defaultOptions
     *
     * @return string
     */
    protected function processManualVideoVariation(Value $value, $variationConfig, $defaultOptions)
    {
        $options = $defaultOptions + $variationConfig;

        return cl_video_tag($value->resourceId, $options);
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
            'controls' => true,
            'fallback_content' => 'Your browser does not support HTML5 video tags'
        );

        if (empty($variationName)) {
            return cl_video_tag($value->resourceId, $finalOptions);
        }

        if (is_array($variationName)) {
            return $this->processManualVideoVariation($value, $variationName, $finalOptions);
        }

        $options = $this->processConfiguredVariation($value, $variationName);

        $finalOptions['transformation'] = $options;
        $finalOptions['secure'] = true;

        return cl_video_tag($value->resourceId, $finalOptions);
    }

    /**
     * Formats browse list to comply with javascript.
     *
     * @todo: check if can be removed/refractored
     * @todo: make use of the 'next_cursor' parametar in the API
     *
     * @param array $list
     *
     * @return array
     */
    public function formatBrowseList(array $list)
    {
        $listFormatted = array();
        foreach ($list as $hit) {
            $thumbOptions = array();
            $thumbOptions['crop'] = 'fit';
            $thumbOptions['width'] = 160;
            $thumbOptions['height'] = 120;

            $listFormatted[] = array(
                'resourceId' => $hit['public_id'],
                'tags' => $hit['tags'],
                'type' => $hit['resource_type'],
                'filesize' => $hit['bytes'],
                'width' => $hit['width'],
                'height' => $hit['height'],
                'filename' => $hit['public_id'],
                'url' => $this->getFormattedUrl($hit['public_id'], $thumbOptions),
            );
        }

        return $listFormatted;
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

        return $this->cloudinary->cloudinary_url($value->resourceId, $options);
    }

    /**
     * Removes the resource from the remote.
     *
     * @param $resourceId
     */
    public function deleteResource($resourceId)
    {
        $this->cloudinaryApi->delete_resources(array($resourceId));
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
