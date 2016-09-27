<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider;

use \Cloudinary;
use \Cloudinary\Uploader;
use \Cloudinary\Api;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;


class CloudinaryProvider implements RemoteMediaProviderInterface
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

    protected $uniqueFilename = false;

    protected $useUpscaling = true;

    /**
     * CloudinaryProvider constructor.
     *
     * @param string $cloudName
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct($cloudName, $apiKey, $apiSecret)
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

    public function setFolderName($folderName = null)
    {
        $this->folderName = $folderName;
    }

    public function setUniqueFilename($uniqueFilename = false)
    {
        $this->uniqueFilename = $uniqueFilename;
    }

    public function setUseUpscaling($useUpscaling = true)
    {
        $this->useUpscaling = $useUpscaling;
    }

    /**
     *
     *
     * @param string $fileName
     * @param string|null $resourceType
     * @param string $altText
     * @param string $caption
     *
     * @return array
     */
    public function prepareUploadOptions($fileName, $resourceType = null, $altText = '', $caption = '')
    {
        $id = $this->folderName ? $this->folderName . '/' . $fileName : $fileName;
        if ($this->uniqueFilename) {
            $id = $id . '_' . base_convert(uniqid(), 16, 36);
        }

        return array(
            'public_id' => $id,
            'overwrite' => true,
            'context' => array(
                'alt' => $altText,
                'caption' => $caption,
            ),
            'resource_type' => $resourceType ?: 'auto'
        );
    }

    /**
     * Uploads the local resource to remote storage.
     *
     * @param string $fileUri
     * @param array $options
     *
     * @return mixed
     */
    public function upload($fileUri, $options = array())
    {
        return $this->cloudinaryUploader->upload($fileUri, $options);
    }

    /**
     * Gets the absolute url of the remote resource formatted according to options provided.
     *
     * @param string $source
     * @param array $options
     *
     * @return string
     */
    public function getFormattedUrl($source, $options = array())
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
    public function getValueFromResponse($response)
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

        return $value;
    }

    /**
     * Gets the remote media Variation.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $format
     * @param array $namedFormats
     * @param bool $secure
     * @return Variation
     */
    public function getVariation(Value $value, $format, array $namedFormats, $secure = true)
    {
        $variation = new Variation();
        $url = $secure ? $value->secure_url : $value->url;

        if (empty($format)) {
            $variation->url = $url;

            return $variation;
        }

        $sizes = array_key_exists($format, $namedFormats) ? explode('x', $namedFormats[$format]) : explode('x', $format);

        if (count($sizes) !== 2) {
            throw new \InvalidArgumentException(
                "Format has to be either name of one of configured formats or '[W]x[H]' (eg. '200x200'), {$format} given"
            );
        }

        $fillOptions = array(
            'crop' => $this->useUpscaling ? 'fill' : 'lfill',
        );

        if (!empty($sizes[0])) $fillOptions['width'] = $sizes[0];
        if (!empty($sizes[1])) $fillOptions['height'] = $sizes[1];

        $options = array( 'secure' => $secure ) + $fillOptions;

        if (array_key_exists($format, $value->variations)) {
            $coords = $value->variations[$format];
            if (count($coords) > 2 && (int)$coords['w'] !== 0) {
                $options = array(
                    'transformation' => array(
                        array(
                            'x' => (int)$coords['x'],
                            'y' => (int)$coords['y'],
                            'width' => (int)$coords['w'],
                            'height' => (int)$coords['h'],
                            'crop' => 'crop',
                        ),
                        $fillOptions
                    ),
                    'secure' => $secure
                );
            }
        }

        $url = $this->getFormattedUrl(
            $value->resourceId, $options
        );

        $variation->width = $sizes[0];
        $variation->height = $sizes[1];
        $variation->url = $url;

        return $variation;
    }

    /**
     * Lists all available resources from the remote storage.
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
            $tag,
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
     * @return array
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

        return $response[0];
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
     * @param mixed $resourceId
     * @param mixed|null $offset
     *
     * @return string
     */
    public function getVideoThumbnail($resourceId, $offset = null)
    {
        $offset = $offset ?: 'auto';

        $options = array();
        $options['crop'] = 'fit';
        $options['width'] = 160;
        $options['height'] = 120;
        $options['resource_type'] = 'video';
        $options['start_offset'] = $offset;

        return cl_video_thumbnail_path($resourceId, $options);
    }

    /**
     * Generates html5 video tag for the video with provided id.
     *
     * @param mixed $resourceId
     * @param string $format
     * @param array $namedFormats
     *
     * @return string
     */
    public function generateVideoTag($resourceId, $format = '', $namedFormats = array())
    {
        $options = array(
            'controls' => true,
            'fallback_content' => 'Your browser does not support HTML5 video tags'
        );

        if (!empty($format)) {
            if (array_key_exists($format, $namedFormats)) {
                $selectedFormat = $namedFormats[$format];
                $sizes = explode('x', $selectedFormat);
            } else {
                $sizes = explode('x', $format);
            }

            if ($sizes[0] !== 0) {
                $options['width'] = $sizes[0];
            }
            if ($sizes[1] !== 0) {
                $options['height'] = $sizes[1];
            }

            $options['background'] = 'black';
            $options['crop'] = 'pad';
        }

        return cl_video_tag($resourceId, $options);
    }

    /**
     * Formats browse list to comply with javascript.
     *
     * @param array $list
     *
     * @return array
     */
    public function formatBrowseList(array $list)
    {
        $listFormatted = array();
        foreach ($list as $hit) {
            $fileName = explode('/', $hit['public_id']);
            if (count($fileName) > 1) {
                $fileName = $fileName[1]; // first part is the (siteaccess) folder
            }

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
                'filename' => $hit['public_id'], //$fileName, -> change for Kavli project
                'url' => $this->getFormattedUrl($hit['public_id'], $thumbOptions),
            );
        }

        return $listFormatted;
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

    public function getIdentifier()
    {
        return 'cloudinary';
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
        return $this->cloudinary->cloudinary_url($value->resourceId);
    }
}
