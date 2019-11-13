<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage;

use eZ\Publish\Core\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

abstract class Gateway extends StorageGateway
{
    /**
     * Stores the data in the database based on the given field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param int $productId
     * @param mixed $fieldId
     * @param mixed $resourceId
     * @param mixed $contentId
     * @param mixed $providerIdentifier
     * @param mixed $version
     */
    abstract public function storeFieldData($fieldId, $resourceId, $contentId, $providerIdentifier, $version);

    /**
     * Gets the product ID stored in the field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param mixed $contentId
     * @param mixed $fieldId
     * @param mixed $versionNo
     * @param mixed $providerIdentifier
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
