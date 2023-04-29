<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests;

use DateTimeImmutable;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use PHPUnit\Framework\TestCase;

use function count;

abstract class AbstractTestCase extends TestCase
{
    public static function assertFolderSame(Folder $expected, Folder $actual): void
    {
        self::assertSame(
            $expected->getName(),
            $actual->getName(),
        );

        self::assertSame(
            $expected->getPath(),
            $actual->getPath(),
        );

        if (!$expected->getParent() instanceof Folder) {
            self::assertSame(
                $expected->getParent(),
                $actual->getParent(),
            );

            return;
        }

        self::assertFolderSame(
            $expected->getParent(),
            $actual->getParent(),
        );
    }

    public static function assertRemoteResourceSame(RemoteResource $expected, RemoteResource $actual): void
    {
        self::assertSame(
            $expected->getId(),
            $actual->getId(),
        );

        self::assertSame(
            $expected->getRemoteId(),
            $actual->getRemoteId(),
        );

        self::assertSame(
            $expected->getType(),
            $actual->getType(),
        );

        self::assertSame(
            $expected->getUrl(),
            $actual->getUrl(),
        );

        self::assertSame(
            $expected->getName(),
            $actual->getName(),
        );

        self::assertSame(
            $expected->getVersion(),
            $actual->getVersion(),
        );

        self::assertSame(
            $expected->isPublic(),
            $actual->isPublic(),
        );

        self::assertSame(
            $expected->isProtected(),
            $actual->isProtected(),
        );

        self::assertSame(
            $expected->getVisibility(),
            $actual->getVisibility(),
        );

        self::assertSame(
            $expected->getSize(),
            $actual->getSize(),
        );

        self::assertSame(
            $expected->getAltText(),
            $actual->getAltText(),
        );

        self::assertSame(
            $expected->getCaption(),
            $actual->getCaption(),
        );

        self::assertSame(
            $expected->getTags(),
            $actual->getTags(),
        );

        self::assertSame(
            $expected->getMd5(),
            $actual->getMd5(),
        );

        self::assertSame(
            $expected->getMetadata(),
            $actual->getMetadata(),
        );

        self::assertSame(
            $expected->getCreatedAt(),
            $actual->getCreatedAt(),
        );

        self::assertSame(
            $expected->getUpdatedAt(),
            $actual->getUpdatedAt(),
        );

        if (!$expected->getFolder() instanceof Folder) {
            self::assertSame(
                $expected->getFolder(),
                $actual->getFolder(),
            );

            return;
        }

        self::assertFolderSame(
            $expected->getFolder(),
            $actual->getFolder(),
        );

        self::assertCount(
            count($expected->getLocations()),
            $actual->getLocations(),
        );

        foreach ($expected->getLocations() as $key => $location) {
            self::assertRemoteResourceLocationSame(
                $location,
                ((array) $actual->getLocations())[$key],
            );
        }

        if ($expected instanceof AuthenticatedRemoteResource) {
            self::assertInstanceOf(AuthenticatedRemoteResource::class, $actual);

            self::authTokenSame(
                $expected->getToken(),
                $actual->getToken(),
            );
        }
    }

    public static function assertRemoteResourceLocationSame(RemoteResourceLocation $expected, RemoteResourceLocation $actual): void
    {
        self::assertRemoteResourceSame(
            $expected->getRemoteResource(),
            $actual->getRemoteResource(),
        );

        self::assertSame(
            $expected->getSource(),
            $actual->getSource(),
        );

        foreach ($expected->getCropSettings() as $key => $cropSettings) {
            self::assertCropSettingsSame(
                $expected->getCropSettings()[$key],
                $actual->getCropSettings()[$key],
            );
        }

        self::assertSame(
            $expected->getWatermarkText(),
            $actual->getWatermarkText(),
        );
    }

    public static function assertRemoteResourceVariationSame(RemoteResourceVariation $expected, RemoteResourceVariation $actual): void
    {
        self::assertRemoteResourceSame(
            $expected->getRemoteResource(),
            $actual->getRemoteResource(),
        );

        self::assertSame(
            $expected->getUrl(),
            $actual->getUrl(),
        );
    }

    public static function authTokenSame(AuthToken $expected, AuthToken $actual): void
    {
        if (!($expected->getExpiresAt() instanceof DateTimeImmutable && $actual->getExpiresAt() instanceof DateTimeImmutable)) {
            self::assertSame(
                $expected->getExpiresAt(),
                $actual->getExpiresAt(),
            );
        }

        if ($expected->getExpiresAt() instanceof DateTimeImmutable && $actual->getExpiresAt() instanceof DateTimeImmutable) {
            $diff = $expected->getExpiresAt()->getTimestamp() - $actual->getExpiresAt()->getTimestamp();

            self::assertLessThan(20, $diff);
        }

        self::assertSame(
            $expected->getStartsAt(),
            $actual->getStartsAt(),
        );

        self::assertSame(
            $expected->getIpAddress(),
            $actual->getIpAddress(),
        );
    }

    public static function assertSearchResultSame(Result $expected, Result $actual): void
    {
        self::assertSame(
            $expected->getNextCursor(),
            $actual->getNextCursor(),
        );

        self::assertSame(
            $expected->getTotalCount(),
            $actual->getTotalCount(),
        );

        self::assertCount(
            count($expected->getResources()),
            $actual->getResources(),
        );

        foreach ($expected->getResources() as $key => $resource) {
            self::assertRemoteResourceSame(
                $expected->getResources()[$key],
                $actual->getResources()[$key],
            );
        }
    }

    public static function assertCropSettingsSame(CropSettings $expected, CropSettings $actual): void
    {
        self::assertSame(
            $expected->getVariationName(),
            $actual->getVariationName(),
        );

        self::assertSame(
            $expected->getWidth(),
            $actual->getWidth(),
        );

        self::assertSame(
            $expected->getHeight(),
            $actual->getHeight(),
        );

        self::assertSame(
            $expected->getX(),
            $actual->getX(),
        );

        self::assertSame(
            $expected->getY(),
            $actual->getY(),
        );
    }
}
