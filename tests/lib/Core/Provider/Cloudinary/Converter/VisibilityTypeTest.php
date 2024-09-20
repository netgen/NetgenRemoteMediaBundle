<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Converter;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\VisibilityType as VisibilityTypeConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(VisibilityTypeConverter::class)]
final class VisibilityTypeTest extends TestCase
{
    protected VisibilityTypeConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new VisibilityTypeConverter();
    }

    #[DataProvider('fromCloudinaryTypeProvider')]
    public function testFromCloudinaryType(string $type, string $expectedFormat): void
    {
        self::assertSame(
            $expectedFormat,
            $this->converter->fromCloudinaryType($type),
        );
    }

    #[DataProvider('toCloudinaryTypeProvider')]
    public function testToCloudinaryType(string $visibility, string $expectedType): void
    {
        self::assertSame(
            $expectedType,
            $this->converter->toCloudinaryType($visibility),
        );
    }

    #[DataProvider('toCloudinaryAccessModeProvider')]
    public function testToCloudinaryAccessMode(string $visibility, string $expectedMode): void
    {
        self::assertSame(
            $expectedMode,
            $this->converter->toCloudinaryAccessMode($visibility),
        );
    }

    #[DataProvider('toCloudinaryAccessControlProvider')]
    public function testToCloudinaryAccessControl(string $visibility, array $expectedSettings): void
    {
        self::assertSame(
            $expectedSettings,
            $this->converter->toCloudinaryAccessControl($visibility),
        );
    }

    public static function fromCloudinaryTypeProvider(): array
    {
        return [
            ['upload', RemoteResource::VISIBILITY_PUBLIC],
            ['authenticated', RemoteResource::VISIBILITY_PROTECTED],
        ];
    }

    public static function toCloudinaryTypeProvider(): array
    {
        return [
            [RemoteResource::VISIBILITY_PUBLIC, 'upload'],
            [RemoteResource::VISIBILITY_PROTECTED, 'authenticated'],
        ];
    }

    public static function toCloudinaryAccessModeProvider(): array
    {
        return [
            [RemoteResource::VISIBILITY_PUBLIC, 'public'],
            [RemoteResource::VISIBILITY_PROTECTED, 'authenticated'],
        ];
    }

    public static function toCloudinaryAccessControlProvider(): array
    {
        return [
            [RemoteResource::VISIBILITY_PUBLIC, [['access_type' => 'anonymous']]],
            [RemoteResource::VISIBILITY_PROTECTED, [['access_type' => 'token']]],
        ];
    }
}
