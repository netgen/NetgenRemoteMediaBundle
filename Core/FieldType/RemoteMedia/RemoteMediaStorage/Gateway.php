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
     * Deletes field data for content id identified by $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    abstract public function deleteFieldData($fieldId, $resourceId, $contentId, $providerIdentifier, $version);

    abstract public function remoteResourceConnected($resourceId);
}
