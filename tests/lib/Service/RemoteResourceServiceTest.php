<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Service;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

use function json_encode;

#[CoversClass(RemoteResourceService::class)]
final class RemoteResourceServiceTest extends AbstractTestCase
{
    private MockObject&ProviderInterface $providerMock;

    private LoggerInterface&MockObject $loggerMock;

    private RemoteResourceService $service;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->service = new RemoteResourceService($this->providerMock, $this->loggerMock);
    }

    #[DataProvider('persistDataProvider')]
    public function testHandleRemoteUpdateUpdatesResourceOnRemote(bool $persist): void
    {
        $resource = self::createResource();

        $this->providerMock
            ->expects(self::once())
            ->method('updateOnRemote')
            ->with($resource);

        $this->providerMock
            ->expects($persist ? self::once() : self::never())
            ->method('store')
            ->with($resource);

        $this->loggerMock
            ->expects(self::never())
            ->method('warning');

        self::assertTrue(
            $this->service->handleRemoteUpdate(
                $resource,
                ['altText' => 'New alt', 'caption' => '', 'tags' => ['b', 'a']],
                $persist,
            ),
        );

        self::assertSame('New alt', $resource->getAltText());
        self::assertNull($resource->getCaption());
        self::assertSame(['a', 'b'], $resource->getTags());
    }

    public function testHandleRemoteUpdateSkipsEquivalentMetadata(): void
    {
        $resource = self::createResource();

        $this->providerMock
            ->expects(self::never())
            ->method('updateOnRemote');

        $this->providerMock
            ->expects(self::never())
            ->method('store');

        self::assertTrue(
            $this->service->handleRemoteUpdate(
                $resource,
                ['altText' => 'Old alt', 'caption' => 'Old caption', 'tags' => ['tag2', 'tag1']],
                true,
            ),
        );
    }

    #[DataProvider('persistDataProvider')]
    public function testHandleRemoteUpdateKeepsResourceMissingOnRemote(bool $persist): void
    {
        $resource = self::createResource();

        $this->providerMock
            ->expects(self::once())
            ->method('updateOnRemote')
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|example.jpg'));

        $this->providerMock
            ->expects(self::never())
            ->method('remove');

        $this->providerMock
            ->expects($persist ? self::once() : self::never())
            ->method('store')
            ->with($resource);

        $this->loggerMock
            ->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('no longer exists on the remote'),
                ['remoteId' => 'upload|image|example.jpg'],
            );

        self::assertFalse(
            $this->service->handleRemoteUpdate($resource, ['altText' => 'New alt', 'caption' => 'New caption', 'tags' => []], $persist),
        );

        self::assertSame('New alt', $resource->getAltText());
        self::assertSame('New caption', $resource->getCaption());
        self::assertSame([], $resource->getTags());
    }

    public function testResolveCropSettingsSkipsUnsetCropSizes(): void
    {
        $cropSettings = $this->service->resolveCropSettings(json_encode([
            'small' => ['x' => 10, 'y' => 20, 'w' => 300, 'h' => 400],
            'medium' => null,
            'large' => ['x' => 30, 'y' => 40, 'width' => 500, 'height' => 600],
        ]));

        self::assertCount(2, $cropSettings);
        self::assertCropSettingsSame(new CropSettings('small', 10, 20, 300, 400), $cropSettings[0]);
        self::assertCropSettingsSame(new CropSettings('large', 30, 40, 500, 600), $cropSettings[1]);
    }

    public function testResolveCropSettingsReturnsEmptyArrayForInvalidJson(): void
    {
        self::assertSame([], $this->service->resolveCropSettings('null'));
        self::assertSame([], $this->service->resolveCropSettings('invalid'));
    }

    public static function persistDataProvider(): iterable
    {
        return [
            'stored' => [true],
            'not stored' => [false],
        ];
    }

    private static function createResource(): RemoteResource
    {
        return new RemoteResource(
            remoteId: 'upload|image|example.jpg',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://example.test/example.jpg',
            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
            altText: 'Old alt',
            caption: 'Old caption',
            tags: ['tag1', 'tag2'],
        );
    }
}
