<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage;

use eZ\Publish\SPI\FieldType\FieldStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

class CloudinaryStorage extends RemoteMediaStorage implements FieldStorage
{
    /**
     * Stores value for $field in an external data source.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return true Indicating internal value data has changed
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        if (is_string( $field->value->externalData)) {
            $options = $this->uploader->getUploadOptions($versionInfo, $field);

            $response = $this->uploader->upload(
                $field->value->externalData,
                $options
            );

            $field->value->data = $response;
            return true;
        }

        return false;
    }
}
