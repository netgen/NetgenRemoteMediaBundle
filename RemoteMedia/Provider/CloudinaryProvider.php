<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider;

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
}
