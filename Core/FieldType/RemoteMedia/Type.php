<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

class Type extends FieldType
{
    /**
     * Returns the field type identifier for this field type
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
     * @return integer
     */
    public function getName(SPIValue $value)
    {
        if (!empty($value->public_id)) {
            return $value->public_id;
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
        if ($inputValue instanceof Value) {
            return $inputValue;
        }
        else if (is_string($inputValue)) {
            $newValue = new Value();
            $newValue->input_uri = $inputValue;
            return $newValue;
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
        if ($value instanceof Value && (empty($value->input_uri) || !is_string($value->input_uri))) {
            throw new InvalidArgumentType(
                '$value',
                'string',
                $value->input_uri
            );
        }
    }

    /**
     * Converts an $hash to the Value defined by the field type
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

        $value = new Value($hash);

        return $value;
    }

    /**
     * Converts the given $value into a plain hash format
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
     * Converts a $value to a persistence value
     *
     * @param \eZ\Publish\SPI\FieldType\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        if ($value instanceof Value) {
            return new FieldValue(
                array(
                    "data" => $value->input_uri,
                    "externalData" => $value->input_uri,
                    "sortKey" => $this->getSortInfo($value),
                )
            );
        }
    }

    /**
     * Converts a persistence $fieldValue to a Value
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
    static protected function checkValueType($value)
    {
        if (!$value instanceof Value) {
            throw new InvalidArgumentType(
                "\$value",
                "Netgen\\Bundle\\RemoteMediaBundle\\Core\\FieldType\\RemoteMedia\\Value",
                $value
            );
        }
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return false;
    }
}
