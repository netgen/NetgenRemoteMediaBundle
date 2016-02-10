<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\RemoteMedia;

use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as BaseMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\RemoteMediaConverter;
use eZ\Publish\Core\FieldType\FieldSettings;

class Mapper
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\RemoteMediaConverter
     */
    protected $converter;

    /**
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\RemoteMediaConverter $converter
     */
    public function __construct(RemoteMediaConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Builds value from db row
     *
     * @param array $row
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia
     */
    public function extractFieldValueFromRow(array $row)
    {
        $storageValue = new StorageFieldValue();

        $storageValue->dataFloat = isset($row['data_float'])
            ? (float) $row['data_float']
            : null;
        $storageValue->dataInt = isset($row['data_int'])
            ? (int) $row['data_int']
            : null;
        $storageValue->dataText = $row['data_text'];
        $storageValue->sortKeyInt = (int) $row['sort_key_int'];
        $storageValue->sortKeyString = $row['sort_key_string'];

        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageValue, $fieldValue);

        $value = new Value($fieldValue->data);

        return $value;
    }

    /**
     * Builds RemoteMedia field settings from db row
     *
     * @param array $row
     *
     * @return \eZ\Publish\Core\FieldType\FieldSettings
     */
    public function extractFieldSettingsFromRow(array $row)
    {
        return new FieldSettings(
            array(
                'formats' => json_decode($row['data_text4'], true),
            )
        );
    }
}
