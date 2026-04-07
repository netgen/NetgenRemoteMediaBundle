<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller;

use Netgen\Bundle\RemoteMediaBundle\Controller\Configuration as ConfigurationController;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function json_decode;

#[CoversClass(ConfigurationController::class)]
final class ConfigurationTest extends TestCase
{
    private MockObject|RouterInterface $routerMock;
    private MockObject|TranslatorInterface $translatorMock;
    private MockObject|VariationResolver $variationResolverMock;

    protected function setUp(): void
    {
        $this->routerMock = $this->createMock(RouterInterface::class);
        $this->translatorMock = $this->createMock(TranslatorInterface::class);
        $this->variationResolverMock = new VariationResolver(
            new Registry(),
            null,
            [],
        );
    }

    /**
     * @dataProvider folderScopedUploadsDataProvider
     */
    public function testFolderScopedUploads(string $folderMode, bool $appendFolderPath, bool $expected): void
    {
        $controller = new ConfigurationController(
            $this->routerMock,
            $this->translatorMock,
            $this->variationResolverMock,
            $folderMode,
            $appendFolderPath,
        );

        $response = $controller->__invoke(new Request());
        $data = json_decode((string) $response->getContent(), true);

        self::assertSame($expected, $data['folderScopedUploads']);
    }

    public static function folderScopedUploadsDataProvider(): iterable
    {
        return [
            [CloudinaryProvider::FOLDER_MODE_FIXED, false, true],
            [CloudinaryProvider::FOLDER_MODE_DYNAMIC, false, false],
            [CloudinaryProvider::FOLDER_MODE_DYNAMIC, true, true],
            [CloudinaryProvider::FOLDER_MODE_FIXED, true, true],
        ];
    }

    public function testTranslationsExist(): void
    {
        $controller = new ConfigurationController(
            $this->routerMock,
            $this->translatorMock,
            $this->variationResolverMock,
            CloudinaryProvider::FOLDER_MODE_FIXED,
            false,
        );

        $this->translatorMock->method('trans')->willReturn('translated string');

        $response = $controller->__invoke(new Request());
        $data = json_decode((string) $response->getContent(), true);

        self::assertArrayHasKey('upload_error_existing_resource', $data['translations']);
        self::assertArrayHasKey('upload_error_existing_resource_in_folder', $data['translations']);
    }
}
