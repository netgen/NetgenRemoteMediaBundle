<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\Core\FieldType\StorageGateway;

abstract class Gateway extends StorageGateway
{
    /**
     * Stores the data in the database based on the given field data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param int $productId
     */
    abstract public function storeFieldData($fieldId, $resourceId, $contentId, $providerIdentifier, $version);

    /**
     * Gets the product ID stored in the field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     *
     * @return int product ID
     */
    //abstract public function getFieldData(VersionInfo $versionInfo);


    /**
     * Deletes the entry in the link table for the provided field id and version.
     *
     * @param $contentId
     * @param $fieldId
     * @param $versionNo
     * @param $providerIdentifier
     *
     * @return mixed
     */
    abstract public function deleteFieldData($contentId, $fieldId, $versionNo, $providerIdentifier);

    /**
     * Checks if the remote resource is connected to any content.
     *
     * @param $resourceId
     * @param $providerIdentifier
     *
     * @return bool
     */
    abstract public function remoteResourceConnected($resourceId, $providerIdentifier);
}
