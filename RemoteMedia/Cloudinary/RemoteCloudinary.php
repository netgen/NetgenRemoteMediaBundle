<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Cloudinary;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaInterface;
use \Cloudinary;
use \Cloudinary\Uploader;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

class RemoteCloudinary implements RemoteMediaInterface
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

    public function upload(VersionInfo $versionInfo, Field $field)
    {
        $options = $this->getUploadOptions($versionInfo, $field);

        return $this->cloudinaryUploader->upload($field->value->externalData, $options);
    }

    protected function getUploadOptions(VersionInfo $versionInfo, Field $field)
    {
        $fileUri = $field->value->externalData;
        $folder = $versionInfo->contentInfo->id . '/' . $versionInfo->id;
        $options = array(
            'public_id' => $folder . '/' . basename($fileUri)
        );

        return $options;
    }
}
