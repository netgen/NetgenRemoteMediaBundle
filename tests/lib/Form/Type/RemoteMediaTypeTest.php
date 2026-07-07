<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\Type;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Form\Type\RemoteMediaType;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(RemoteMediaType::class)]
class RemoteMediaTypeTest extends TypeTestCase
{
    private DataTransformerInterface|MockObject $dataTransformerMock;

    private MockObject|ProviderInterface $providerMock;

    protected function setUp(): void
    {
        $this->dataTransformerMock = $this->createMock(DataTransformerInterface::class);
        $this->providerMock = $this->createMock(ProviderInterface::class);

        parent::setUp();
    }

    #[DataProvider('submitDataProvider')]
    public function testSubmitValidData(
        array $formData,
        array $options,
        array $transformerData,
        RemoteResourceLocation $expectedLocation,
        array $viewData,
        array $viewOptions
    ): void {
        $location = new RemoteResourceLocation(
            new RemoteResource(
                remoteId: 'upload|image|media/images/example.jpg',
                type: 'image',
                url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                name: 'example.jpg',
                folder: Folder::fromPath('media/images'),
            ),
        );

        $form = $this->factory->create(RemoteMediaType::class, $location, $options);

        $this->dataTransformerMock
            ->expects(self::once())
            ->method('reverseTransform')
            ->with($transformerData)
            ->willReturn($expectedLocation);

        $this->providerMock
            ->expects(self::once())
            ->method('getSupportedVisibilities')
            ->willReturn(RemoteResource::SUPPORTED_VISIBILITIES);

        $this->providerMock
            ->expects(self::once())
            ->method('getSupportedTypes')
            ->willReturn(RemoteResource::SUPPORTED_TYPES);

        $this->providerMock
            ->expects(self::once())
            ->method('listTags')
            ->willReturn(['tag1', 'tag3']);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());

        AbstractTestCase::assertRemoteResourceLocationSame(
            $expectedLocation,
            $form->getData(),
        );

        $view = $form->createView();

        self::assertSame(
            $viewData,
            $view->vars['value'],
        );

        self::assertSame(
            'remote_media',
            $view->vars['id'],
        );

        self::assertSame(
            'remote_media',
            $view->vars['name'],
        );

        self::assertSame(
            'remote_media',
            $view->vars['full_name'],
        );

