<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Quality;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

final class QualityTest extends BaseTest
{
    protected Quality $quality;

    protected function setUp(): void
    {
        parent::setUp();
        $this->quality = new Quality();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Quality::process
     */
    public function testQualitySimple(): void
    {
        self::assertSame(
            ['quality' => 80],
            $this->quality->process($this->resource, 'test', [80]),
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
            $this->quality->process($this->resource, 'test', ['auto', 'best']),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Quality::process
     */
    public function testQualityWithNonAutoType(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->quality->process($this->resource, 'test', ['test', 'best']);
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler\Quality::process
     */
    public function testMissingNamedTransformationConfiguration(): void
    {
        $this->expectException(TransformationHandlerFailedException::class);

        $this->quality->process($this->resource, 'test');
    }
}
