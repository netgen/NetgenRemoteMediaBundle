<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface;
use \Cloudinary;
use \Cloudinary\Uploader;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

class CloudinaryProvider implements RemoteMediaProviderInterface
{
    protected $cloudinary;

    protected $cloudinaryUploader;

    public function __construct($cloudinaryOptions)
    {
        if (empty($cloudinaryOptions['cloud_name']) || empty($cloudinaryOptions['api_key']) || empty($cloudinaryOptions['api_secret'])) {
            throw new \InvalidArgumentException('Cloudinary cloud_name, api_key and api_secret must all be set!');
        }

        $this->cloudinary = new Cloudinary();
        $this->cloudinary->config(
            array(
                "cloud_name" => $cloudinaryOptions['cloud_name'],
                "api_key" => $cloudinaryOptions['api_key'],
                "api_secret" => $cloudinaryOptions['api_secret']
            )
        );

        $this->cloudinaryUploader = new Uploader();
    }

    public function upload($fileUri, $options = array())
    {
        return $this->cloudinaryUploader->upload($fileUri, $options);
    }

    public function getFormattedUrl($source, $options = array())
    {
        return cloudinary_url_internal($source, $options);
    }

    public function getValueFromResponse($response)
    {
        $metaData = array(
            'version'   => $response['version'] ?: '',
            'width'     => $response['width'] ?: '',
            'height'    => $response['height'] ?: '',
            'format'    => $response['format'] ?: '',
            'resource_type' => $response['resource_type'] ?: '',
            'created'   => $response['created_at'] ?: '',
            'tags'      => $response['tags'] ?: array(),
            'signature' => $response['signature'] ?: '',
            'type'      => $response['type'] ?: '',
            'etag'      => $response['etag'] ?: '',
            'overwritten' => $response['overwritten'] ?: '',
            'alt_text'  => !empty($response['context']['custom']['alt']) ? $response['context']['custom']['alt'] : '',
            'caption'  => !empty($response['context']['custom']['caption']) ? $response['context']['custom']['caption'] : '',
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
}
