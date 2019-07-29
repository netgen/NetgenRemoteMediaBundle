<?php

namespace Netgen\Bundle\RemoteMediaBundle\Form\FieldType;

use eZ\Publish\API\Repository\FieldType;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\SPI\FieldType\Value;
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

    public function __construct(FieldType $fieldType, Field $field, RemoteMediaProvider $remoteMediaProvider)
    {
        $this->fieldType = $fieldType;
        $this->field = $field;
        $this->remoteMediaProvider = $remoteMediaProvider;
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
            'alt_text' => isset($value->metaData['alt_text']) ? $value->metaData['alt_text'] : '',
            'resource_url' => $value->secure_url,
            'url' => $value->url,
            'size' => $value->size,
            'tags' => implode(', ', $value->metaData['tags']),
            'width' => isset($value->metaData['width']) ? $value->metaData['width'] : '',
            'height' => isset($value->metaData['height']) ? $value->metaData['height'] : ''
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

//        $hash = [
//            'resourceId' => $value['resource_id'],
//            'secure_url' => $value['resource_url'],
//            'mediaType' => $value['media_type'],
//            'size' => $value['size'],
//            'metaData' => [
//                'alt_text' => $value['alt_text'],
//                'tags' => explode(',', $value['tags']),
//                'width' => $value['width'],
//                'height' => $value['height']
//            ]
//        ];
//
//        if ($value['resource_id'] === $this->field->value->resourceId) {
//            // update variations
//        }


//        $value = new \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value($hash);

        return $this->field->value;
    }
}
