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

    /**
     * CloudinaryProvider constructor.
     *
     * @param array $cloudinaryOptions
     */
    public function __construct($cloudinaryOptions)
    {
        if (empty($cloudinaryOptions['cloud_name']) || empty($cloudinaryOptions['api_key']) || empty($cloudinaryOptions['api_secret'])) {
            throw new \InvalidArgumentException('Cloudinary cloud_name, api_key and api_secret must all be set!');
        }

        $this->cloudinary = new Cloudinary();
        $this->cloudinary->config(
            array(
                'cloud_name' => $cloudinaryOptions['cloud_name'],
                'api_key' => $cloudinaryOptions['api_key'],
                'api_secret' => $cloudinaryOptions['api_secret'],
            )
        );

        $this->cloudinaryUploader = new Uploader();
        $this->cloudinaryApi = new Api();
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
            'version' => $response['version'] ?: '',
            'width' => $response['width'] ?: '',
            'height' => $response['height'] ?: '',
            'format' => $response['format'] ?: '',
            'resource_type' => $response['resource_type'] ?: '',
            'created' => $response['created_at'] ?: '',
            'tags' => $response['tags'] ?: array(),
            'signature' => $response['signature'] ?: '',
            'type' => $response['type'] ?: '',
            'etag' => $response['etag'] ?: '',
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
     * @param array $namedFormats
     * @param string $format
     * @param bool $secure
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    public function getVariation(Value $value, array $namedFormats, $format, $secure = true)
    {
        $variation = new Variation();
        $url = $secure ? $value->secure_url : $value->url;

        if (empty($format)) {
            $variation->url = $url;

            return $variation;
        }

        $options = array('secure' => $secure);

        if (array_key_exists($format, $namedFormats)) {
            $selectedFormat = $namedFormats[$format];
            $sizes = explode('x', $selectedFormat);
        } else {
            $sizes = explode('x', $format);
        }

        if (count($sizes) !== 2) {
            throw new \InvalidArgumentException(
                "Format has to be either name of one of configured formats or '[W]x[H]' (eg. '200x200'), {$format} given"
            );
        }

        if (array_key_exists($format, $value->variations)) {
            $coords = $value->variations[$format];
            if (count($coords) > 2 && (int)$coords['w'] !== 0 ) {
                $options['transformation'] = array(
                    array(
                        'x' => (int)$coords['x'],
                        'y' => (int)$coords['y'],
                        'width' => (int)$coords['w'],
                        'height' => (int)$coords['h'],
                        'crop' => 'crop',
                    ),
                    array(
                        'width' => $sizes[0],
                        'height' => $sizes[1],
                        'crop' => 'fill'
                    )
                );
            } else {
                $options['x'] = $coords['x'];
                $options['y'] = $coords['y'];
            }

        } else {
            $options['crop'] = 'fit';
            $options['width'] = $sizes[0];
            $options['height'] = $sizes[1];
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
        $resources = $this->cloudinaryApi->resources(array('tags' => true, 'max_results' => $limit))->getArrayCopy();

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
        return count($this->listResources());
    }

    /**
     * Searches for the remote resource containing term in the query.
     *
     * @param string $query
     * @param string $resourceType
     * @param int $limit
     *
     * @return array
     */
    public function searchResources($query, $resourceType, $limit = 10)
    {
        $result = $this->cloudinaryApi->resources(
            array(
                'prefix' => $query,
                'resource_type' => $resourceType,
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
     * Adds tag to remote resource
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
     * Removes tag from remote resource
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
     * Updates the resource context
     * (eg. alt text and caption)
     *
     * @param string $resourceId
     * @param string $resourceType
     * @param array $context
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
     * Returns thumbnail url for the video with provided id
     *
     * @param $resourceId
     * @param string $offset
     *
     * @return string
     */
    public function getVideoThumbnail($resourceId, $offset = 'auto')
    {
        $options = array();
        $options['crop'] = 'fit';
        $options['width'] = 160;
        $options['height'] = 120;
        $options['resource_type'] = 'video';
        $options['start_offset'] = $offset;

        return cl_video_thumbnail_path($resourceId, $options);
    }

    /**
     * Generates html5 video tag for the video with provided id
     *
     * @param $resourceId
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
}
