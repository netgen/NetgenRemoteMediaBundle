<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use function in_array;
use function json_encode;
use function property_exists;

final class RemoteResource
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_OTHER = 'other';

    public ?string $resourceId = null;
    public ?string $resourceType = null;
    public string $mediaType = 'image';
    public ?string $type = 'upload';

    public ?string $url = null;
    public ?string $secure_url = null;
    public int $size = 0;

    public array $variations = [];

    /** @var array<string, mixed> */
    public array $metaData = [
        'version' => '',
        'width' => '',
        'height' => '',
        'created' => '',
        'format' => '',
        'tags' => [],
        'signature' => '',
        'etag' => '',
        'overwritten' => '',
        'alt_text' => '',
        'caption' => '',
    ];

    public function __construct(array $properties = [])
    {
        foreach ($properties as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    public static function createFromCloudinaryResponse(array $response): self
    {
        $altText = !empty($response['context']['custom']['alt_text'])
            ? $response['context']['custom']['alt_text']
            : '';

        if ($altText === '') {
            $altText = !empty($response['context']['alt_text']) ? $response['context']['alt_text'] : '';
        }

        $metaData = [
            'version' => !empty($response['version']) ? $response['version'] : '',
            'width' => !empty($response['width']) ? $response['width'] : '',
            'height' => !empty($response['height']) ? $response['height'] : '',
            'format' => !empty($response['format']) ? $response['format'] : '',
            'created' => !empty($response['created_at']) ? $response['created_at'] : '',
            'tags' => !empty($response['tags']) ? $response['tags'] : [],
            'signature' => !empty($response['signature']) ? $response['signature'] : '',
            'etag' => !empty($response['etag']) ? $response['etag'] : '',
            'overwritten' => !empty($response['overwritten']) ? $response['overwritten'] : '',
            'alt_text' => $altText,
            'caption' => !empty($response['context']['custom']['caption']) ? $response['context']['custom']['caption'] : '',
        ];

        $resource = new self();
        $resource->resourceId = $response['public_id'];
        $resource->resourceType = !empty($response['resource_type']) ? $response['resource_type'] : 'image';
        $resource->type = !empty($response['type']) ? $response['type'] : 'upload';
        $resource->url = $response['url'];
        $resource->secure_url = $response['secure_url'];
        $resource->size = $response['bytes'];
        $resource->metaData = $metaData;
        $resource->variations = !empty($response['variations']) ? $response['variations'] : [];

        if ($response['resource_type'] === 'video') {
            $resource->mediaType = self::TYPE_VIDEO;
        } elseif ($response['resource_type'] === 'image' && (!isset($response['format']) || !in_array($response['format'], ['pdf', 'doc', 'docx'], true))) {
            $resource->mediaType = self::TYPE_IMAGE;
        } else {
            $resource->mediaType = self::TYPE_OTHER;
        }

        return $resource;
    }
}
