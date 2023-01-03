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
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;
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

    protected function setUp(): void
    {
        $this->gatewayMock = $this->createMock(CacheableGatewayInterface::class);
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->signatureVerifierMock = $this->createMock(RequestVerifierInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->controller = new NotifyController(
            $this->gatewayMock,
            $this->providerMock,
            $this->signatureVerifierMock,
            $this->entityManagerMock,
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
}
