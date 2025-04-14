<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Location;

use Netgen\Bundle\RemoteMediaBundle\Controller\Location\SelectedImage as SelectedImageController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(SelectedImageController::class)]
final class SelectedImageTest extends TestCase
{
    private SelectedImageController $controller;

    private MockObject|ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $remoteResourceService = new RemoteResourceService($this->providerMock);
        $this->controller = new SelectedImageController($this->providerMock, $remoteResourceService);
    }

    public function testWrongLocation(): void
    {
        $id = 1;

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with($id)
            ->willThrowException(new RemoteResourceLocationNotFoundException($id));

        self::expectException(RemoteResourceLocationNotFoundException::class);

        $this->controller->__invoke(1);
    }

    public function testSuccess(): void
    {
        $remoteResource = $this->getRemoteResourceWithCorrectFields();
        $remoteResource->setType('type');

        $location = $this->getRemoteResourceLocation($remoteResource);

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->willReturn($location);

        $response = $this->controller->__invoke(1);

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame($this->getExpectedJsonResponse()->getContent(), $response->getContent());
    }

    public function testPublicImageBrowseAndPreviewUrlSuccess(): void
    {
        $remoteResource = $this->getRemoteResourceWithCorrectFields();

        $location = $this->getRemoteResourceLocation($remoteResource);

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->willReturn($location);

        $this->providerMock
            ->expects(self::any())
            ->method('buildVariation')
            ->will(
                self::onConsecutiveCalls(
                    new RemoteResourceVariation($remoteResource, 'testImageBrowseUrl'),
                    new RemoteResourceVariation($remoteResource, 'testImagePreviewUrl'),
                ),
            );

        $response = $this->controller->__invoke(1);

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            $this->getExpectedJsonResponse(
                'image',
                'testImageBrowseUrl',
                'testImagePreviewUrl',
            )->getContent(),
            $response->getContent(),
        );
    }

    public function testPublicVideoBrowseAndPreviewUrlSuccess(): void
    {
        $remoteResource = $this->getRemoteResourceWithCorrectFields();
        $remoteResource->setType('video');

        $location = $this->getRemoteResourceLocation($remoteResource);

        $this->providerMock
            ->expects(self::once())
            ->method('loadLocation')
            ->willReturn($location);

        $this->providerMock
            ->expects(self::any())
            ->method('buildVideoThumbnailVariation')
            ->will(
                self::onConsecutiveCalls(
                    new RemoteResourceVariation($remoteResource, 'testVideoBrowseUrl'),
                    new RemoteResourceVariation($remoteResource, 'testVideoPreviewUrl'),
                ),
            );

        $response = $this->controller->__invoke(1);

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            $this->getExpectedJsonResponse(
                'video',
                'testVideoBrowseUrl',
                'testVideoPreviewUrl',
            )->getContent(),
            $response->getContent(),
        );
    }

    private function getRemoteResourceLocation(RemoteResource $remoteResource): RemoteResourceLocation
    {
        return new RemoteResourceLocation(
            remoteResource: $remoteResource,
            cropSettings: [new CropSettings('testVariation')],
            watermarkText: 'watermarkText',
            id: 1,
        );
    }

    private function getRemoteResourceWithCorrectFields(): RemoteResource
    {
        $remoteResource = new RemoteResource(
            remoteId: '1',
            type: 'image',
            url: 'url',
            md5: 'md5',
            id: 1,
            name: 'name',
        );

        $remoteResource->setOriginalFilename('originalFilename');
        $remoteResource->setVersion('version');
        $remoteResource->setAltText('altText');
        $remoteResource->setCaption('caption');
        $remoteResource->addTag('test');
        $remoteResource->setMetadata([
            'format' => 'format',
            'height' => 0,
            'width' => 0,
        ]);

        return $remoteResource;
    }

    private function getExpectedJsonResponse(?string $type = 'type', ?string $browseUrl = '', ?string $previewUrl = ''): JsonResponse
    {
        return new JsonResponse([
            'id' => '1',
            'name' => 'name',
            'type' => $type,
            'format' => 'format',
            'url' => 'url',
            'browse_url' => $browseUrl,
            'previewUrl' => $previewUrl,
            'alternateText' => 'altText',
            'caption' => 'caption',
            'watermarkText' => 'watermarkText',
            'tags' => ['test'],
            'size' => 0,
            'variations' => [
                'testVariation' => [
                    'x' => 0,
                    'y' => 0,
                    'w' => 0,
                    'h' => 0,
                ],
            ],
            'height' => 0,
            'width' => 0,
        ]);
    }
}
