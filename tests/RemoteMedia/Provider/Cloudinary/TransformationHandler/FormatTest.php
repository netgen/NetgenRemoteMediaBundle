<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Format;

class FormatTest extends BaseTest
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler\Format
     */
    protected $format;

    protected function setUp()
    {
        parent::setUp();
        $this->format = new Format();
    }

    public function testFormat()
    {
        self::assertEquals(
            ['fetch_format' => 'png'],
            $this->format->process($this->value, 'png_format', ['png']),
        );
    }

    public function testMissingFormatConfiguration()
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->format->process($this->value, 'png_format');
    }
}
