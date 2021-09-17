<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Templating\Twig\Runtime;

use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RemoteMediaRuntimeTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\RemoteMedia\Core\RemoteMediaProvider
     */
    private MockObject $providerMock;

    private RemoteMediaRuntime $runtime;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(RemoteMediaProvider::class);

        $this->runtime = new RemoteMediaRuntime(
            $this->providerMock,
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
}
