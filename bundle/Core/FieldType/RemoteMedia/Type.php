<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use function is_array;
use function is_string;

class Type extends FieldType
{
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

        if (isset($hash['input_uri'])) {
            return new InputValue($hash);
        }

        // @todo: finish this!!!!
        $valueHash = [
            'resourceId' => $hash['resourceId'],
            'secureUrl' => $hash['secure_url'],
        ];

        return new Value($hash);
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
        return (array) $value;
    }

    /**
     * Converts a $value to a persistence value.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        if ($value instanceof InputValue) {
            return new FieldValue(
                [
                    'data' => null,
                    'externalData' => [
                        'input_uri' => $value->input_uri,
                        'alt_text' => $value->alt_text,
                        'caption' => $value->caption,
                        'variations' => $value->variations,
                    ],
                    'sortKey' => $this->getSortInfo($value),
                ],
            );
        }
        if ($value instanceof Value) {
            return new FieldValue(
                [
                    'data' => $value,
                    'externalData' => $value,
                    'sortKey' => $this->getSortInfo($value),
                ],
            );
        }
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        if ($fieldValue->data === null) {
            return $this->getEmptyValue();
        }
        if ($fieldValue->data === $this->getEmptyValue()) {
            return $this->getEmptyValue();
        }

        if ($fieldValue->data instanceof Value) {
            return $fieldValue->data;
        }

        return new Value($fieldValue->data);
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value)
    {
        if ($value instanceof InputValue && !empty($value->input_uri)) {
            return false;
        }

        return $value === null || $value === $this->getEmptyValue() || empty($value->resourceId);
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
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
     * @return Value $value the potentially converted input value
     */
    protected function createValueFromInput($inputValue)
    {
        if ($inputValue instanceof InputValue) {
            return $inputValue;
        }
        if (is_string($inputValue)) {
            $newValue = new InputValue();
            $newValue->input_uri = $inputValue;

            return $newValue;
        }
        if (is_array($inputValue)) {
            return new InputValue($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if ($value instanceof Value && !is_string($value->resourceId)) {
            throw new InvalidArgumentType('$value', 'string', $value->resourceId);
        }
        if ($value instanceof InputValue && !is_string($value->input_uri)) {
            throw new InvalidArgumentType('$value', 'string', $value->input_uri);
        }
    }

    /**
     * Throws an exception if the given $value is not an instance of the supported value subtype.
     *
     * @param mixed $value a value returned by {@see createValueFromInput()}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not an instance of the supported value subtype
     */
    protected static function checkValueType($value)
    {
        if (!$value instanceof Value && !$value instanceof InputValue) {
            throw new InvalidArgumentType('$value', 'Netgen\\Bundle\\RemoteMediaBundle\\Core\\FieldType\\RemoteMedia\\Value or "Netgen\\Bundle\\RemoteMediaBundle\\Core\\FieldType\\RemoteMedia\\InputValue",', $value);
        }
    }
}