        foreach ($viewOptions as $key => $value) {
            self::assertSame(
                $value,
                $view->vars[$key],
            );
        }
    }

    public function testCollectionShapedSubmissionCollapsesToFirstEntry(): void
    {
        $expectedLocation = new RemoteResourceLocation(
            new RemoteResource(
                remoteId: 'remote-1',
                type: 'image',
                url: 'https://example.test/1',
                md5: 'md5-1',
            ),
        );

        $this->dataTransformerMock
            ->expects(self::once())
            ->method('reverseTransform')
            ->with(self::callback(static fn (array $data): bool => $data['remoteId'] === 'remote-1'
                    && $data['altText'] === 'first alt'))
            ->willReturn($expectedLocation);

        $form = $this->factory->create(RemoteMediaType::class);

        $form->submit([
            'source' => 'form_gallery',
            [
                'remoteId' => 'remote-1',
                'type' => 'image',
                'altText' => 'first alt',
            ],
            [
                'remoteId' => 'remote-2',
                'type' => 'image',
                'altText' => 'second alt',
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame($expectedLocation, $form->getData());
    }

    public function testSubmitForeignLocationIdCreatesFreshLocation(): void
    {
        $existingLocation = new RemoteResourceLocation(
            new RemoteResource('upload|image|owned.jpg', RemoteResource::TYPE_IMAGE, 'https://example.test/owned.jpg', 'md5-owned'),
            id: 5,
        );
        $resultLocation = new RemoteResourceLocation(
            new RemoteResource('upload|image|foreign.jpg', RemoteResource::TYPE_IMAGE, 'https://example.test/foreign.jpg', 'md5-foreign'),
        );

        $this->dataTransformerMock
            ->expects(self::once())
            ->method('reverseTransform')
            ->with(self::callback(static fn (array $data): bool => $data['locationId'] === null
                && $data['remoteId'] === 'upload|image|foreign.jpg'))
            ->willReturn($resultLocation);

        $form = $this->factory->create(RemoteMediaType::class, $existingLocation);

        $form->submit([
            'locationId' => '999',
            'remoteId' => 'upload|image|foreign.jpg',
            'type' => 'image',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame($resultLocation, $form->getData());
    }

    public function testSubmitOwnLocationIdIsPreserved(): void
    {
        $existingLocation = new RemoteResourceLocation(
            new RemoteResource('upload|image|owned.jpg', RemoteResource::TYPE_IMAGE, 'https://example.test/owned.jpg', 'md5-owned'),
            id: 5,
        );

        $this->dataTransformerMock
            ->expects(self::once())
            ->method('reverseTransform')
            ->with(self::callback(static fn (array $data): bool => $data['locationId'] === '5'
                && $data['remoteId'] === 'upload|image|owned.jpg'))
            ->willReturn($existingLocation);

        $form = $this->factory->create(RemoteMediaType::class, $existingLocation);

        $form->submit([
            'locationId' => '5',
            'remoteId' => 'upload|image|owned.jpg',
            'type' => 'image',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame($existingLocation, $form->getData());
    }

    public function testBuildViewExposesSingleLocationAndCollectionFlags(): void
    {
        $location = new RemoteResourceLocation(
            new RemoteResource('remote-1', RemoteResource::TYPE_IMAGE, 'https://example.test/1', 'md5-1'),
        );

        $this->providerMock
            ->method('getSupportedVisibilities')
            ->willReturn(RemoteResource::SUPPORTED_VISIBILITIES);

        $this->providerMock
            ->method('getSupportedTypes')
            ->willReturn(RemoteResource::SUPPORTED_TYPES);

        $this->providerMock
            ->method('listTags')
            ->willReturn([]);

        $form = $this->factory->create(RemoteMediaType::class, $location);
        $view = $form->createView();

        self::assertSame([$location], $view->vars['remote_media_locations']);
        self::assertSame(1, $view->vars['upload_limit']);
        self::assertFalse($view->vars['is_collection']);
        self::assertArrayNotHasKey('uploadLimit', $view->children);
        self::assertArrayNotHasKey('collectionPayload', $view->children);
    }

    public static function submitDataProvider(): iterable
    {
        return [
            [
                [
                    'locationId' => '',
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => 'Test alt text',
                    'caption' => 'Test caption',
                    'tags' => ['example', 'image', 'test'],
                    'cropSettings' => '{"hero_image": {"x": 10, "y": 20, "w": 1920, "h": 1080}}',
                    'source' => null,
                    'watermarkText' => 'This is a watermark',
                ],
                [],
                [
                    'locationId' => '',
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => 'Test alt text',
                    'caption' => 'Test caption',
                    'tags' => ['example', 'image', 'test'],
                    'cropSettings' => '{"hero_image": {"x": 10, "y": 20, "w": 1920, "h": 1080}}',
                    'source' => null,
                    'watermarkText' => 'This is a watermark',
                ],
                new RemoteResourceLocation(
                    remoteResource: new RemoteResource(
                        remoteId: 'upload|image|media/images/example.jpg',
                        type: 'image',
                        url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                        md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                        name: 'example.jpg',
                        folder: Folder::fromPath('media/images'),
                    ),
                    cropSettings: [
                        new CropSettings('hero_image', 10, 20, 1920, 1080),
                    ],
                    watermarkText: 'This is a watermark',
                ),
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => 'Test alt text',
                    'caption' => 'Test caption',
                    'watermarkText' => 'This is a watermark',
                    'tags' => ['example', 'image', 'test'],
                    'cropSettings' => '{"hero_image": {"x": 10, "y": 20, "w": 1920, "h": 1080}}',
                    'source' => null,
                ],
                [
                    'variation_group' => 'default',
                    'allowed_visibilities' => [],
                    'allowed_types' => [],
                    'allowed_tags' => [],
                    'parent_folder' => null,
                    'folder' => null,
                    'upload_context' => [],
                    'upload_limit' => 1,
                    'is_collection' => false,
                ],
            ],
            [
                [
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'source' => 'Test source',
                ],
                [
                    'variation_group' => null,
                    'allowed_visibilities' => ['public', 'other'],
                    'allowed_types' => ['image', 'video', 'pdf'],
                    'allowed_tags' => ['tag1', 'tag2'],
                    'parent_folder' => 'media/test',
                    'folder' => 'media',
                    'upload_context' => ['test'],
                ],
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => null,
                    'caption' => null,
                    'tags' => [],
                    'cropSettings' => null,
                    'source' => 'Test source',
                    'watermarkText' => null,
                ],
                new RemoteResourceLocation(
                    new RemoteResource(
                        remoteId: 'upload|image|media/images/example.jpg',
                        type: 'image',
                        url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                        md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                        name: 'example.jpg',
                    ),
                    'Test source',
                ),
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => null,
                    'caption' => null,
                    'watermarkText' => null,
                    'tags' => [],
                    'cropSettings' => null,
                    'source' => 'Test source',
                ],
                [
                    'variation_group' => 'default',
                    'allowed_visibilities' => ['public'],
                    'allowed_types' => ['image', 'video'],
                    'allowed_tags' => ['tag1'],
                    'parent_folder' => [
                        'id' => 'media/test',
                        'label' => 'test',
                    ],
                    'folder' => [
                        'id' => 'media',
                        'label' => 'media',
                    ],
                    'upload_context' => [],
                    'upload_limit' => 1,
                    'is_collection' => false,
                ],
            ],
            [
                [
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'source' => 'Test source',
                ],
                [
                    'variation_group' => 'product_image',
                    'allowed_visibilities' => ['protected'],
                    'allowed_types' => ['other'],
                    'parent_folder' => Folder::fromPath('media/test'),
                    'folder' => Folder::fromPath('media'),
                    'upload_context' => [
                        'alt' => 'Some alt',
                        'caption' => 'Some caption',
                        'type' => 'product_image',
                    ],
                ],
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => null,
                    'caption' => null,
                    'tags' => [],
                    'cropSettings' => null,
                    'source' => 'Test source',
                    'watermarkText' => null,
                ],
                new RemoteResourceLocation(
                    new RemoteResource(
                        remoteId: 'upload|image|media/images/example.jpg',
                        type: 'image',
                        url: 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                        md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                        name: 'example.jpg',
                    ),
                    'Test source',
                ),
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => null,
                    'caption' => null,
                    'watermarkText' => null,
                    'tags' => [],
                    'cropSettings' => null,
                    'source' => 'Test source',
                ],
                [
                    'variation_group' => 'product_image',
                    'allowed_visibilities' => ['protected'],
                    'allowed_types' => ['other'],
                    'allowed_tags' => [],
                    'parent_folder' => [
                        'id' => 'media/test',
                        'label' => 'test',
                    ],
                    'folder' => [
                        'id' => 'media',
                        'label' => 'media',
                    ],
                    'upload_context' => [
                        'alt' => 'Some alt',
                        'caption' => 'Some caption',
                        'type' => 'product_image',
                    ],
                    'upload_limit' => 1,
                    'is_collection' => false,
                ],
            ],
        ];
    }

    protected function getExtensions(): array
    {
        $type = new RemoteMediaType($this->dataTransformerMock, $this->providerMock);

        return [
            new PreloadedExtension([$type], []),
        ];
    }
}
