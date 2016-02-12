<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\FieldType\FieldStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\API\Repository\ContentService;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface;

class RemoteMediaStorage extends GatewayBasedStorage
{
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface
     */
    protected $provider;

    protected $deleteUnused;

    /**
     * Constructor.

     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface
     */
    public function __construct(
        ContentService $contentService,
        RemoteMediaProviderInterface $provider
    ) {
        $this->contentService = $contentService;
        $this->provider = $provider;
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

        if ($data instanceof Value)
        {
            $gateway->storeFieldData(
                $field->id,
                $data->resourceId,
                $versionInfo->contentInfo->id,
                $this->provider->getIdentifier(),
                $versionInfo->versionNo
            );
        }
        else if (is_array($data) && !empty($data)) {
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
        if ($this->deleteUnused) {
            $fields = $this->contentService->loadContentByVersionInfo($versionInfo)->getFields();
            foreach ($fields as $field) {
                if (in_array($field->id, $fieldIds)) {
                    // 1) remove entry in the database connecting field/version and remote resource
                    /** @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\RemoteMediaStorage\Gateway $gateway */
                    $gateway = $this->getGateway($context);
                    $gateway->deleteFieldData($field->value->resourceId, $versionInfo->contentInfo->id, $this->provider->getIdentifier(),
                        $versionInfo->versionNo);

                    // 2) check whether the remote resource is no longer in the database
                    if (!$gateway->remoteResourceConnected($field->value->resourceId)) {
                        // 3) remove from remote provider
                        $this->provider->deleteResource($field->value->resourceId);
                    }
                }
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
     * @return null|bool Same as {@link \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()}.
     */
    public function copyLegacyField(VersionInfo $versionInfo, Field $field, Field $originalField, array $context)
    {
        return $this->storeFieldData($versionInfo, $field, $context);
    }
}
