<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Templating\Twig\Runtime;

use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;

final class RemoteMediaRuntimeTest extends AbstractTest
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\ProviderInterface */
    private MockObject $providerMock;

    private RemoteMediaRuntime $runtime;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->runtime = new RemoteMediaRuntime($this->providerMock);
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResource
     */
    public function testGetRemoteResource(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('load')
            ->with(30)
            ->willReturn($resource);

        self::assertRemoteResourceSame(
            $resource,
            $this->runtime->getRemoteResource(30),
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
            ->method('load')
            ->with(50)
            ->willThrowException(new RemoteResourceNotFoundException('50'));

        self::assertNull(
            $this->runtime->getRemoteResource(50),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceLocation
     */
    public function testGetRemoteResourceLocation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

        $location = new RemoteResourceLocation($resource);

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(10)
            ->willReturn($location);

        self::assertRemoteResourceLocationSame(
            $location,
            $this->runtime->getRemoteResourceLocation(10),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceLocation
     */
    public function testGetRemoteResourceLocationNotFound(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(10)
            ->willThrowException(new RemoteResourceLocationNotFoundException(10));

        self::assertNull(
            $this->runtime->getRemoteResourceLocation(10),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceByRemoteId
     */
    public function testGetRemoteResourceByRemoteId(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('image|upload|test_image.jpg')
            ->willReturn($resource);

        self::assertRemoteResourceSame(
            $resource,
            $this->runtime->getRemoteResourceByRemoteId('image|upload|test_image.jpg'),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceByRemoteId
     */
    public function testGetRemoteResourceByRemoteIdNotFound(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('image|upload|test_image.jpg')
            ->willThrowException(new RemoteResourceNotFoundException('50'));

        self::assertNull(
            $this->runtime->getRemoteResourceByRemoteId('image|upload|test_image.jpg'),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceFromRemote
     */
    public function testGetRemoteResourceFromRemote(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('image|upload|test_image.jpg')
            ->willReturn($resource);

        self::assertRemoteResourceSame(
            $resource,
            $this->runtime->getRemoteResourceFromRemote('image|upload|test_image.jpg'),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceByRemoteId
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceFromRemote
     */
    public function testGetRemoteResourceFromRemoteNotFound(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('image|upload|test_image.jpg')
            ->willThrowException(new RemoteResourceNotFoundException('50'));

        self::assertNull(
            $this->runtime->getRemoteResourceFromRemote('image|upload|test_image.jpg'),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::buildRemoteResourceVariation
     */
    public function testBuildRemoteResourceVariation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

        $location = new RemoteResourceLocation($resource);
        $variation = new RemoteResourceVariation($resource, 'https://cloudinary.com/upload/image/c10_20_0_0/test_image.jpg');

        $this->providerMock
            ->expects(self::once())
            ->method('buildVariation')
            ->with($location, 'article', 'header')
            ->willReturn($variation);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->runtime->buildRemoteResourceVariation($location, 'article', 'header'),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::buildRemoteResourceRawVariation
     */
    public function testBuildRemoteResourceRawVariation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_image.jpg',
            'url' => 'https://cloudinary.com/upload/image/test_image.jpg',
            'type' => 'image',
            'size' => 200,
        ]);

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 100,
            'crop' => 'crop',
        ];

        $transformations = [$cropOptions];

        $variation = new RemoteResourceVariation($resource, 'https://cloudinary.com/upload/image/c10_20_0_0/test_image.jpg');

        $this->providerMock
            ->expects(self::once())
            ->method('buildRawVariation')
            ->with($resource, $transformations)
            ->willReturn($variation);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->runtime->buildRemoteResourceRawVariation($resource, $transformations),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getVideoThumbnail
     */
    public function testGetVideoThumbnail(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_video.mp4',
            'url' => 'https://cloudinary.com/upload/video/test_video.mp4',
            'type' => 'video',
            'size' => 1500,
        ]);

        $variation = new RemoteResourceVariation($resource, 'https://cloudinary.com/upload/video/test_video.mp4');

        $this->providerMock
            ->expects(self::once())
            ->method('buildVideoThumbnail')
            ->with($resource, 15)
            ->willReturn($variation);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->runtime->getVideoThumbnail($resource, 15),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getVideoThumbnailVariation
     */
    public function testGetVideoThumbnailVariation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_video.mp4',
            'url' => 'https://cloudinary.com/upload/video/test_video.mp4',
            'type' => 'video',
            'size' => 1500,
        ]);

        $location = new RemoteResourceLocation($resource);
        $variation = new RemoteResourceVariation($resource, 'https://cloudinary.com/upload/video/c_20_10_0_0/test_video.mp4');

        $this->providerMock
            ->expects(self::once())
            ->method('buildVideoThumbnailVariation')
            ->with(
                $location,
                'article',
                'header',
                15,
            )
            ->willReturn($variation);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->runtime->getVideoThumbnailVariation(
                $location,
                'article',
                'header',
                15,
            ),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getVideoThumbnailRawVariation
     */
    public function testGetVideoThumbnailRawVariation(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_video.mp4',
            'url' => 'https://cloudinary.com/upload/video/test_video.mp4',
            'type' => 'video',
            'size' => 1500,
        ]);

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 100,
            'crop' => 'crop',
        ];

        $transformations = [$cropOptions];

        $variation = new RemoteResourceVariation($resource, 'https://cloudinary.com/upload/video/c_20_10_0_0/test_video.mp4');

        $this->providerMock
            ->expects(self::once())
            ->method('buildVideoThumbnailRawVariation')
            ->with(
                $resource,
                $transformations,
                15,
            )
            ->willReturn($variation);

        self::assertRemoteResourceVariationSame(
            $variation,
            $this->runtime->getVideoThumbnailRawVariation(
                $resource,
                $transformations,
                15,
            ),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceHtmlTag
     */
    public function testGetRemoteResourceHtmlTag(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_video.mp4',
            'url' => 'https://cloudinary.com/upload/video/test_video.mp4',
            'type' => 'video',
            'size' => 1500,
        ]);

        $htmlAttributes = ['style' => 'width:100%;'];
        $tag = '<video style="width:100%;"><source src="https://cloudinary.com/upload/video/test_video.mp4"></video>';

        $this->providerMock
            ->expects(self::once())
            ->method('generateHtmlTag')
            ->with($resource, $htmlAttributes, true)
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->runtime->getRemoteResourceHtmlTag($resource, $htmlAttributes, true),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceVariationHtmlTag
     */
    public function testGetRemoteResourceVariationHtmlTag(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_video.mp4',
            'url' => 'https://cloudinary.com/upload/video/test_video.mp4',
            'type' => 'video',
            'size' => 1500,
        ]);

        $location = new RemoteResourceLocation($resource);

        $htmlAttributes = ['style' => 'width:100%;'];
        $tag = '<video style="width:100%;"><source src="https://cloudinary.com/upload/video/c_20_10_0_0/test_video.mp4"></video>';

        $this->providerMock
            ->expects(self::once())
            ->method('generateVariationHtmlTag')
            ->with(
                $location,
                'article',
                'header',
                $htmlAttributes,
                true,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->runtime->getRemoteResourceVariationHtmlTag(
                $location,
                'article',
                'header',
                $htmlAttributes,
                true,
            ),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceRawVariationHtmlTag
     */
    public function testGetRemoteResourceRawVariationHtmlTag(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_video.mp4',
            'url' => 'https://cloudinary.com/upload/video/test_video.mp4',
            'type' => 'video',
            'size' => 1500,
        ]);

        $cropOptions = [
            'x' => 5,
            'y' => 10,
            'width' => 200,
            'height' => 100,
            'crop' => 'crop',
        ];

        $transformations = [$cropOptions];

        $htmlAttributes = ['style' => 'width:100%;'];
        $tag = '<video style="width:100%;"><source src="https://cloudinary.com/upload/video/c_20_10_0_0/test_video.mp4"></video>';

        $this->providerMock
            ->expects(self::once())
            ->method('generateRawVariationHtmlTag')
            ->with(
                $resource,
                $transformations,
                $htmlAttributes,
                true,
            )
            ->willReturn($tag);

        self::assertSame(
            $tag,
            $this->runtime->getRemoteResourceRawVariationHtmlTag(
                $resource,
                $transformations,
                $htmlAttributes,
                true,
            ),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getRemoteResourceDownloadUrl
     */
    public function testGetRemoteResourceDownloadUrl(): void
    {
        $resource = new RemoteResource([
            'id' => 30,
            'remoteId' => 'test_video.mp4',
            'url' => 'https://cloudinary.com/upload/video/test_video.mp4',
            'type' => 'video',
            'size' => 1500,
        ]);

        $url = 'https://cloudinary.com/upload/video/download/test_video.mp4';

        $this->providerMock
            ->expects(self::once())
            ->method('generateDownloadLink')
            ->with($resource)
            ->willReturn($url);

        self::assertSame(
            $url,
            $this->runtime->getRemoteResourceDownloadUrl($resource),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::applyScalingFormat
     */
    public function testApplyScalingFormatEmpty(): void
    {
        self::assertSame(
            [],
            $this->runtime->applyScalingFormat([]),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::applyScalingFormat
     */
    public function testApplyScalingFormat(): void
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
            $this->runtime->applyScalingFormat($variations),
        );
    }
}
