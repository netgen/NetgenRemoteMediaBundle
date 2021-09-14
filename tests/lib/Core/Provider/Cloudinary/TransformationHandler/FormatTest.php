<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Format;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

final class FormatTest extends BaseTest
{
    protected Format $format;

    protected function setUp(): void
    {
        parent::setUp();
        $this->format = new Format();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Format::process
     */
    public function testFormat(): void
    {
        self::assertSame(
            ['fetch_format' => 'png'],
            $this->format->process($this->resource, 'png_format', ['png']),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Format::process
     */
    public function testMissingFormatConfiguration(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->format->process($this->resource, 'png_format');
    }
}
