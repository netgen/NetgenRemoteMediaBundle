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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;
use function sprintf;
use function time;

#[CoversClass(NotifyController::class)]
final class NotifyTest extends TestCase
{
    private NotifyController $controller;

    private CacheableGatewayInterface|MockObject $gatewayMock;

    private MockObject|ProviderInterface $providerMock;

    private MockObject|RequestVerifierInterface $signatureVerifierMock;

    private EntityManagerInterface|MockObject $entityManagerMock;

    private EventDispatcherInterface|MockObject $eventDispatcherMock;

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

        $resource = new RemoteResource(
            remoteId: 'upload|image|sample',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/sample.jpg',
            md5: 'dsf099i32432432432',
            id: 5,
            name: 'sample',
            version: '1608120578',
            size: 380250,
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

        $resource = new RemoteResource(
            remoteId: 'upload|image|sample',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/sample.jpg',
            md5: 'ef438r9438u43453432432',
            id: 5,
            name: 'sample',
            size: 380250,
        );

        $resource2 = new RemoteResource(
            remoteId: 'upload|video|sample2',
            type: 'video',
            url: 'https://res.cloudinary.com/demo/image/upload/sample2.mp4',
            md5: '43543fref43f43f43f43',
            id: 5,
            name: 'sample2',
            size: 3802530,
        );

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

        $cloudinaryRemoteId1 = CloudinaryRemoteId::fromRemoteId('upload|image|sample');
        $cloudinaryRemoteId2 = CloudinaryRemoteId::fromRemoteId('upload|video|sample2');

        $this->gatewayMock
            ->expects(self::exactly(2))
            ->method('invalidateResourceCache')
            ->willReturnCallback(
                static fn (CloudinaryRemoteId $cloudinaryRemoteId) => match ($cloudinaryRemoteId->getRemoteId()) {
                    $cloudinaryRemoteId1->getRemoteId(), $cloudinaryRemoteId2->getRemoteId() => null,
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "invalidateResourceCache" with value "%s" matches one of the expecting values.',
                            $cloudinaryRemoteId->getRemoteId(),
                        ),
                    ),
                },
            );

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('loadByRemoteId')
            ->willReturnCallback(
                static fn (string $remoteId): ?RemoteResource => match ($remoteId) {
                    $cloudinaryRemoteId1->getRemoteId() => $resource,
                    $cloudinaryRemoteId2->getRemoteId() => $resource2,
                    default => null,
                },
            );

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('remove')
            ->willReturnCallback(
                static fn (RemoteResource $resource) => match ($resource->getRemoteId()) {
                    $cloudinaryRemoteId1->getRemoteId(), $cloudinaryRemoteId2->getRemoteId() => null,
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "remove" with value "%s" matches one of the expecting values.',
                            $resource->getRemoteId(),
                        ),
                    ),
                },
            );

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
            ->willReturnCallback(
                static fn (CloudinaryRemoteId $cloudinaryRemoteId) => match ($cloudinaryRemoteId->getRemoteId()) {
                    'upload|image|sample', 'upload|video|video_sample', 'upload|raw|non_existing_sample' => null,
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "invalidateResourceCache" with value "%s" matches one of the expecting values.',
                            $cloudinaryRemoteId->getRemoteId(),
                        ),
                    ),
                },
            );

        $this->gatewayMock
            ->expects(self::once())
            ->method('invalidateTagsCache');

        $image = new RemoteResource(
            remoteId: 'upload|image|sample',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/sample',
            md5: 'r43tr4t45454324342',
            id: 5,
            name: 'sample',
            size: 380250,
            tags: ['tag1', 'tag4'],
        );

        $video = new RemoteResource(
            remoteId: 'upload|video|video_sample',
            type: 'video',
            url: 'https://res.cloudinary.com/demo/video/upload/video_sample',
            md5: '3r43456fdgregregre',
            id: 6,
            name: 'video_sample',
            size: 3802350,
            tags: ['old_tag'],
        );

        $this->providerMock
            ->expects(self::exactly(3))
            ->method('loadByRemoteId')
            ->willReturnCallback(
                static fn (string $remoteId): ?RemoteResource => match ($remoteId) {
                    'upload|image|sample' => $image,
                    'upload|video|video_sample' => $video,
                    'upload|raw|non_existing_sample' => throw new RemoteResourceNotFoundException('upload|raw|non_existing_sample'),
                    default => null,
                },
            );

        $image->addTag('tag2');
        $image->addTag('tag3');
        $image->removeTag('tag1');

        $video->addTag('new_tag');
        $video->addTag('sample_tag');
        $video->addTag('old_tag');

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('store')
            ->willReturnCallback(
                static fn (RemoteResource $resource): ?RemoteResource => match ($resource->getRemoteId()) {
                    'upload|image|sample', 'upload|video|video_sample' => $resource,
                    default => null,
                },
            );

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
