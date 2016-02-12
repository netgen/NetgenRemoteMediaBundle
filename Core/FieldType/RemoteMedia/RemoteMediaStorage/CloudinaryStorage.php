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
        $data = $field->value->externalData;

        if (is_array($data) && !empty($data)) {
            $fileUri = $data['input_uri'];
            $folder = $field->id . '/' . $versionInfo->id;
            $id = pathinfo($fileUri, PATHINFO_FILENAME) . '/' . $folder;

            $options = $this->provider->prepareUploadOptions($id, null, $data['alt_text'], $data['caption']);
            $response = $this->provider->upload(
                $fileUri,
                $options
            );

            $response['variations'] = $data['variations'];

            $value = $this->provider->getValueFromResponse($response);

            $field->value->data = $value;

            return true;
        }

        return false;
    }

    /**
     * Populates $field value property based on the external data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $field->value->externalData = array();
    }
}
