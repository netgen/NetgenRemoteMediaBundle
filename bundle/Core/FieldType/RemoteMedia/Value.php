<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    /**
     * @var string
     */
    const TYPE_IMAGE = 'image';
    /**
     * @var string
     */
    const TYPE_VIDEO = 'video';
    /**
     * @var string
     */
    const TYPE_OTHER = 'other';

    public $resourceId = null;
    public $url = null;
    public $secure_url = null;
    public $size = 0;

    public $mediaType = 'image';

    public $variations = [];
    public $metaData = [
        'format' => '',
        'alt_text' => '',
        'caption' => '',
        'width' => '',
        'height' => '',
        'tags' => [],
    ];

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        return \json_encode($this);
    }

    /**
     * Creates a value from cloudinary response array.
     *
     * @return Value
     */
    public static function createFromCloudinaryResponse(array $response)
    {
        $metaData = [
            'version' => !empty($response['version']) ? $response['version'] : '',
            'width' => !empty($response['width']) ? $response['width'] : '',
            'height' => !empty($response['height']) ? $response['height'] : '',
            'format' => !empty($response['format']) ? $response['format'] : '',
            'resource_type' => !empty($response['resource_type']) ? $response['resource_type'] : '',
            'created' => !empty($response['created_at']) ? $response['created_at'] : '',
            'tags' => !empty($response['tags']) ? $response['tags'] : [],
            'signature' => !empty($response['signature']) ? $response['signature'] : '',
            'type' => !empty($response['type']) ? $response['type'] : '',
            'etag' => !empty($response['etag']) ? $response['etag'] : '',
            'overwritten' => !empty($response['overwritten']) ? $response['overwritten'] : '',
            'alt_text' => !empty($response['context']['custom']['alt']) ? $response['context']['custom']['alt'] : '',
            'caption' => !empty($response['context']['custom']['caption']) ? $response['context']['custom']['caption'] : '',
        ];

        $value = new self();
        $value->resourceId = $response['public_id'];
        $value->url = $response['url'];
        $value->secure_url = $response['secure_url'];
        $value->size = $response['bytes'];
        $value->metaData = $metaData;
        $value->variations = !empty($response['variations']) ? $response['variations'] : [];

        if ($response['resource_type'] === 'video') {
            $value->mediaType = self::TYPE_VIDEO;
        } elseif ($response['resource_type'] === 'image' && (!isset($response['format']) || !\in_array($response['format'], ['pdf', 'doc', 'docx'], true))) {
            $value->mediaType = self::TYPE_IMAGE;
        } else {
            $value->mediaType = self::TYPE_OTHER;
        }

        return $value;
    }
}
