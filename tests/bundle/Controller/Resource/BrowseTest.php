<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Resource;

use Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse as BrowseController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;

final class BrowseTest extends TestCase
{
    private BrowseController $controller;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\ProviderInterface */
    private MockObject $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->controller = new BrowseController($this->providerMock);
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse::formatResource
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse::formatResources
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse::getArrayFromInputBag
     */
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

        $query = new Query([
            'query' => 'image',
            'types' => ['image'],
            'folders' => ['media'],
            'visibilities' => ['public'],
            'tags' => ['test'],
            'limit' => 20,
            'nextCursor' => 'ewdewr43r43r43',
        ]);

        $result = new Result(
            10,
            'i4gtgoijf94fef43dss',
            [
                new RemoteResource([
                    'remoteId' => 'upload|image|media/images/image.jpg',
                    'type' => 'image',
                    'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
                    'name' => 'image.jpg',
                    'folder' => Folder::fromPath('media/images'),
                    'size' => 95,
                    'tags' => ['test', 'image'],
                    'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
                ]),
                new RemoteResource([
                    'remoteId' => 'upload|image|media/images/image2.jpg',
                    'type' => 'image',
                    'url' => 'https://cloudinary.com/test/upload/images/image2.jpg',
                    'name' => 'image2.jpg',
                    'folder' => Folder::fromPath('media/images'),
                    'size' => 75,
                    'tags' => ['test'],
                    'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
                ]),
                new RemoteResource([
                    'remoteId' => 'upload|image|media/videos/example.mp4',
                    'type' => 'video',
                    'url' => 'https://cloudinary.com/test/upload/videos/example.mp4',
                    'name' => 'example.mp4',
                    'folder' => Folder::fromPath('media/videos'),
                    'size' => 550,
                    'tags' => ['test', 'video'],
                    'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
                ]),
            ],
        );

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($result);

        $image1BrowseVariation = new RemoteResourceVariation(
            $result->getResources()[0],
            'https://cloudinary.com/test/c_fit_160_120/upload/images/image.jpg',
        );

        $image1PreviewVariation = new RemoteResourceVariation(
            $result->getResources()[0],
            'https://cloudinary.com/test/c_fit_800_600/upload/images/image.jpg',
        );

        $image2BrowseVariation = new RemoteResourceVariation(
            $result->getResources()[1],
            'https://cloudinary.com/test/c_fit_160_120/upload/images/image2.jpg',
        );

        $image2PreviewVariation = new RemoteResourceVariation(
            $result->getResources()[1],
            'https://cloudinary.com/test/c_fit_800_600/upload/images/image2.jpg',
        );

        $videoThumbnailBrowseVariation = new RemoteResourceVariation(
            $result->getResources()[2],
            'https://cloudinary.com/test/c_fit_160_120/upload/videos/example.mp4.jpg',
        );

        $videoThumbnailPreviewVariation = new RemoteResourceVariation(
            $result->getResources()[2],
            'https://cloudinary.com/test/c_fit_800_600/upload/videos/example.mp4.jpg',
        );

        $this->providerMock
            ->expects(self::exactly(4))
            ->method('buildVariation')
            ->withConsecutive(
                [
                    new RemoteResourceLocation($result->getResources()[0]),
                    'ngrm_interface',
                    'browse',
                ],
                [
                    new RemoteResourceLocation($result->getResources()[0]),
                    'ngrm_interface',
                    'preview',
                ],
                [
                    new RemoteResourceLocation($result->getResources()[1]),
                    'ngrm_interface',
                    'browse',
                ],
                [
                    new RemoteResourceLocation($result->getResources()[1]),
                    'ngrm_interface',
                    'preview',
                ],
            )
            ->willReturnOnConsecutiveCalls(
                $image1BrowseVariation,
                $image1PreviewVariation,
                $image2BrowseVariation,
                $image2PreviewVariation,
            );

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('buildVideoThumbnailVariation')
            ->withConsecutive(
                [new RemoteResourceLocation($result->getResources()[2]), 'ngrm_interface', 'browse'],
                [new RemoteResourceLocation($result->getResources()[2]), 'ngrm_interface', 'preview'],
            )
            ->willReturnOnConsecutiveCalls($videoThumbnailBrowseVariation, $videoThumbnailPreviewVariation);

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
                    'format' => null,
                    'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/images/image.jpg',
                    'previewUrl' => 'https://cloudinary.com/test/c_fit_800_600/upload/images/image.jpg',
                    'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
                    'altText' => null,
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
                    'format' => null,
                    'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/images/image2.jpg',
                    'previewUrl' => 'https://cloudinary.com/test/c_fit_800_600/upload/images/image2.jpg',
                    'url' => 'https://cloudinary.com/test/upload/images/image2.jpg',
                    'altText' => null,
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
                    'format' => null,
                    'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/videos/example.mp4.jpg',
                    'previewUrl' => 'https://cloudinary.com/test/c_fit_800_600/upload/videos/example.mp4.jpg',
                    'url' => 'https://cloudinary.com/test/upload/videos/example.mp4',
                    'altText' => null,
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

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse::formatResource
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse::formatResources
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Resource\Browse::getArrayFromInputBag
     */
    public function testWithoutResults(): void
    {
        $request = new Request();
        $request->query->add([
            'query' => 'image',
            'type' => ['image', 'video'],
            'folder' => ['media', 'other'],
            'visibility' => ['private', 'protected'],
            'tags' => 'test',
        ]);

        $query = new Query([
            'query' => 'image',
            'types' => ['image', 'video'],
            'folders' => ['media', 'other'],
            'visibilities' => ['private', 'protected'],
            'tags' => [],
        ]);

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
