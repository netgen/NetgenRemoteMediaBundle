<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Templating\Twig\Runtime;

use Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use Netgen\RemoteMedia\Exception\NamedRemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\NamedRemoteResourceNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

#[CoversClass(RemoteMediaRuntime::class)]
final class RemoteMediaRuntimeTest extends AbstractTestCase
{
    private MockObject|ProviderInterface $providerMock;

    private RemoteMediaRuntime $runtime;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $variations = [
            'default' => [
                'small' => [
                    'transformations' => [
                        'crop' => [100, 100],
                    ],
                ],
                'non_croppable' => [
                    'transformations' => [
                        'resize' => [100, 100],
                    ],
                ],
            ],
        ];

        $variationResolver = new VariationResolver(
            new Registry(),
            new NullLogger(),
            $variations,
        );

        $this->runtime = new RemoteMediaRuntime(
            $this->providerMock,
            $variationResolver,
        );
    }

    public function testGetRemoteResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

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

    public function testGetRemoteResourceLocation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

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

    public function testGetRemoteResourceByRemoteId(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

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

    public function testGetRemoteResourceFromRemote(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'test_image.jpg',
            size: 200,
        );

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

    public function testBuildRemoteResourceVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

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

    public function testBuildRemoteResourceRawVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_image.jpg',
            type: 'image',
            url: 'https://cloudinary.com/upload/image/test_image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_image.jpg',
            size: 200,
        );

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

    public function testGetVideoThumbnail(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/test_video.mp4',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_video.mp4',
            size: 1500,
        );

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

    public function testGetVideoThumbnailVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/test_video.mp4',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_video.mp4',
            size: 1500,
        );

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

    public function testGetVideoThumbnailRawVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/test_video.mp4',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_video.mp4',
            size: 1500,
        );

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

    public function testGetRemoteResourceHtmlTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/test_video.mp4',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_video.mp4',
            size: 1500,
        );

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

    public function testGetRemoteResourceVariationHtmlTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/test_video.mp4',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_video.mp4',
            size: 1500,
        );

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

    public function testGetRemoteResourceRawVariationHtmlTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/test_video.mp4',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_video.mp4',
            size: 1500,
        );

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

    public function testGetRemoteResourceDownloadUrl(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/test_video.mp4',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_video.mp4',
            size: 1500,
        );

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

    public function testGetAvailableVariations(): void
    {
        self::assertSame(
            [
                'small' => [
                    'transformations' => [
                        'crop' => [100, 100],
                    ],
                ],
                'non_croppable' => [
                    'transformations' => [
                        'resize' => [100, 100],
                    ],
                ],
            ],
            $this->runtime->getAvailableVariations(),
        );
    }

    public function testGetAvailableCroppableVariations(): void
    {
        self::assertSame(
            [
                'small' => [
                    'transformations' => [
                        'crop' => [100, 100],
                    ],
                ],
            ],
            $this->runtime->getAvailableCroppableVariations(),
        );
    }

    public function testApplyScalingFormatEmpty(): void
    {
        self::assertSame(
            [],
            $this->runtime->applyScalingFormat([]),
        );
    }

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

    public function testAuthenticateRemoteResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/test_video.mp4',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_video.mp4',
            size: 1500,
        );

        $duration = 500;
        $token = AuthToken::fromDuration($duration);

        $authenticatedRemoteResource = new AuthenticatedRemoteResource(
            $resource,
            'https://cloudinary.com/upload/video/test_video.mp4?_token=08c0f6285317dd072adb852914f4e2b8',
            $token,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('authenticateRemoteResource')
            ->willReturn($authenticatedRemoteResource);

        self::assertRemoteResourceSame(
            $authenticatedRemoteResource,
            $this->runtime->authenticateRemoteResource($resource, 500),
        );
    }

    public function testAuthenticateRemoteResourceLocation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/video/test_video.mp4',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_video.mp4',
            size: 1500,
        );

        $duration = 500;
        $token = AuthToken::fromDuration($duration);
        $url = 'https://cloudinary.com/upload/video/test_video.mp4?token=a2b0a07dbe418d45544ba186addf827d';

        $location = new RemoteResourceLocation($resource);

        $authenticatedRemoteResource = new AuthenticatedRemoteResource(
            remoteResource: $resource,
            url: $url,
            token: $token,
        );

        $authenticatedLocation = new RemoteResourceLocation($authenticatedRemoteResource);

        $this->providerMock
            ->expects(self::once())
            ->method('authenticateRemoteResourceLocation')
            ->willReturn($authenticatedLocation);

        self::assertRemoteResourceLocationSame(
            $authenticatedLocation,
            $this->runtime->authenticateRemoteResourceLocation($location, 500),
        );
    }

    public function testGetNamedRemoteResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/image/image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            id: 30,
            name: 'test_video.mp4',
            size: 1500,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('loadNamedRemoteResource')
            ->with('test_video')
            ->willReturn($resource);

        self::assertRemoteResourceSame(
            $resource,
            $this->runtime->getNamedRemoteResource('test_video'),
        );
    }

    public function testGetNonExistingNamedRemoteResource(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadNamedRemoteResource')
            ->with('non_existing_resource')
            ->willThrowException(new NamedRemoteResourceNotFoundException('non_existing_resource'));

        self::assertNull($this->runtime->getNamedRemoteResource('non_existing_resource'));
    }

    public function testGetNamedNonExistingRemoteResource(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadNamedRemoteResource')
            ->with('test_video')
            ->willThrowException(new RemoteResourceNotFoundException('upload|video|test_video'));

        self::assertNull($this->runtime->getNamedRemoteResource('test_video'));
    }

    public function testGetNamedRemoteResourceLocation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'test_video.mp4',
            type: 'video',
            url: 'https://cloudinary.com/upload/image/image.jpg',
            md5: 'a522f23sf81aa0afd03387c37e2b6eax',
            name: 'test_video.mp4',
            size: 1500,
        );

        $location = new RemoteResourceLocation($resource);

        $this->providerMock
            ->expects(self::once())
            ->method('loadNamedRemoteResourceLocation')
            ->with('test_video_location')
            ->willReturn($location);

        self::assertRemoteResourceLocationSame(
            $location,
            $this->runtime->getNamedRemoteResourceLocation('test_video_location'),
        );
    }

    public function testGetNonExistingNamedRemoteResourceLocation(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadNamedRemoteResourceLocation')
            ->with('non_existing_resource_location')
            ->willThrowException(new NamedRemoteResourceLocationNotFoundException('non_existing_resource_location'));

        self::assertNull($this->runtime->getNamedRemoteResourceLocation('non_existing_resource_location'));
    }

    public function testGetNamedRemoteResourceLocationNonExistingResource(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadNamedRemoteResourceLocation')
            ->with('test_video_location')
            ->willThrowException(new RemoteResourceNotFoundException('upload|video|test_video'));

        self::assertNull($this->runtime->getNamedRemoteResourceLocation('test_video_location'));
    }
}
