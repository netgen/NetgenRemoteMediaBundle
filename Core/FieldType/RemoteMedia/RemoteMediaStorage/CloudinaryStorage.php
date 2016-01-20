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
            $folder = $versionInfo->contentInfo->id . '/' . $field->id;
            $options = array(
                'public_id' => $folder . '/' . pathinfo($fileUri, PATHINFO_FILENAME),
                'overwrite' => true,
                'context' => array(
                    'alt' => $data['alt_text'],
                    'caption' => $data['caption']
                )
            );

            $response = $this->uploader->upload(
                $fileUri,
                $options
            );

            $field->value->data = $response;
            return true;
        }

        return false;
    }
}
