<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Converter;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter\ResourceType as ResourceTypeConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceTypeConverter::class)]
final class ResourceTypeTest extends TestCase
{
    protected ResourceTypeConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new ResourceTypeConverter();
    }

    #[DataProvider('fromCloudinaryDataProvider')]
    public function testFromCloudinaryData(string $type, ?string $format, string $expectedFormat): void
    {
        self::assertSame(
            $expectedFormat,
            $this->converter->fromCloudinaryData($type, $format),
        );
    }

    #[DataProvider('toCloudinaryTypeProvider')]
    public function testToCloudinaryType(string $type, string $expectedType): void
    {
        self::assertSame(
            $expectedType,
            $this->converter->toCloudinaryType($type),
        );
    }

    public function testGetAudioFormats(): void
    {
        self::assertSame(
            ['aac', 'aiff', 'amr', 'flac', 'm4a', 'mp3', 'ogg', 'opus', 'wav'],
            $this->converter->getAudioFormats(),
        );
    }

    public function testGetDocumentFormats(): void
    {
        self::assertSame(
            ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt'],
            $this->converter->getDocumentFormats(),
        );
    }

    public static function fromCloudinaryDataProvider(): array
    {
        return [
            ['image', null, RemoteResource::TYPE_IMAGE],
            ['image', 'jpg', RemoteResource::TYPE_IMAGE],
            ['image', 'pdf', RemoteResource::TYPE_DOCUMENT],
            ['video', null, RemoteResource::TYPE_VIDEO],
            ['video', 'mp4', RemoteResource::TYPE_VIDEO],
            ['video', 'mp3', RemoteResource::TYPE_AUDIO],
            ['video', 'wav', RemoteResource::TYPE_AUDIO],
            ['raw', null, RemoteResource::TYPE_OTHER],
            ['raw', 'zip', RemoteResource::TYPE_OTHER],
            ['raw', 'rar', RemoteResource::TYPE_OTHER],
            ['test', 'rar', RemoteResource::TYPE_OTHER],
            ['raw', 'doc', RemoteResource::TYPE_DOCUMENT],
            ['raw', 'docx', RemoteResource::TYPE_DOCUMENT],
            ['raw', 'ppt', RemoteResource::TYPE_DOCUMENT],
            ['raw', 'pptx', RemoteResource::TYPE_DOCUMENT],
            ['raw', 'txt', RemoteResource::TYPE_DOCUMENT],
        ];
    }

    public static function toCloudinaryTypeProvider(): array
    {
        return [
            [RemoteResource::TYPE_IMAGE, 'image'],
            [RemoteResource::TYPE_DOCUMENT, 'image|raw'],
            [RemoteResource::TYPE_VIDEO, 'video'],
            [RemoteResource::TYPE_AUDIO, 'video'],
            [RemoteResource::TYPE_OTHER, 'raw'],
        ];
    }
}
