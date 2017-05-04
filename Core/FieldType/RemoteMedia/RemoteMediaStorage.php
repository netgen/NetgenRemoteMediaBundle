<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;

class RemoteMediaStorage extends GatewayBasedStorage
{
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    protected $provider;

    /**
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    protected $fieldTypeService;

    protected $deleteUnused;

    /**
     * Constructor.

     *
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider $provider
     * @param \eZ\Publish\API\Repository\FieldTypeService $fieldTypeService
     */
    public function __construct(
        ContentService $contentService,
        RemoteMediaProvider $provider,
        FieldTypeService $fieldTypeService
    ) {
        $this->contentService = $contentService;
        $this->provider = $provider;
        $this->fieldTypeService = $fieldTypeService;
    }

    public function setDeleteUnused($deleteUnused = false)
    {
        $this->deleteUnused = $deleteUnused;
    }

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

        /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage\Gateway $gateway */
        $gateway = $this->getGateway($context);

        if ($data instanceof Value && $data !== $this->fieldTypeService->getFieldType('ngremotemedia')->getEmptyValue()) {
            $gateway->storeFieldData(
                $field->id,
                $data->resourceId,
                $versionInfo->contentInfo->id,
                $this->provider->getIdentifier(),
                $versionInfo->versionNo
            );
        } elseif (is_array($data) && !empty($data)) {
            $fileUri = $data['input_uri'];
            $id = pathinfo($fileUri, PATHINFO_FILENAME);

            $options['alt_text'] = $data['alt_text'];
            $options['caption'] = $data['caption'];
            $value = $this->provider->upload(
                $fileUri,
                $id,
                $options
            );

            $value->variations = $data['variations'];

            $field->value->data = $value;
            $gateway->storeFieldData(
                $field->id,
                $value->resourceId,
                $versionInfo->contentInfo->id,
                $this->provider->getIdentifier(),
                $versionInfo->versionNo
            );

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

    /**
     * Deletes field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds Array of field IDs
     * @param array $context
     *
     * @return bool
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        $versionNo = $versionInfo->versionNo;
        $content = $this->contentService->loadContent($versionInfo->contentInfo->id, null, $versionNo);
        $fields = $content->getFields();

        $gateway = $this->getGateway($context);

        if ($this->deleteUnused) {
            $resourceIdsToDelete = array();
            foreach ($fields as $field) {
                if (in_array($field->id, $fieldIds, true)) {
                    // load resource_id from table
                    $resourceIdsToDelete = array_merge(
                        $resourceIdsToDelete,
                        $gateway->loadFromTable($content->id, $field->id, $versionNo, $this->provider->getIdentifier())
                    );

                    // delete for current version
                    $gateway->deleteFieldData($content->id, $field->id, $versionNo, $this->provider->getIdentifier());
                }
            }

            foreach ($resourceIdsToDelete as $resourceId) {
                // check if resource_id is used anywhere else
                if (!$gateway->remoteResourceConnected($resourceId)) {
                    // delete from remote provider
                    $this->provider->deleteResource($resourceId);
                }
            }
        } else {
            // remove from link table entry just for that version
            foreach ($fields as $field) {
                if (!in_array($field->id, $fieldIds, true)) {
                    continue;
                }

                $gateway->deleteFieldData($content->id, $field->id, $versionNo, $this->provider->getIdentifier());
            }
        }
    }

    /**
     * Checks if field type has external data to deal with.
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * Get index data for external data for search backend.
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
     * @return null|bool same as {@link \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()}
     */
    public function copyLegacyField(VersionInfo $versionInfo, Field $field, Field $originalField, array $context)
    {
        return $this->storeFieldData($versionInfo, $field, $context);
    }
}
