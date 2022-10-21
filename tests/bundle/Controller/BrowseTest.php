<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller;

use Netgen\Bundle\RemoteMediaBundle\Controller\Browse as BrowseController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
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
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Browse::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Browse::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Browse::formatResource
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Browse::formatResources
     */
    public function test(): void
    {
        $request = new Request();
        $request->query->add([
            'query' => 'image',
            'type' => 'image',
            'folder' => 'media',
            'tag' => 'test',
            'limit' => 20,
            'next_cursor' => 'ewdewr43r43r43',
        ]);

        $query = new Query([
            'query' => 'image',
            'types' => ['image'],
            'folders' => ['media'],
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
                    'size' => 95,
                    'tags' => ['test', 'image'],
                ]),
                new RemoteResource([
                    'remoteId' => 'upload|image|media/images/image2.jpg',
                    'type' => 'image',
                    'url' => 'https://cloudinary.com/test/upload/images/image2.jpg',
                    'size' => 75,
                    'tags' => ['test'],
                ]),
                new RemoteResource([
                    'remoteId' => 'upload|image|media/videos/example.mp4',
                    'type' => 'video',
                    'url' => 'https://cloudinary.com/test/upload/videos/example.mp4',
                    'size' => 550,
                    'tags' => ['test', 'video'],
                ]),
            ],
        );

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($result);

        $transformation = [
            'crop' => 'fit',
            'width' => 160,
            'height' => 120,
        ];

        $image1Variation = new RemoteResourceVariation(
            $result->getResources()[0],
            'https://cloudinary.com/test/c_fit_160_120/upload/images/image.jpg',
        );

        $image2Variation = new RemoteResourceVariation(
            $result->getResources()[1],
            'https://cloudinary.com/test/c_fit_160_120/upload/images/image2.jpg',
        );

        $videoThumbnailVariation = new RemoteResourceVariation(
            $result->getResources()[2],
            'https://cloudinary.com/test/c_fit_160_120/upload/videos/example.mp4.jpg',
        );

        $videoThumbnail = new RemoteResourceVariation(
            $result->getResources()[2],
            'https://cloudinary.com/test/upload/videos/example.mp4.jpg',
        );

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('buildRawVariation')
            ->withConsecutive(
                [
                    $result->getResources()[0],
                    [$transformation],
                ],
                [
                    $result->getResources()[1],
                    [$transformation],
                ],
            )
            ->willReturnOnConsecutiveCalls(
                $image1Variation,
                $image2Variation,
            );

        $this->providerMock
            ->expects(self::once())
            ->method('buildVideoThumbnailRawVariation')
            ->with($result->getResources()[2], [$transformation])
            ->willReturn($videoThumbnailVariation);

        $this->providerMock
            ->expects(self::once())
            ->method('buildVideoThumbnail')
            ->with($result->getResources()[2])
            ->willReturn($videoThumbnail);

        $expectedResponseContent = json_encode([
            'hits' => [
                [
                    'remoteId' => 'upload|image|media/images/image.jpg',
                    'tags' => ['test', 'image'],
                    'type' => 'image',
                    'size' => 95,
                    'width' => null,
                    'height' => null,
                    'filename' => 'image.jpg',
                    'format' => null,
                    'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/images/image.jpg',
                    'previewUrl' => 'https://cloudinary.com/test/upload/images/image.jpg',
                    'url' => 'https://cloudinary.com/test/upload/images/image.jpg',
                    'altText' => null,
                ],
                [
                    'remoteId' => 'upload|image|media/images/image2.jpg',
                    'tags' => ['test'],
                    'type' => 'image',
                    'size' => 75,
                    'width' => null,
                    'height' => null,
                    'filename' => 'image2.jpg',
                    'format' => null,
                    'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/images/image2.jpg',
                    'previewUrl' => 'https://cloudinary.com/test/upload/images/image2.jpg',
                    'url' => 'https://cloudinary.com/test/upload/images/image2.jpg',
                    'altText' => null,
                ],
                [
                    'remoteId' => 'upload|image|media/videos/example.mp4',
                    'tags' => ['test', 'video'],
                    'type' => 'video',
                    'size' => 550,
                    'width' => null,
                    'height' => null,
                    'filename' => 'example.mp4',
                    'format' => null,
                    'browseUrl' => 'https://cloudinary.com/test/c_fit_160_120/upload/videos/example.mp4.jpg',
                    'previewUrl' => 'https://cloudinary.com/test/upload/videos/example.mp4.jpg',
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
}
