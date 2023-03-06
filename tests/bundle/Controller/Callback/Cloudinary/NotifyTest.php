<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Callback\Cloudinary;

use Doctrine\ORM\EntityManagerInterface;
use Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify as NotifyController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CacheableGatewayInterface;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;
use Netgen\RemoteMedia\Core\RequestVerifierInterface;
use Netgen\RemoteMedia\Event\Cloudinary\NotificationReceivedEvent;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function in_array;
use function json_encode;
use function sprintf;
use function time;

final class NotifyTest extends TestCase
{
    private NotifyController $controller;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\Core\Provider\Cloudinary\GatewayInterface */
    private MockObject $gatewayMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\ProviderInterface */
    private MockObject $providerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\Core\RequestVerifierInterface */
    private MockObject $signatureVerifierMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Doctrine\ORM\EntityManagerInterface */
    private MockObject $entityManagerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private MockObject $eventDispatcherMock;

    protected function setUp(): void
    {
        $this->gatewayMock = $this->createMock(CacheableGatewayInterface::class);
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->signatureVerifierMock = $this->createMock(RequestVerifierInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $this->controller = new NotifyController(
            $this->gatewayMock,
            $this->providerMock,
            $this->signatureVerifierMock,
            $this->entityManagerMock,
            $this->eventDispatcherMock,
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::returnUnverified
     */
    public function testUnverified(): void
    {
        $request = new Request();

        $this->signatureVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with($request)
            ->willReturn(false);

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            '"Signature did not pass data verification!"',
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::handleResourceUploaded
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::returnSuccess
     */
    public function testResourceUploaded(): void
    {
        $body = json_encode([
            'notification_type' => 'upload',
            'timestamp' => '2020-12-16T12:09:39+00:00',
            'request_id' => '71763d4cacf19521f5691a02c8b143b1',
            'asset_id' => 'ede59e6d3befdc65a8adc2f381c0f96f',
            'public_id' => 'sample',
            'version' => 1608120578,
            'version_id' => '3144395a27aa6c02df1ca8aaf9aa6e7a',
            'width' => 1279,
            'height' => 853,
            'format' => 'jpg',
            'resource_type' => 'image',
            'created_at' => '2020-12-16T12:09:38Z',
            'tags' => [],
            'bytes' => 380250,
            'type' => 'upload',
            'etag' => '0b40494da087cba7092d29c58aede2e2',
            'placeholder' => false,
            'url' => 'http://res.cloudinary.com/demo/image/upload/v1608120578/sample.jpg',
            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1608120578/sample.jpg',
            'original_filename' => 'jeans-1421398-1279x852',
        ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body,
        );

        $request->headers->add(
            [
                'x-cld-timestamp' => time(),
                'x-cld-signature' => 'test',
            ],
        );

        $cloudinaryRemoteId = CloudinaryRemoteId::fromRemoteId('upload|image|sample');

        $this->signatureVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with($request)
            ->willReturn(true);

        $event = new NotificationReceivedEvent($request);

        $this->eventDispatcherMock
            ->expects(self::once())
            ->method('dispatch')
            ->with($event, $event::NAME);

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateResourceListCache');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateTagsCache');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateFoldersCache');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateResourceCache')
            ->with($cloudinaryRemoteId);

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with($cloudinaryRemoteId->getRemoteId())
            ->willThrowException(new RemoteResourceNotFoundException($cloudinaryRemoteId->getRemoteId()));

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            '"Notification handled."',
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::handleResourceUploaded
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::returnSuccess
     */
    public function testResourceRewritten(): void
    {
        $body = json_encode([
            'notification_type' => 'upload',
            'timestamp' => '2020-12-16T12:09:39+00:00',
            'request_id' => '71763d4cacf19521f5691a02c8b143b1',
            'asset_id' => 'ede59e6d3befdc65a8adc2f381c0f96f',
            'public_id' => 'sample',
            'version' => 1608120578,
            'version_id' => '3144395a27aa6c02df1ca8aaf9aa6e7a',
            'width' => 1279,
            'height' => 853,
            'format' => 'jpg',
            'resource_type' => 'image',
            'created_at' => '2020-12-16T12:09:38Z',
            'tags' => [],
            'bytes' => 380250,
            'type' => 'upload',
            'etag' => '0b40494da087cba7092d29c58aede2e2',
            'placeholder' => false,
            'url' => 'http://res.cloudinary.com/demo/image/upload/v1608120578/sample.jpg',
            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1608120578/sample.jpg',
            'original_filename' => 'jeans-1421398-1279x852',
        ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body,
        );

        $request->headers->add(
            [
                'x-cld-timestamp' => time(),
                'x-cld-signature' => 'test',
            ],
        );

        $resource = new RemoteResource([
            'id' => 5,
            'remoteId' => 'upload|image|sample',
            'type' => 'image',
            'url' => 'https://res.cloudinary.com/demo/image/upload/v1608120578/sample.jpg',
            'name' => 'sample',
            'size' => 380250,
        ]);

        $cloudinaryRemoteId = CloudinaryRemoteId::fromRemoteId('upload|image|sample');

        $this->signatureVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with($request)
            ->willReturn(true);

        $event = new NotificationReceivedEvent($request);

        $this->eventDispatcherMock
            ->expects(self::once())
            ->method('dispatch')
            ->with($event, $event::NAME);

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateResourceListCache');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateTagsCache');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateFoldersCache');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateResourceCache')
            ->with($cloudinaryRemoteId);

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with($cloudinaryRemoteId->getRemoteId())
            ->willReturn($resource);

        $this->providerMock
            ->expects(self::once())
            ->method('store')
            ->with($resource)
            ->willReturn($resource);

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            '"Notification handled."',
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::handleResourceDeleted
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::returnSuccess
     */
    public function testResourcesDeleted(): void
    {
        $body = json_encode([
            'notification_type' => 'delete',
            'resources' => [
                [
                    'public_id' => 'sample',
                    'resource_type' => 'image',
                    'type' => 'upload',
                ],
                [
                    'public_id' => 'sample2',
                    'resource_type' => 'video',
                    'type' => 'upload',
                ],
            ],
        ]);

        $resource = new RemoteResource([
            'id' => 5,
            'remoteId' => 'upload|image|sample',
            'type' => 'image',
            'url' => 'https://res.cloudinary.com/demo/image/upload/v1608120578/sample.jpg',
            'name' => 'sample',
            'size' => 380250,
        ]);

        $resource2 = new RemoteResource([
            'id' => 5,
            'remoteId' => 'upload|video|sample2',
            'type' => 'video',
            'url' => 'https://res.cloudinary.com/demo/image/upload/v1608120578/sample2.mp4',
            'name' => 'sample2',
            'size' => 3802530,
        ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body,
        );

        $request->headers->add(
            [
                'x-cld-timestamp' => time(),
                'x-cld-signature' => 'test',
            ],
        );

        $this->signatureVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with($request)
            ->willReturn(true);

        $event = new NotificationReceivedEvent($request);

        $this->eventDispatcherMock
            ->expects(self::once())
            ->method('dispatch')
            ->with($event, $event::NAME);

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateResourceListCache');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateTagsCache');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateFoldersCache');

        $cloudinaryRemoteId = CloudinaryRemoteId::fromRemoteId('upload|image|sample');
        $cloudinaryRemoteId2 = CloudinaryRemoteId::fromRemoteId('upload|video|sample2');

        $this->gatewayMock
            ->expects(self::exactly(2))
            ->method('invalidateResourceCache')
            ->withConsecutive([$cloudinaryRemoteId], [$cloudinaryRemoteId2]);

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('loadByRemoteId')
            ->withConsecutive(
                [$cloudinaryRemoteId->getRemoteId()],
                [$cloudinaryRemoteId2->getRemoteId()],
            )
            ->willReturnOnConsecutiveCalls($resource, $resource2);

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('remove')
            ->withConsecutive([$resource], [$resource2]);

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            '"Notification handled."',
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::handleResourceDeleted
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::returnSuccess
     */
    public function testResourceDeletedNotFound(): void
    {
        $body = json_encode([
            'notification_type' => 'delete',
            'resources' => [
                [
                    'public_id' => 'sample',
                    'resource_type' => 'image',
                    'type' => 'upload',
                ],
            ],
        ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body,
        );

        $request->headers->add(
            [
                'x-cld-timestamp' => time(),
                'x-cld-signature' => 'test',
            ],
        );

        $this->signatureVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with($request)
            ->willReturn(true);

        $event = new NotificationReceivedEvent($request);

        $this->eventDispatcherMock
            ->expects(self::once())
            ->method('dispatch')
            ->with($event, $event::NAME);

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateResourceListCache');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateTagsCache');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateFoldersCache');

        $cloudinaryRemoteId = CloudinaryRemoteId::fromRemoteId('upload|image|sample');

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateResourceCache')
            ->with($cloudinaryRemoteId);

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with($cloudinaryRemoteId->getRemoteId())
            ->willThrowException(new RemoteResourceNotFoundException($cloudinaryRemoteId->getRemoteId()));

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            '"Notification handled."',
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::handleTagsChanged
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::returnSuccess
     */
    public function testTagsChanged(): void
    {
        $body = json_encode([
            'notification_type' => 'resource_tags_changed',
            'resources' => [
                [
                    'public_id' => 'sample',
                    'resource_type' => 'image',
                    'type' => 'upload',
                    'added' => ['tag2', 'tag3'],
                    'removed' => ['tag1'],
                    'updated' => [],
                ],
                [
                    'public_id' => 'video_sample',
                    'resource_type' => 'video',
                    'type' => 'upload',
                    'added' => ['new_tag'],
                    'removed' => ['old_tag'],
                    'updated' => ['sample_tag'],
                ],
                [
                    'public_id' => 'non_existing_sample',
                    'resource_type' => 'raw',
                    'type' => 'upload',
                    'added' => [],
                    'removed' => [],
                    'updated' => [],
                ],
            ],
        ]);
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body,
        );

        $request->headers->add(
            [
                'x-cld-timestamp' => time(),
                'x-cld-signature' => 'test',
            ],
        );

        $this->signatureVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with($request)
            ->willReturn(true);

        $event = new NotificationReceivedEvent($request);

        $this->eventDispatcherMock
            ->expects(self::once())
            ->method('dispatch')
            ->with($event, $event::NAME);

        $this->gatewayMock
            ->expects(self::exactly(3))
            ->method('invalidateResourceCache')
            ->willReturnCallback(static function ($cloudinaryRemoteId) {
                $expectedIds = [
                    CloudinaryRemoteId::fromRemoteId('upload|image|sample'),
                    CloudinaryRemoteId::fromRemoteId('upload|video|video_sample'),
                    CloudinaryRemoteId::fromRemoteId('upload|raw|non_existing_sample'),
                ];

                if (in_array($cloudinaryRemoteId, $expectedIds, true)) {
                    return null;
                }

                throw new RuntimeException(sprintf('Failed asserting that argument #1 for method "invalidateResourceCache" with value "%s" matches one of the expecting values.', $cloudinaryRemoteId->getRemoteId()));
            });

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateTagsCache');

        $image = new RemoteResource([
            'id' => 5,
            'remoteId' => 'upload|image|sample',
            'type' => 'image',
            'url' => 'https://res.cloudinary.com/demo/image/upload/sample',
            'name' => 'sample',
            'size' => 380250,
            'tags' => ['tag1', 'tag4'],
        ]);

        $video = new RemoteResource([
            'id' => 6,
            'remoteId' => 'upload|video|video_sample',
            'type' => 'video',
            'url' => 'https://res.cloudinary.com/demo/video/upload/video_sample',
            'name' => 'video_sample',
            'size' => 3802350,
            'tags' => ['old_tag'],
        ]);

        $this->providerMock
            ->expects(self::exactly(3))
            ->method('loadByRemoteId')
            ->willReturnCallback(static function ($remoteId) use ($image, $video) {
                if ($remoteId === 'upload|image|sample') {
                    return $image;
                }

                if ($remoteId === 'upload|video|video_sample') {
                    return $video;
                }

                if ($remoteId === 'upload|raw|non_existing_sample') {
                    throw new RemoteResourceNotFoundException('upload|raw|non_existing_sample');
                }

                throw new RuntimeException(sprintf('Failed asserting that argument #1 for method "loadByRemoteId" with value "%s" matches one of the expecting values.', $remoteId));
            });

        $image->addTag('tag2');
        $image->addTag('tag3');
        $image->removeTag('tag1');

        $video->addTag('new_tag');
        $video->addTag('sample_tag');
        $video->addTag('old_tag');

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('store')
            ->willReturnCallback(static function ($resource) use ($image, $video) {
                $expectedResources = [
                    $image,
                    $video,
                ];

                if (in_array($resource, $expectedResources, true)) {
                    return $resource;
                }

                throw new RuntimeException(sprintf('Failed asserting that remote resource as argument #1 for method "store" with remote ID "%s" matches one of the expecting values.', $resource->getRemoteId()));
            });

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            '"Notification handled."',
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::handleContextChanged
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::returnSuccess
     */
    public function testContextChanged(): void
    {
        $body = json_encode([
            'notification_type' => 'resource_context_changed',
            'resources' => [
                'sample' => [
                    'resource_type' => 'image',
                    'type' => 'upload',
                    'added' => [
                        [
                            'name' => 'alt',
                            'value' => 'New alt text',
                        ],
                        [
                            'name' => 'caption',
                            'value' => 'New caption',
                        ],
                    ],
                    'removed' => [
                        [
                            'name' => 'source',
                            'value' => 'This was once a source',
                        ],
                    ],
                    'updated' => [
                        [
                            'name' => 'type',
                            'value' => 'New type',
                        ],
                    ],
                ],
                'video_sample' => [
                    'resource_type' => 'video',
                    'type' => 'upload',
                    'added' => [
                        [
                            'name' => 'type',
                            'value' => 'New type',
                        ],
                    ],
                    'removed' => [
                        [
                            'name' => 'alt',
                            'value' => 'New alt text',
                        ],
                        [
                            'name' => 'caption',
                            'value' => 'New caption',
                        ],
                    ],
                    'updated' => [
                        [
                            'name' => 'source',
                            'value' => 'New source',
                        ],
                    ],
                ],
                'file_sample' => [
                    'resource_type' => 'raw',
                    'type' => 'upload',
                    'added' => [],
                    'removed' => [],
                    'updated' => [
                        [
                            'name' => 'alt',
                            'value' => 'New alt text',
                        ],
                        [
                            'name' => 'caption',
                            'value' => 'New caption',
                        ],
                    ],
                ],
                'non_existing_sample' => [
                    'resource_type' => 'raw',
                    'type' => 'upload',
                    'added' => [],
                    'removed' => [],
                    'updated' => [
                        [
                            'name' => 'alt_text',
                            'value' => 'New alt text',
                        ],
                    ],
                ],
            ],
        ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body,
        );

        $request->headers->add(
            [
                'x-cld-timestamp' => time(),
                'x-cld-signature' => 'test',
            ],
        );

        $this->signatureVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with($request)
            ->willReturn(true);

        $event = new NotificationReceivedEvent($request);

        $this->eventDispatcherMock
            ->expects(self::once())
            ->method('dispatch')
            ->with($event, $event::NAME);

        $this->gatewayMock
            ->expects(self::exactly(4))
            ->method('invalidateResourceCache')
            ->willReturnCallback(static function ($cloudinaryRemoteId) {
                $expectedIds = [
                    CloudinaryRemoteId::fromRemoteId('upload|image|sample'),
                    CloudinaryRemoteId::fromRemoteId('upload|video|video_sample'),
                    CloudinaryRemoteId::fromRemoteId('upload|raw|file_sample'),
                    CloudinaryRemoteId::fromRemoteId('upload|raw|non_existing_sample'),
                ];

                if (in_array($cloudinaryRemoteId, $expectedIds, true)) {
                    return null;
                }

                throw new RuntimeException(sprintf('Failed asserting that argument #1 for method "invalidateResourceCache" with value "%s" matches one of the expecting values.', $cloudinaryRemoteId->getRemoteId()));
            });

        $image = new RemoteResource([
            'id' => 5,
            'remoteId' => 'upload|image|sample',
            'type' => 'image',
            'url' => 'https://res.cloudinary.com/demo/image/upload/sample',
            'name' => 'sample',
            'size' => 380250,
            'context' => [
                'source' => 'This was once a source',
                'type' => 'Old type',
            ],
        ]);

        $video = new RemoteResource([
            'id' => 6,
            'remoteId' => 'upload|video|video_sample',
            'type' => 'video',
            'url' => 'https://res.cloudinary.com/demo/video/upload/video_sample',
            'name' => 'video_sample',
            'size' => 3802350,
            'altText' => 'New alt text',
            'caption' => 'New caption',
            'context' => [
                'source' => 'Old source',
            ],
        ]);

        $file = new RemoteResource([
            'id' => 6,
            'remoteId' => 'upload|raw|file_sample',
            'type' => 'other',
            'url' => 'https://res.cloudinary.com/demo/raw/upload/file_sample',
            'name' => 'file_sample',
            'size' => 234142,
            'altText' => 'Old alt text',
            'caption' => 'Old caption',
        ]);

        $this->providerMock
            ->expects(self::exactly(4))
            ->method('loadByRemoteId')
            ->willReturnCallback(static function ($remoteId) use ($image, $video, $file) {
                if ($remoteId === 'upload|image|sample') {
                    return $image;
                }

                if ($remoteId === 'upload|video|video_sample') {
                    return $video;
                }

                if ($remoteId === 'upload|raw|file_sample') {
                    return $file;
                }

                if ($remoteId === 'upload|raw|non_existing_sample') {
                    throw new RemoteResourceNotFoundException('upload|raw|non_existing_sample');
                }

                throw new RuntimeException(sprintf('Failed asserting that argument #1 for method "loadByRemoteId" with value "%s" matches one of the expecting values.', $remoteId));
            });

        $image->setAltText('New alt text');
        $image->setCaption('New caption');
        $image->removeContextProperty('source');
        $image->addContextProperty('type', 'New type');

        $video->setAltText(null);
        $video->setCaption(null);
        $video->addContextProperty('type', 'New type');
        $video->addContextProperty('source', 'New source');

        $file->setAltText('New alt text');

        $this->providerMock
            ->expects(self::exactly(3))
            ->method('store')
            ->willReturnCallback(static function ($resource) use ($image, $video, $file) {
                $expectedResources = [
                    $image,
                    $video,
                    $file,
                ];

                if (in_array($resource, $expectedResources, true)) {
                    return $resource;
                }

                throw new RuntimeException(sprintf('Failed asserting that remote resource as argument #1 for method "store" with remote ID "%s" matches one of the expecting values.', $resource->getRemoteId()));
            });

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            '"Notification handled."',
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::handleFoldersChanged
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::returnSuccess
     */
    public function testFolderCreated(): void
    {
        $body = json_encode([
            'notification_type' => 'create_folder',
            'resources' => [],
        ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body,
        );

        $request->headers->add(
            [
                'x-cld-timestamp' => time(),
                'x-cld-signature' => 'test',
            ],
        );

        $this->signatureVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with($request)
            ->willReturn(true);

        $event = new NotificationReceivedEvent($request);

        $this->eventDispatcherMock
            ->expects(self::once())
            ->method('dispatch')
            ->with($event, $event::NAME);

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateFoldersCache');

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            '"Notification handled."',
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::handleFoldersChanged
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify::returnSuccess
     */
    public function testFolderDeleted(): void
    {
        $body = json_encode([
            'notification_type' => 'delete_folder',
            'resources' => [],
        ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body,
        );

        $request->headers->add(
            [
                'x-cld-timestamp' => time(),
                'x-cld-signature' => 'test',
            ],
        );

        $this->signatureVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with($request)
            ->willReturn(true);

        $event = new NotificationReceivedEvent($request);

        $this->eventDispatcherMock
            ->expects(self::once())
            ->method('dispatch')
            ->with($event, $event::NAME);

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateFoldersCache');

        $response = $this->controller->__invoke($request);

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            '"Notification handled."',
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }
}
