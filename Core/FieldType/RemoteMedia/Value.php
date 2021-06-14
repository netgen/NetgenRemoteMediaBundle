<?php

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_OTHER = 'other';

    public $resourceId = null;
    public $url = null;
    public $secure_url = null;
    public $size = null;

    public $mediaType = 'image';

    public $variations = array();
    public $metaData = array(
        'format' => '',
        'alt_text' => '',
        'caption' => '',
        'width' => '',
        'height' => '',
        'tags' => array(),
    );

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * Creates a value from cloudinary response array
     *
     * @param array $response
     *
     * @return Value
     */
    public static function createFromCloudinaryResponse(array $response)
    {
        $altText = !empty($response['context']['custom']['alt_text']) ? $response['context']['custom']['alt_text'] : '';

        if ($altText === '') {
            $altText = !empty($response['context']['alt_text']) ? $response['context']['alt_text'] : '';
        }

        $metaData = array(
            'version' => !empty($response['version']) ? $response['version'] : '',
            'width' => !empty($response['width']) ? $response['width'] : '',
            'height' => !empty($response['height']) ? $response['height'] : '',
            'format' => !empty($response['format']) ? $response['format'] : '',
            'resource_type' => !empty($response['resource_type']) ? $response['resource_type'] : '',
            'created' => !empty($response['created_at']) ? $response['created_at'] : '',
            'tags' => !empty($response['tags']) ? $response['tags'] : array(),
            'signature' => !empty($response['signature']) ? $response['signature'] : '',
            'type' => !empty($response['type']) ? $response['type'] : '',
            'etag' => !empty($response['etag']) ? $response['etag'] : '',
            'overwritten' => !empty($response['overwritten']) ? $response['overwritten'] : '',
            'alt_text' => $altText,
            'caption' => !empty($response['context']['custom']['caption']) ? $response['context']['custom']['caption'] : '',
        );

        $value = new self();
        $value->resourceId = $response['public_id'];
        $value->url = $response['url'];
        $value->secure_url = $response['secure_url'];
        $value->size = $response['bytes'];
        $value->metaData = $metaData;
        $value->variations = !empty($response['variations']) ? $response['variations'] : array();

        if ($response['resource_type'] == 'video') {
            $value->mediaType = Value::TYPE_VIDEO;
        } else if ($response['resource_type'] == 'image' && (!isset($response['format']) || !in_array($response['format'], array('pdf', 'doc', 'docx')))) {
            $value->mediaType = Value::TYPE_IMAGE;
        } else {
            $value->mediaType = Value::TYPE_OTHER;
        }

        return $value;
    }
}
