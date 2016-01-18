<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\SPI\FieldType\FieldStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\API\Repository\ContentService;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaInterface;

class RemoteMediaStorage implements FieldStorage
{
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaInterface
     */
    protected $uploader;

    /**
     * Constructor

     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaInterface
     */
    public function __construct(
        ContentService $contentService,
        RemoteMediaInterface $uploader
    )
    {
        $this->contentService = $contentService;
        $this->uploader = $uploader;
    }

    /**
     * Stores value for $field in an external data source.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @return true Indicating internal value data has changed
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        // @todo: Implement specifics for each storage eg. Cloudinary
        throw new NotImplementedException('Not implemented');
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
    /**
     * Deletes field data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds Array of field IDs
     * @param array $context
     *
     * @return boolean
     */

    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {
        $fields = $this->contentService->loadContentByVersionInfo( $versionInfo )->getFields();
        foreach ( $fields as $field )
        {
            if ( in_array( $field->id, $fieldIds ) )
            {
                // @todo (if possible): check if used elsewhere, if not, remove from remote
            }
        }
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return boolean
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * Get index data for external data for search backend
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
        return false;
    }

    /**
     * This method is used exclusively by Legacy Storage to copy external data of existing field in main language to
     * the untranslatable field not passed in create or update struct, but created implicitly in storage layer.
     *
     * By default the method falls back to the {@link \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()}.
     * External storages implement this method as needed.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Field $originalField
     * @param array $context
     *
     * @return null|boolean Same as {@link \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()}.
     */
    public function copyLegacyField(VersionInfo $versionInfo, Field $field, Field $originalField, array $context)
    {
        return $this->storeFieldData( $versionInfo, $field, $context );
    }
}
