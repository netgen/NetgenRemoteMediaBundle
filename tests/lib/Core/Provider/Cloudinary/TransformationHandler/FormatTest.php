<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Format;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Format::class)]
final class FormatTest extends TestCase
{
    protected Format $format;

    protected function setUp(): void
    {
        $this->format = new Format();
    }

    public function test(): void
    {
        self::assertSame(
            ['fetch_format' => 'png'],
            $this->format->process(['png']),
        );
    }

    public function testInvalidConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->format->process();
    }
}
