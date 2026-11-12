<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Service;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function json_encode;

#[CoversClass(RemoteResourceService::class)]
final class RemoteResourceServiceTest extends AbstractTestCase
{
    private RemoteResourceService $service;

    protected function setUp(): void
    {
        $this->service = new RemoteResourceService($this->createMock(ProviderInterface::class));
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
}
