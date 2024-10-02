<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Resource;

use Netgen\Bundle\RemoteMediaBundle\Controller\Resource\AbstractController;
use Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse as BrowseController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;

#[CoversClass(BrowseController::class)]
#[CoversClass(AbstractController::class)]
final class BrowseTest extends TestCase
{
    private BrowseController $controller;

    private MockObject|ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->controller = new BrowseController($this->providerMock);
    }

    public function test(): void
    {
        $request = new Request();
        $request->query->add([
            'query' => 'image',
            'type' => 'image',
            'folder' => 'media',
            'visibility' => 'public',
            'tag' => 'test',
            'limit' => 20,
            'next_cursor' => 'ewdewr43r43r43',
        ]);

        $query = new Query(
            query: 'image',
            types: ['image'],
            folders: ['media'],
            visibilities: ['public'],
            tags: ['test'],
            limit: 20,
            nextCursor: 'ewdewr43r43r43',
        );

        $result = new Result(
            10,
            'i4gtgoijf94fef43dss',
            [
                new RemoteResource(
                    remoteId: 'upload|image|media/images/image.jpg',
                    type: 'image',
                    url: 'https://cloudinary.com/test/upload/images/image.jpg',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'image.jpg',
                    folder: Folder::fromPath('media/images'),
                    size: 95,
                    tags: ['test', 'image'],
                ),
                new RemoteResource(
                    remoteId: 'upload|image|media/images/image2.jpg',
                    type: 'image',
                    url: 'https://cloudinary.com/test/upload/images/image2.jpg',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'image2.jpg',
                    originalFilename: 'orig_image2.jpg',
                    folder: Folder::fromPath('media/images'),
                    size: 75,
                    altText: 'test alt text',
                    caption: 'test caption',
                    tags: ['test'],
                ),
                new RemoteResource(
                    remoteId: 'upload|image|media/videos/example.mp4',
                    type: 'video',
                    url: 'https://cloudinary.com/test/upload/videos/example.mp4',
                    md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                    name: 'example.mp4',
                    originalFilename: 'example.mp4',
                    folder: Folder::fromPath('media/videos'),
                    size: 550,
                    altText: 'some alt text',
                    tags: ['test', 'video'],
                ),
            ],
        );

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($result);

        $image1BrowseVariation = new RemoteResourceVariation(
            $result->getResources()[0],
            'https://cloudinary.com/test/c_fit_160_120/f_jpg/upload/images/image.jpg',
        );

        $image1PreviewVariation = new RemoteResourceVariation(
            $result->getResources()[0],
            'https://cloudinary.com/test/c_fit_800_600/f_jpg/upload/images/image.jpg',
        );

        $image2BrowseVariation = new RemoteResourceVariation(
            $result->getResources()[1],
            'https://cloudinary.com/test/c_fit_160_120/f_jpg/upload/images/image2.jpg',
        );

        $image2PreviewVariation = new RemoteResourceVariation(
            $result->getResources()[1],
            'https://cloudinary.com/test/c_fit_800_600/f_jpg/upload/images/image2.jpg',
        );

        $videoThumbnailBrowseVariation = new RemoteResourceVariation(
            $result->getResources()[2],
            'https://cloudinary.com/test/c_fit_160_120/f_jpg/upload/videos/example.mp4.jpg',
        );

        $videoPreviewVariation = new RemoteResourceVariation(
            $result->getResources()[2],
            'https://cloudinary.com/test/c_fit_800_600/upload/videos/example.mp4.jpg',
        );

        $this->providerMock
            ->expects(self::exactly(5))
            ->method('buildVariation')
            ->willReturnCallback(
                static fn (
                    RemoteResourceLocation $location,
                    string $variationGroup,
                    string $variationName
                ) => match ($location->getRemoteResource()->getRemoteId()) {
                    'upload|image|media/images/image.jpg' => $variationName === 'browse_image' ? $image1BrowseVariation : $image1PreviewVariation,
                    'upload|image|media/images/image2.jpg' => $variationName === 'browse_image' ? $image2BrowseVariation : $image2PreviewVariation,
                    'upload|image|media/videos/example.mp4' => $videoPreviewVariation,
                    default => null,
                },
            );

        $this->providerMock
            ->expects(self::once())
            ->method('buildVideoThumbnailVariation')
            ->with(new RemoteResourceLocation($result->getResources()[2]), 'ngrm_interface', 'browse')
            ->willReturn($videoThumbnailBrowseVariation);

        $expectedResponseContent = json_encode([
            'hits' => [
                [
                    'remoteId' => 'upload|image|media/images/image.jpg',
                    'folder' => 'media/images',
                    'tags' => ['test', 'image'],
                    'type' => 'image',
                    'visibility' => 'public',
                    'size' => 95,
                    'width' => null,
                    'height' => null,
                    'filename' => 'image.jpg',
                    'originalFilename' => null,
                    'format' => null,
                    'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/f_jpg/upload/images/image.jpg',
                    'previewUrl' => 'https://cloudinary.com/test/c_fit_800_600/f_jpg/upload/images/image.jpg',
                    'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
                    'altText' => null,
                    'caption' => null,
                ],
                [
                    'remoteId' => 'upload|image|media/images/image2.jpg',
                    'folder' => 'media/images',
                    'tags' => ['test'],
                    'type' => 'image',
                    'visibility' => 'public',
                    'size' => 75,
                    'width' => null,
                    'height' => null,
                    'filename' => 'image2.jpg',
                    'originalFilename' => 'orig_image2.jpg',
                    'format' => null,
                    'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/f_jpg/upload/images/image2.jpg',
                    'previewUrl' => 'https://cloudinary.com/test/c_fit_800_600/f_jpg/upload/images/image2.jpg',
                    'url' => 'https://cloudinary.com/test/upload/images/image2.jpg',
                    'altText' => 'test alt text',
                    'caption' => 'test caption',
                ],
                [
                    'remoteId' => 'upload|image|media/videos/example.mp4',
                    'folder' => 'media/videos',
                    'tags' => ['test', 'video'],
                    'type' => 'video',
                    'visibility' => 'public',
                    'size' => 550,
                    'width' => null,
                    'height' => null,
                    'filename' => 'example.mp4',
                    'originalFilename' => 'example.mp4',
                    'format' => null,
                    'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/f_jpg/upload/videos/example.mp4.jpg',
                    'previewUrl' => 'https://cloudinary.com/test/c_fit_800_600/upload/videos/example.mp4.jpg',
                    'url' => 'https://cloudinary.com/test/upload/videos/example.mp4',
                    'altText' => 'some alt text',
                    'caption' => null,
                ],
            ],
            'load_more' => true,
            'next_cursor' => 'i4gtgoijf94fef43dss',
        ]);

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            $expectedResponseContent,
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    public function testWithoutResults(): void
    {
        $request = new Request();
        $request->query->add([
            'query' => 'image',
            'type' => ['image', 'video'],
            'folder' => ['media', 'other'],
            'visibility' => ['protected'],
            'tags' => 'test',
        ]);

        $query = new Query(
            query: 'image',
            types: ['image', 'video'],
            folders: ['media', 'other'],
            visibilities: ['protected'],
            tags: [],
        );

        $result = new Result(0, null, []);

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($result);

        $expectedResponseContent = json_encode([
            'hits' => [],
            'load_more' => false,
            'next_cursor' => null,
        ]);

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            $expectedResponseContent,
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }
}
