<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller;

use Netgen\Bundle\RemoteMediaBundle\Controller\Configuration as ConfigurationController;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function json_decode;

#[CoversClass(ConfigurationController::class)]
final class ConfigurationTest extends TestCase
{
    #[DataProvider('folderScopedUploadsDataProvider')]
    public function testFolderScopedUploads(string $folderMode, bool $useAssetFolderAsPublicIdPrefix, bool $expected): void
    {
        $data = $this->invoke($folderMode, $useAssetFolderAsPublicIdPrefix);

        self::assertSame($expected, $data['folderScopedUploads']);
    }

    public static function folderScopedUploadsDataProvider(): iterable
    {
        return [
            [CloudinaryProvider::FOLDER_MODE_FIXED, false, true],
            [CloudinaryProvider::FOLDER_MODE_FIXED, true, true],
            [CloudinaryProvider::FOLDER_MODE_DYNAMIC, false, false],
            [CloudinaryProvider::FOLDER_MODE_DYNAMIC, true, true],
        ];
    }

    public function testExistingResourceTranslations(): void
    {
        $data = $this->invoke(CloudinaryProvider::FOLDER_MODE_FIXED, false);

        self::assertArrayHasKey('upload_error_existing_resource', $data['translations']);
        self::assertArrayHasKey('upload_error_existing_resource_in_folder', $data['translations']);
    }

    private function invoke(string $folderMode, bool $useAssetFolderAsPublicIdPrefix): array
    {
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $translatorMock->method('trans')->willReturnArgument(0);

        $controller = new ConfigurationController(
            $this->createMock(RouterInterface::class),
            $translatorMock,
            new VariationResolver(new Registry(), null, []),
            $folderMode,
            $useAssetFolderAsPublicIdPrefix,
        );

        return json_decode((string) $controller(new Request())->getContent(), true);
    }
}
