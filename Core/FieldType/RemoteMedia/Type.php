<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;

class Type extends FieldType
{
    /**
     * List of settings available for this FieldType.
     *
     * The key is the setting name, and the value is the default value for this setting
     *
     * @var array
     */
    protected $settingsSchema = array(
        'formats' => array(
            'type' => 'string',
            'default' => '',
        ),
    );

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ngremotemedia';
    }

    /**
     * Returns a human readable string representation from the given $value
     * It will be used to generate content name and url alias if current field
     * is designated to be used in the content name/urlAlias pattern.
     *
     * @param \eZ\Publish\SPI\FieldType\Value $value
     *
     * @return int
     */
    public function getName(SPIValue $value)
    {
        if (!empty($value->resourceId)) {
            return $value->resourceId;
        }

        return '';
    }

    /**
     * Returns the empty value for this field type.
     *
     * @return Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return bool
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param mixed $inputValue
     *
     * @return Value $value The potentially converted input value.
     */
    protected function createValueFromInput($inputValue)
    {
        if ($inputValue instanceof InputValue) {
            return $inputValue;
        } elseif (is_string($inputValue)) {
            $newValue = new InputValue();
            $newValue->input_uri = $inputValue;

            return $newValue;
        } elseif (is_array($inputValue)) {
            return new InputValue($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if ($value instanceof Value && !is_string($value->resourceId)) {
            throw new InvalidArgumentType(
                '$value',
                'string',
                $value->resourceId
            );
        } elseif ($value instanceof InputValue && !is_string($value->input_uri)) {
            throw new InvalidArgumentType(
                '$value',
                'string',
                $value->input_uri
            );
        }
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function fromHash($hash)
    {
        if (!is_array($hash)) {
            return $this->getEmptyValue();
        }

        $value = new InputValue($hash);

        return $value;
    }

    /**
     * Converts the given $value into a plain hash format.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value|\eZ\Publish\SPI\FieldType\Value $value
     *
     * @return array
     */
    public function toHash(SPIValue $value)
    {
        return (array)$value;
    }

    /**
     * Converts a $value to a persistence value.
     *
     * @param \eZ\Publish\SPI\FieldType\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        if ($value instanceof InputValue) {
            return new FieldValue(
                array(
                    'data' => null,
                    'externalData' => array(
                        'input_uri' => $value->input_uri,
                        'alt_text' => $value->alt_text,
                        'caption' => $value->caption,
                        'variations' => $value->variations,
                    ),
                    'sortKey' => $this->getSortInfo($value),
                )
            );
        } else if ($value instanceof Value) {
            return new FieldValue(
                array(
                    'data' => $value,
                    'externalData' => array(),
                    'sortKey' => $this->getSortInfo($value)
                )
            );
        }
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        if ($fieldValue->data === null) {
            return $this->getEmptyValue();
        }

        $value = new Value($fieldValue->data);

        return $value;
    }

    /**
     * Throws an exception if the given $value is not an instance of the supported value subtype.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the parameter is not an instance of the supported value subtype.
     *
     * @param mixed $value A value returned by {@see createValueFromInput()}.
     */
    protected static function checkValueType($value)
    {
        if (!$value instanceof Value && !$value instanceof InputValue) {
            throw new InvalidArgumentType(
                '$value',
                'Netgen\\Bundle\\RemoteMediaBundle\\Core\\FieldType\\RemoteMedia\\Value or "Netgen\\Bundle\\RemoteMediaBundle\\Core\\FieldType\\RemoteMedia\\InputValue",',
                $value
            );
        }
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = array();
        if (!is_array($fieldSettings)) {
            $validationErrors[] = new ValidationError('Field settings must be in form of an array');

            return $validationErrors;
        }
        foreach ($fieldSettings as $name => $value) {
            if (!isset($this->settingsSchema[$name])) {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    array(
                        'setting' => $name,
                    )
                );
                continue;
            }
            switch ($name) {
                case 'formats':
                    if (!is_array($value)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be array",
                            null,
                            array(
                                'setting' => $name,
                            )
                        );
                    }

                    foreach ($value as $formatName => $format) {
                        if (empty($format)) {
                            $validationErrors[] = new ValidationError(
                                "For setting '%setting%' specification of format %format% must not be empty",
                                null,
                                array(
                                    'setting' => $name,
                                    'format' => $format,
                                )
                            );
                        }
                    }
                    break;
            }
        }

        return $validationErrors;
    }

    /**
     * Returns if the given $value is considered empty by the field type
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return boolean
     */
    public function isEmptyValue( SPIValue $value )
    {
        return $value === null || $value == $this->getEmptyValue() || empty($value->resourceId);
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return false;
    }
}
