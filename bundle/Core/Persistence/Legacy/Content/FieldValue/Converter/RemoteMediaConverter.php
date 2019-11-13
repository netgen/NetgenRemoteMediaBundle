<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

/**
 * Converter for Remote Media field values in legacy storage.
 */
class RemoteMediaConverter implements Converter
{
    public static function create()
    {
        return new self();
    }

    /**
     * Converts data from $value to $storageFieldValue.
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        $storageFieldValue->dataText = \json_encode($value->data);
    }

    /**
     * Converts data from $value to $fieldValue.
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = \json_decode($value->dataText, true);
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
    }

    /**
     * Returns the name of the index column in the attribute table.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return 'data_text';
    }
}
