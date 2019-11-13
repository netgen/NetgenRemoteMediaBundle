<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Core\FieldType\RemoteMedia;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    const EXAMPLE_PARAMETERS = [
        'resourceId' => 'c87hg9xfxrd4itiim3t0',
        'url' => 'http://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
        'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
        'size' => '120253',
        'mediaType' => 'image',
        'variations' => [
            'variation1',
            'variation2',
        ],
        'metaData' => [
            'version' => '1371995958',
            'width' => '864',
            'height' => '576',
            'format' => 'jpg',
            'resource_type' => 'image',
            'created' => '2013-06-23T13:59:18Z',
            'tags' => ['tag1'],
            'signature' => 'f8645b000be7d717599affc89a068157e4748276',
            'type' => 'upload',
            'etag' => 'test_tag',
            'overwritten' => 'true',
            'alt_text' => 'alt text',
            'caption' => 'caption text',
        ],
    ];

    const EMPTY_PARAMETERS = [
        'resourceId' => null,
        'url' => null,
        'secure_url' => null,
        'size' => null,
        'mediaType' => 'image',
        'variations' => [],
        'metaData' => [
            'format' => '',
            'alt_text' => '',
            'caption' => '',
            'width' => '',
            'height' => '',
            'tags' => [],
        ],
    ];

    const EXAMPLE_CLOUDINARY_RESPONSE = [
        'public_id' => 'c87hg9xfxrd4itiim3t0',
        'version' => '1371995958',
        'signature' => 'f8645b000be7d717599affc89a068157e4748276',
        'width' => '864',
        'height' => '576',
        'format' => 'jpg',
        'resource_type' => 'image',
        'created_at' => '2013-06-23T13:59:18Z',
        'bytes' => '120253',
        'type' => 'upload',
        'url' => 'http://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
        'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
        'etag' => 'test_tag',
        'tags' => ['tag1'],
        'overwritten' => 'true',
        'context' => [
            'custom' => [
                'alt' => 'alt text',
                'caption' => 'caption text',
            ],
        ],
        'variations' => [
            'variation1',
            'variation2',
        ],
    ];

    public function testInstanceOfValue()
    {
        $this->assertInstanceOf(BaseValue::class, new Value());
    }

    public function testConstructionWithParameters()
    {
        $value = new Value(self::EXAMPLE_PARAMETERS);

        $this->assertEquals(\json_encode(self::EXAMPLE_PARAMETERS), (string) $value);
    }

    public function testConstructionWithoutParameters()
    {
        $value = new Value();

        $this->assertEquals(\json_encode(self::EMPTY_PARAMETERS), (string) $value);
    }

    public function testImageConstructionFromCloudinaryResponse()
    {
        $value = Value::createFromCloudinaryResponse(self::EXAMPLE_CLOUDINARY_RESPONSE);

        $this->assertEquals(\json_encode(self::EXAMPLE_PARAMETERS), (string) $value);
    }

    public function testVideoConstructionFromCloudinaryResponse()
    {
        $exampleCloudinaryResponse = self::EXAMPLE_CLOUDINARY_RESPONSE;
        $exampleCloudinaryResponse['resource_type'] = 'video';

        $value = Value::createFromCloudinaryResponse($exampleCloudinaryResponse);

        $exampleParameters = self::EXAMPLE_PARAMETERS;
        $exampleParameters['mediaType'] = 'video';
        $exampleParameters['metaData']['resource_type'] = 'video';

        $this->assertEquals(\json_encode($exampleParameters), (string) $value);
    }

    public function testPdfConstructionFromCloudinaryResponse()
    {
        $exampleCloudinaryResponse = self::EXAMPLE_CLOUDINARY_RESPONSE;
        $exampleCloudinaryResponse['resource_type'] = 'pdf';

        $value = Value::createFromCloudinaryResponse($exampleCloudinaryResponse);

        $exampleParameters = self::EXAMPLE_PARAMETERS;
        $exampleParameters['mediaType'] = 'other';
        $exampleParameters['metaData']['resource_type'] = 'pdf';

        $this->assertEquals(\json_encode($exampleParameters), (string) $value);
    }
}
