<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Quality;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Quality::class)]
final class QualityTest extends TestCase
{
    protected Quality $quality;

    protected function setUp(): void
    {
        $this->quality = new Quality();
    }

    public function testQualitySimple(): void
    {
        self::assertSame(
            ['quality' => 80],
            $this->quality->process([80]),
        );
    }

    public function testQualityWithAutoType(): void
    {
        self::assertSame(
            [
                'quality' => 'auto:best',
            ],
            $this->quality->process(['auto', 'best']),
        );
    }

    public function testQualityWithNonAutoType(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->quality->process(['test', 'best']);
    }

    public function testWithoutConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->quality->process();
    }
}
