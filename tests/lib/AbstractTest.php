<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests;

use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use PHPUnit\Framework\TestCase;

use function count;

abstract class AbstractTest extends TestCase
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
            $expected->isPublic(),
            $actual->isPublic(),
        );

        self::assertSame(
            $expected->isPrivate(),
            $actual->isPrivate(),
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
    }

    public static function assertRemoteResourceLocationSame(RemoteResourceLocation $expected, RemoteResourceLocation $actual): void
    {
        self::assertRemoteResourceSame(
            $expected->getRemoteResource(),
            $actual->getRemoteResource(),
        );

        foreach ($expected->getCropSettings() as $key => $cropSettings) {
            self::assertCropSettingsSame(
                $expected->getCropSettings()[$key],
                $actual->getCropSettings()[$key],
            );
        }
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

        self::assertSame(
            count($expected->getResources()),
            count($actual->getResources()),
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
