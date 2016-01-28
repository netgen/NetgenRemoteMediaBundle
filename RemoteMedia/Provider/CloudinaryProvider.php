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
            'overwritten' => $response['overwritten'] ?: '',
            'alt_text' => !empty($response['context']['custom']['alt']) ? $response['context']['custom']['alt'] : '',
            'caption' => !empty($response['context']['custom']['caption']) ? $response['context']['custom']['caption'] : '',
        );

        $value = new Value();
        $value->resourceId = $response['public_id'];
        $value->url = $response['url'];
        $value->secure_url = $response['secure_url'];
        $value->size = $response['bytes'];
        $value->metaData = $metaData;
        $value->variations = $response['variations'];

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
            $options['crop'] = 'crop';
        } else {
            $sizes = explode('x', $format);
        }

        if (count($sizes) !== 2) {
            throw new \InvalidArgumentException(
                "Format has to be either name of one of configured formats or '[W]x[H]' (eg. '200x200'), {$format} given"
            );
        }

        $options['width'] = $sizes[0];
        $options['height'] = $sizes[1];

        if (array_key_exists($format, $value->variations)) {
            $coords = $value->variations[$format];
            $options['x'] = $coords['x'];
            $options['y'] = $coords['y'];
        }

        $url = $this->getFormattedUrl(
            $value->resourceId, $options
        );

        $variation->width = $options['width'];
        $variation->height = $options['height'];
        $variation->url = $url;

        return $variation;
    }

    /**
     * Lists all available resources from the remote storage.
     *
     * @return array
     */
    public function listResources()
    {
        $resources = $this->cloudinaryApi->resources(array('tags' => true))->getArrayCopy();

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
     *
     * @return array
     */
    public function searchResources($query, $resourceType)
    {
        $result = $this->cloudinaryApi->resources(
            array(
                'prefix' => $query,
                'resource_type' => $resourceType,
                'type' => 'upload',
                'tags' => true,
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
}
