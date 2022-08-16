<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Quality;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use PHPUnit\Framework\TestCase;

final class QualityTest extends TestCase
{
    protected Quality $quality;

    protected function setUp(): void
    {
        $this->quality = new Quality();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Quality::process
     */
    public function testQualitySimple(): void
    {
        self::assertSame(
            ['quality' => 80],
            $this->quality->process([80]),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Quality::process
     */
    public function testQualityWithAutoType(): void
    {
        self::assertSame(
            [
                'quality' => 'auto:best',
            ],
            $this->quality->process(['auto', 'best']),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Quality::process
     */
    public function testQualityWithNonAutoType(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->quality->process(['test', 'best']);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Quality::process
     */
    public function testWithoutConfig(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->quality->process();
    }
}
