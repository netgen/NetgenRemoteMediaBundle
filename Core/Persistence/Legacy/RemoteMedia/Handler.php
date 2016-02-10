<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia;

use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia\Mapper;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

class Handler
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia\Gateway
     */
    protected $gateway;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia\Mapper
     */
    protected $mapper;

    /**
     * @param \Netgen\Bundle\RemoteMediaBundke\Core\Persistence\Legacy\RemoteMedia\Gateway $gateway
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia\Mapper $mapper
     */
    public function __construct(Gateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * Loads the value from the database
     *
     * @param mixed $fieldId
     * @param mixed $versionId
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia
     */
    public function loadValue($fieldId, $versionId)
    {
        $data = $this->gateway->loadField($fieldId, $versionId);

        return $this->mapper->extractFieldValueFromRow($data);
    }

    /**
     * Updates field data in the db
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param mixed $fieldId
     * @param mixed $versionId
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function update($value, $fieldId, $versionId)
    {
        $storageFieldValue = new StorageFieldValue();

        $storageFieldValue->dataText = json_encode($value);
        $this->gateway->updateField($storageFieldValue, $fieldId, $versionId);

        return $this->loadValue($fieldId, $versionId);
    }

    /**
     * Loads field settings for the field
     *
     * @param mixed $fieldId
     * @param mixed $versionId
     *
     * @return \eZ\Publish\Core\FieldType\FieldSettings
     */
    public function loadFieldSettingsByFieldId($fieldId, $versionId)
    {
        $data = $this->gateway->loadFieldDefinitionByFieldId($fieldId, $versionId);

        return $this->mapper->extractFieldSettingsFromRow($data);
    }
}
