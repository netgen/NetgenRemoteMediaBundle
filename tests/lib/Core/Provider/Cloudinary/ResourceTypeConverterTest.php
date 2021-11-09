<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\ResourceTypeConverter;
use PHPUnit\Framework\TestCase;

final class ResourceTypeConverterTest extends TestCase
{
    protected ResourceTypeConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new ResourceTypeConverter();
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\ResourceTypeConverter::fromCloudinaryData
     * @dataProvider fromCloudinaryDataProvider
     */
    public function testFromCloudinaryData(string $type, ?string $format, string $expectedFormat): void
    {
        self::assertSame(
            $expectedFormat,
            $this->converter->fromCloudinaryData($type, $format),
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\ResourceTypeConverter::toCloudinaryType
     * @dataProvider toCloudinaryTypeProvider
     */
    public function testToCloudinaryType(string $type, string $expectedType): void
    {
        self::assertSame(
            $expectedType,
            $this->converter->toCloudinaryType($type),
        );
    }

    public function fromCloudinaryDataProvider(): array
    {
        return [
            ['image', null, RemoteResource::TYPE_IMAGE],
            ['image', 'jpg', RemoteResource::TYPE_IMAGE],
            ['image', 'pdf', RemoteResource::TYPE_DOCUMENT],
            ['image', 'doc', RemoteResource::TYPE_DOCUMENT],
            ['image', 'docx', RemoteResource::TYPE_DOCUMENT],
            ['video', null, RemoteResource::TYPE_VIDEO],
            ['video', 'mp4', RemoteResource::TYPE_VIDEO],
            ['video', 'mp3', RemoteResource::TYPE_AUDIO],
            ['video', 'wav', RemoteResource::TYPE_AUDIO],
            ['raw', null, RemoteResource::TYPE_OTHER],
            ['raw', 'zip', RemoteResource::TYPE_OTHER],
            ['raw', 'rar', RemoteResource::TYPE_OTHER],
        ];
    }

    public function toCloudinaryTypeProvider(): array
    {
        return [
            [RemoteResource::TYPE_IMAGE, 'image'],
            [RemoteResource::TYPE_DOCUMENT, 'image'],
            [RemoteResource::TYPE_VIDEO, 'video'],
            [RemoteResource::TYPE_AUDIO, 'video'],
            [RemoteResource::TYPE_OTHER, 'raw'],
        ];
    }
}
