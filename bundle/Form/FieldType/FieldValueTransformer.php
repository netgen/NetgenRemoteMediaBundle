<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Form\FieldType;

use eZ\Publish\API\Repository\FieldType;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\SPI\FieldType\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\AdminInputValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\UpdateFieldHelper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
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

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $remoteMediaProvider;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\UpdateFieldHelper
     */
    private $updateHelper;

    public function __construct(FieldType $fieldType, Field $field, RemoteMediaProvider $remoteMediaProvider, UpdateFieldHelper $updateFieldHelper)
    {
        $this->fieldType = $fieldType;
        $this->field = $field;
        $this->remoteMediaProvider = $remoteMediaProvider;
        $this->updateHelper = $updateFieldHelper;
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
            'alt_text' => $value->metaData['alt_text'] ?? '',
            'tags' => is_array($value->metaData['tags']) ? $value->metaData['tags'] : [],
            'image_variations' => \json_encode($value->variations),
            'type' => $value->mediaType
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

        $oldValue = $this->field->value;
        if ($oldValue === null) {
            $oldValue = $this->fieldType->getEmptyValue();
        }

        $adminInputValue = AdminInputValue::fromHash($value);

        return $this->updateHelper->updateValue($oldValue, $adminInputValue);
    }
}
