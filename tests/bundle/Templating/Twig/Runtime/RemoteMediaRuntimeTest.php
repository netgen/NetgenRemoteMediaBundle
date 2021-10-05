<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Templating\Twig\Runtime;

use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use Netgen\RemoteMedia\Core\VariationResolver;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RemoteMediaRuntimeTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\RemoteMedia\Core\RemoteMediaProvider
     */
    private MockObject $providerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\RemoteMedia\Core\VariationResolver
     */
    private MockObject $variationResolverMock;

    private RemoteMediaRuntime $runtime;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(RemoteMediaProvider::class);
        $this->variationResolverMock = $this->createMock(VariationResolver::class);

        $this->runtime = new RemoteMediaRuntime(
            $this->providerMock,
            $this->variationResolverMock,
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResource
     */
    public function testGetRemoteResource(): void
    {
        $resource = RemoteResource::createFromParameters([
            'resourceId' => 'test_image',
            'resourceType' => 'image',
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('getRemoteResource')
            ->with($resource->resourceId, $resource->resourceType)
            ->willReturn($resource);

        self::assertSame(
            $resource,
            $this->runtime->getRemoteResource($resource->resourceId, $resource->resourceType),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResource
     */
    public function testGetRemoteResourceNotFound(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('getRemoteResource')
            ->with('test_video', 'video')
            ->willThrowException(
                new RemoteResourceNotFoundException('test_video', 'video'),
            );

        self::assertNull(
            $this->runtime->getRemoteResource('test_video', 'video'),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getDownloadUrl
     */
    public function testGetDownloadUrl(): void
    {
        $resource = RemoteResource::createFromParameters([
            'resourceId' => 'test_file.zip',
            'resourceType' => 'raw',
        ]);

        $downloadUrl = 'https://cloudinary.com/upload/test_file.zip';

        $this->providerMock
            ->expects(self::once())
            ->method('generateDownloadLink')
            ->with($resource)
            ->willReturn($downloadUrl);

        self::assertSame(
            $downloadUrl,
            $this->runtime->getDownloadUrl($resource),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getVideoThumbnailUrl
     */
    public function testGetVideoThumbnailUrl(): void
    {
        $resource = RemoteResource::createFromParameters([
            'resourceId' => 'test_video',
            'resourceType' => 'video',
        ]);

        $videoThumbnailUrl = 'https://cloudinary.com/upload/some_variation_config/test_video_thumbnail.jpg';

        $this->providerMock
            ->expects(self::once())
            ->method('getVideoThumbnail')
            ->with($resource)
            ->willReturn($videoThumbnailUrl);

        self::assertSame(
            $videoThumbnailUrl,
            $this->runtime->getVideoThumbnailUrl($resource),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getAvailableVariations
     */
    public function testGetAvailableVariations(): void
    {
        $variations = ['variation_1', 'variation_2'];

        $this->variationResolverMock
            ->expects(self::once())
            ->method('getVariationsForGroup')
            ->with('test_group')
            ->willReturn($variations);

        self::assertSame(
            $variations,
            $this->runtime->getAvailableVariations('test_group'),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getAvailableVariations
     */
    public function testGetAvailableScalableVariations(): void
    {
        $variations = ['variation_1', 'variation_2'];

        $this->variationResolverMock
            ->expects(self::once())
            ->method('getCroppbableVariations')
            ->with('test_group')
            ->willReturn($variations);

        self::assertSame(
            $variations,
            $this->runtime->getAvailableVariations('test_group', true),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::applyScallingFormat
     */
    public function testApplyScallingFormatEmpty(): void
    {
        self::assertSame(
            [],
            $this->runtime->applyScallingFormat([]),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::applyScallingFormat
     */
    public function testApplyScallingFormat(): void
    {
        $variations = [
            'small' => [
                'transformations' => [
                    'fill' => [600, 300],
                    'crop' => [600, 300],
                ],
            ],
            'default' => [
                'transformations' => [
                    'quality' => ['auto', 'eco'],
                ],
            ],
            'big' => [
                'transformations' => [
                    'crop' => [800, 600],
                ],
            ],
        ];

        $expectedVariations = [
            'small' => [600, 300],
            'big' => [800, 600],
        ];

        self::assertSame(
            $expectedVariations,
            $this->runtime->applyScallingFormat($variations),
        );
    }
}
