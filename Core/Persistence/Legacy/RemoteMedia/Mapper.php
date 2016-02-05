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
     * Extracts a Field from $row
     *
     * @param array $row
     *
     * @return Field
     */
    public function extractFieldFromRow( array $row )
    {
        $field = new Field();

        $field->id = (int)$row['id'];
        $field->fieldDefinitionId = (int)$row['contentclassattribute_id'];
        $field->type = $row['data_type_string'];
        $field->value = $this->extractFieldValueFromRow($row);
        $field->languageCode = $row['language_code'];
        $field->versionNo = (int)$row['version'];

        return $field;
    }

    /**
     * Builds value from db rows
     *
     * @param array $row
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia
     */
    protected function extractFieldValueFromRow(array $row)
    {
        $storageValue = new StorageFieldValue();

        // Nullable field
        $storageValue->dataFloat = isset($row['data_float'])
            ? (float) $row['data_float']
            : null;
        // Nullable field
        $storageValue->dataInt = isset($row['data_int'])
            ? (int) $row['data_int']
            : null;
        $storageValue->dataText = $row['data_text'];
        // Not nullable field
        $storageValue->sortKeyInt = (int) $row['sort_key_int'];
        $storageValue->sortKeyString = $row['sort_key_string'];

        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageValue, $fieldValue);

        $value = new Value($fieldValue->data);

        return $value;
    }

    /**
     * Extracts a field definition from $row
     *
     * @param array $row
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    public function extractFieldDefinitionFromRow(array $row)
    {
        $storageFieldDef = new StorageFieldDefinition();

        $storageFieldDef->dataFloat1 = isset($row['data_float1'])
            ? (float)$row['data_float1']
            : null;
        $storageFieldDef->dataFloat2 = isset($row['data_float2'])
            ? (float)$row['data_float2']
            : null;
        $storageFieldDef->dataFloat3 = isset($row['data_float3'])
            ? (float)$row['data_float3']
            : null;
        $storageFieldDef->dataFloat4 = isset($row['data_float4'])
            ? (float)$row['data_float4']
            : null;
        $storageFieldDef->dataInt1 = isset($row['data_int1'])
            ? (int)$row['data_int1']
            : null;
        $storageFieldDef->dataInt2 = isset($row['data_int2'])
            ? (int)$row['data_int2']
            : null;
        $storageFieldDef->dataInt3 = isset($row['data_int3'])
            ? (int)$row['data_int3']
            : null;
        $storageFieldDef->dataInt4 = isset($row['data_int4'])
            ? (int)$row['data_int4']
            : null;
        $storageFieldDef->dataText1 = $row['data_text1'];
        $storageFieldDef->dataText2 = $row['data_text2'];
        $storageFieldDef->dataText3 = $row['data_text3'];
        $storageFieldDef->dataText4 = $row['data_text4'];
        $storageFieldDef->dataText5 = $row['data_text5'];
        $storageFieldDef->serializedDataText = $row['serialized_data_text'];

        $field = new FieldDefinition();

        $this->converter->toFieldDefinition($storageFieldDef, $field);

        return $field;
    }
}
