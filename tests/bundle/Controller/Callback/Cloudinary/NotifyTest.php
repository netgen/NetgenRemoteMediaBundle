<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Callback\Cloudinary;

use Doctrine\ORM\EntityManagerInterface;
use Netgen\Bundle\RemoteMediaBundle\Controller\Callback\Cloudinary\Notify as NotifyController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CacheableGatewayInterface;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
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
    private NotifyController $fixedFolderModeController;

    private NotifyController $dynamicFolderModeController;

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

        $this->fixedFolderModeController = new NotifyController(
            $this->gatewayMock,
            $this->providerMock,
            $this->signatureVerifierMock,
            $this->entityManagerMock,
            $this->eventDispatcherMock,
            CloudinaryProvider::FOLDER_MODE_FIXED,
        );

        $this->dynamicFolderModeController = new NotifyController(
            $this->gatewayMock,
            $this->providerMock,
            $this->signatureVerifierMock,
            $this->entityManagerMock,
            $this->eventDispatcherMock,
            CloudinaryProvider::FOLDER_MODE_DYNAMIC,
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

        $response = $this->fixedFolderModeController->__invoke($request);

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

        $response = $this->fixedFolderModeController->__invoke($request);

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

        $response = $this->fixedFolderModeController->__invoke($request);

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
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "loadByRemoteId" with value "%s" matches one of the expecting values.',
                            $resource->getRemoteId(),
                        ),
                    ),
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

        $response = $this->fixedFolderModeController->__invoke($request);

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

        $response = $this->fixedFolderModeController->__invoke($request);

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

    public function testResourceMovedFixed(): void
    {
        $body = json_encode([
            'notification_type' => 'move',
            'resources' => [
                'sample' => [
                    'resource_type' => 'image',
                    'type' => 'upload',
                    'from_asset_folder' => 'clothing',
                    'to_asset_folder' => 'clothing_sale',
                    'display_name' => 'blue_sweater',
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
            ->expects(self::never())
            ->method('invalidateResourceListCache');

        $this->gatewayMock
            ->expects(self::never())
            ->method('invalidateFoldersCache');

        $this->gatewayMock
            ->expects(self::never())
            ->method('invalidateResourceCache');

        $this->providerMock
            ->expects(self::never())
            ->method('loadByRemoteId');

        $this->providerMock
            ->expects(self::never())
            ->method('store');

        $response = $this->fixedFolderModeController->__invoke($request);

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

    public function testResourceMovedDynamic(): void
    {
        $body = json_encode([
            'notification_type' => 'move',
            'resources' => [
                'blue_sweater' => [
                    'resource_type' => 'image',
                    'type' => 'upload',
                    'from_asset_folder' => 'clothing',
                    'to_asset_folder' => 'clothing_sale',
                    'display_name' => 'blue_sweater',
                ],
                'red_shirt' => [
                    'resource_type' => 'image',
                    'type' => 'upload',
                    'from_asset_folder' => 'shirts',
                    'to_asset_folder' => 'old_shirts',
                    'display_name' => 'red shirt',
                ],
                'non_existing_shirt' => [
                    'resource_type' => 'image',
                    'type' => 'upload',
                    'from_asset_folder' => 'shirts',
                    'to_asset_folder' => 'old_shirts',
                    'display_name' => 'some shirt',
                ],
                'black_pants' => [
                    'resource_type' => 'video',
                    'type' => 'upload',
                    'from_asset_folder' => 'clothing/pants',
                    'to_asset_folder' => 'clothing_sale/pants',
                    'display_name' => 'Black pants',
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
            ->method('invalidateFoldersCache');

        $this->gatewayMock
            ->expects(self::exactly(4))
            ->method('invalidateResourceCache')
            ->willReturnCallback(
                static fn (CloudinaryRemoteId $cloudinaryRemoteId) => match ($cloudinaryRemoteId->getRemoteId()) {
                    'upload|image|blue_sweater', 'upload|image|red_shirt', 'upload|image|non_existing_shirt', 'upload|video|black_pants' => null,
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "invalidateResourceCache" with value "%s" matches one of the expecting values.',
                            $cloudinaryRemoteId->getRemoteId(),
                        ),
                    ),
                },
            );

        $blueSweater = new RemoteResource(
            remoteId: 'upload|image|blue_sweater',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/blue_sweater',
            md5: 'r43tr4t45454324342',
            id: 5,
            name: 'Sweater (blue)',
            folder: Folder::fromPath('clothing'),
            size: 380250,
        );

        $redShirt = new RemoteResource(
            remoteId: 'upload|image|red_shirt',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/video/image/red_shirt',
            md5: '3r43456fdgregregre',
            id: 6,
            name: 'red shirt',
            folder: Folder::fromPath('shirts'),
            size: 3802350,
        );

        $blackPants = new RemoteResource(
            remoteId: 'upload|video|black_pants',
            type: 'video',
            url: 'https://res.cloudinary.com/demo/video/upload/black_pants',
            md5: '3r43456fdgregregre',
            id: 6,
            name: 'black_pants',
            folder: Folder::fromPath('clothing/pants'),
            size: 329987438,
        );

        $this->providerMock
            ->expects(self::exactly(4))
            ->method('loadByRemoteId')
            ->willReturnCallback(
                static fn (string $remoteId): ?RemoteResource => match ($remoteId) {
                    'upload|image|blue_sweater' => $blueSweater,
                    'upload|image|red_shirt' => $redShirt,
                    'upload|image|non_existing_shirt' => throw new RemoteResourceNotFoundException('upload|image|non_existing_shirt'),
                    'upload|video|black_pants' => $blackPants,
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "loadByRemoteId" with value "%s" matches one of the expecting values.',
                            $remoteId,
                        ),
                    ),
                },
            );

        $this->providerMock
            ->expects(self::exactly(3))
            ->method('store')
            ->willReturnCallback(
                static fn (RemoteResource $resource): ?RemoteResource => match ($resource->getRemoteId() . $resource->getFolder()->getPath() . $resource->getName()) {
                    'upload|image|blue_sweaterclothing_saleblue_sweater', 'upload|image|red_shirtold_shirtsred shirt', 'upload|video|black_pantsclothing_sale/pantsBlack pants' => $resource,
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "store" with value "%s" matches one of the expecting values.',
                            $resource->getRemoteId() . $resource->getFolder()->getPath() . $resource->getName(),
                        ),
                    ),
                },
            );

        $response = $this->dynamicFolderModeController->__invoke($request);

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

    public function testDisplayNameChangedFixed(): void
    {
        $body = json_encode([
            'notification_type' => 'resource_display_name_changed',
            'resources' => [
                'sample' => [
                    'resource_type' => 'image',
                    'type' => 'upload',
                    'public_id' => 'upload',
                    'from_asset_folder' => 'clothing',
                    'to_asset_folder' => 'clothing_sale',
                    'display_name' => 'blue_sweater',
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
            ->expects(self::never())
            ->method('invalidateResourceListCache');

        $this->gatewayMock
            ->expects(self::never())
            ->method('invalidateResourceCache');

        $this->providerMock
            ->expects(self::never())
            ->method('loadByRemoteId');

        $this->providerMock
            ->expects(self::never())
            ->method('store');

        $response = $this->fixedFolderModeController->__invoke($request);

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

    public function testDisplayNameChangedDynamic(): void
    {
        $body = json_encode([
            'notification_type' => 'resource_display_name_changed',
            'resources' => [
                'blue_sweater' => [
                    'resource_type' => 'image',
                    'type' => 'upload',
                    'public_id' => 'blue_sweater',
                    'new_display_name' => 'blue_sweater',
                ],
                'red_shirt' => [
                    'resource_type' => 'image',
                    'type' => 'upload',
                    'public_id' => 'red_shirt',
                    'new_display_name' => 'red shirt',
                ],
                'non_existing_shirt' => [
                    'resource_type' => 'image',
                    'type' => 'upload',
                    'public_id' => 'non_existing_shirt',
                    'new_display_name' => 'some shirt',
                ],
                'black_pants' => [
                    'resource_type' => 'video',
                    'type' => 'upload',
                    'public_id' => 'black_pants',
                    'new_display_name' => 'Black pants',
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
            ->expects(self::exactly(4))
            ->method('invalidateResourceCache')
            ->willReturnCallback(
                static fn (CloudinaryRemoteId $cloudinaryRemoteId) => match ($cloudinaryRemoteId->getRemoteId()) {
                    'upload|image|blue_sweater', 'upload|image|red_shirt', 'upload|image|non_existing_shirt', 'upload|video|black_pants' => null,
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "invalidateResourceCache" with value "%s" matches one of the expecting values.',
                            $cloudinaryRemoteId->getRemoteId(),
                        ),
                    ),
                },
            );

        $blueSweater = new RemoteResource(
            remoteId: 'upload|image|blue_sweater',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/image/upload/blue_sweater',
            md5: 'r43tr4t45454324342',
            id: 5,
            name: 'Sweater (blue)',
            folder: Folder::fromPath('clothing'),
            size: 380250,
        );

        $redShirt = new RemoteResource(
            remoteId: 'upload|image|red_shirt',
            type: 'image',
            url: 'https://res.cloudinary.com/demo/video/image/red_shirt',
            md5: '3r43456fdgregregre',
            id: 6,
            name: 'red shirt',
            folder: Folder::fromPath('shirts'),
            size: 3802350,
        );

        $blackPants = new RemoteResource(
            remoteId: 'upload|video|black_pants',
            type: 'video',
            url: 'https://res.cloudinary.com/demo/video/upload/black_pants',
            md5: '3r43456fdgregregre',
            id: 6,
            name: 'black_pants',
            folder: Folder::fromPath('clothing/pants'),
            size: 329987438,
        );

        $this->providerMock
            ->expects(self::exactly(4))
            ->method('loadByRemoteId')
            ->willReturnCallback(
                static fn (string $remoteId): ?RemoteResource => match ($remoteId) {
                    'upload|image|blue_sweater' => $blueSweater,
                    'upload|image|red_shirt' => $redShirt,
                    'upload|image|non_existing_shirt' => throw new RemoteResourceNotFoundException('upload|image|non_existing_shirt'),
                    'upload|video|black_pants' => $blackPants,
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "loadByRemoteId" with value "%s" matches one of the expecting values.',
                            $remoteId,
                        ),
                    ),
                },
            );

        $this->providerMock
            ->expects(self::exactly(3))
            ->method('store')
            ->willReturnCallback(
                static fn (RemoteResource $resource): ?RemoteResource => match ($resource->getRemoteId() . $resource->getName()) {
                    'upload|image|blue_sweaterblue_sweater', 'upload|image|red_shirtred shirt', 'upload|video|black_pantsBlack pants' => $resource,
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "store" with value "%s" matches one of the expecting values.',
                            $resource->getRemoteId() . $resource->getName(),
                        ),
                    ),
                },
            );

        $response = $this->dynamicFolderModeController->__invoke($request);

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
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "loadByRemoteId" with value "%s" matches one of the expecting values.',
                            $remoteId,
                        ),
                    ),
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
                    default => throw new RuntimeException(
                        sprintf(
                            'Failed asserting that argument #1 for method "store" with value "%s" matches one of the expecting values.',
                            $resource->getRemoteId(),
                        ),
                    ),
                },
            );

        $response = $this->fixedFolderModeController->__invoke($request);

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

        $response = $this->fixedFolderModeController->__invoke($request);

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

        $response = $this->fixedFolderModeController->__invoke($request);

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

    public function testFolderRenamed(): void
    {
        $body = json_encode([
            'notification_type' => 'move_or_rename_asset_folder',
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

        $response = $this->fixedFolderModeController->__invoke($request);

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
