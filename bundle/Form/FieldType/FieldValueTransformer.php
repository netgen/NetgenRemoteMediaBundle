<?php

namespace Netgen\Bundle\RemoteMediaBundle\Form\FieldType;

use eZ\Publish\API\Repository\FieldType;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\SPI\FieldType\Value;
use Symfony\Component\Form\DataTransformerInterface;

class FieldValueTransformer implements DataTransformerInterface
{
    /**
     * @var \eZ\Publish\API\Repository\FieldType
     */
    private $fieldType;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Field
     */
    private $field;

    public function __construct(FieldType $fieldType, Field $field)
    {
        $this->fieldType = $fieldType;
        $this->field = $field;
    }

    /**
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     *
     * @return array|null
     */
    public function transform($value)
    {
        if (!$value instanceof Value) {
            return null;
        }

        return [
            'resource_id' => $value->resourceId,
            'alt_text' => isset($value->metaData['alt_text']) ? $value->metaData['alt_text'] : ''
        ];
    }

    /**
     * @param array|null $value
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    public function reverseTransform($value)
    {
        if ($value === null) {
            return $this->fieldType->getEmptyValue();
        }

        //@todo: create proper hash for ngremotemedia, meaning 'value' array must contain all relevant data
        //      meaning form has to contain all that data
    }
}
