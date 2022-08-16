<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Format;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\TestCase;

final class FormatTest extends TestCase
{
    protected Format $format;

    protected function setUp(): void
    {
        $this->format = new Format();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Format::process
     */
    public function test(): void
    {
        self::assertSame(
            ['fetch_format' => 'png'],
            $this->format->process(['png']),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Format::process
     */
    public function testInvalidConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->format->process();
    }
}
