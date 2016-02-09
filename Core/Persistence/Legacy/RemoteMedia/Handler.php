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
     * Loads value by field id and version
     *
     * @param mixed $fieldId
     * @param mixed versionId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    public function load($fieldId, $versionId)
    {
        $data = $this->gateway->loadField($fieldId, $versionId);
        return $this->mapper->extractFieldFromRow($data);
    }

    /**
     * Updates field data in the db
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param mixed $fieldId
     * @param mixed $versionId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    public function update($value, $fieldId, $versionId)
    {
        $storageFieldValue = new StorageFieldValue();

        $storageFieldValue->dataText = json_encode($value);
        $this->gateway->updateField($storageFieldValue, $fieldId, $versionId);

        return $this->load($fieldId, $versionId);
    }

    /**
     * Loads field settings from the db
     *
     * @param $fieldDefinitionId
     *
     * @return mixed
     */
    public function loadFieldSettings($fieldDefinitionId)
    {
        $data = $this->gateway->loadFieldDefinition($fieldDefinitionId);
        $field = $this->mapper->extractFieldDefinitionFromRow($data);

        $fieldSettings = $field->fieldTypeConstraints->fieldSettings;

        return $fieldSettings;
    }
}
