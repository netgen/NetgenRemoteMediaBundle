<?php

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Format;

class FormatTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Format
     */
    protected $format;

    public function setUp()
    {
        parent::setUp();
        $this->format = new Format();
    }

    public function testFormat()
    {
        $this->assertEquals(
            array('fetch_format' => 'png'),
            $this->format->process($this->value, 'png_format', array('png'))
        );
    }

    public function testMissingFormatConfiguration()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->format->process($this->value, 'png_format');
    }
}
