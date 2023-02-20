<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\Type;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Form\Type\RemoteMediaType;
use Netgen\RemoteMedia\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class RemoteMediaTypeTest extends TypeTestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Form\DataTransformerInterface */
    private MockObject $dataTransformerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\ProviderInterface */
    private MockObject $providerMock;

    protected function setUp(): void
    {
        $this->dataTransformerMock = $this->createMock(DataTransformerInterface::class);
        $this->providerMock = $this->createMock(ProviderInterface::class);

        parent::setUp();
    }

    /**
     * @covers \Netgen\RemoteMedia\Form\Type\RemoteMediaType::__construct
     * @covers \Netgen\RemoteMedia\Form\Type\RemoteMediaType::buildForm
     * @covers \Netgen\RemoteMedia\Form\Type\RemoteMediaType::buildView
     * @covers \Netgen\RemoteMedia\Form\Type\RemoteMediaType::configureOptions
     *
     * @dataProvider submitDataProvider
     */
    public function testSubmitValidData(
        array $formData,
        array $options,
        array $transformerData,
        RemoteResourceLocation $expectedLocation,
        array $viewData,
        array $viewOptions
    ): void {
        $location = new RemoteResourceLocation(new RemoteResource());

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

        AbstractTest::assertRemoteResourceLocationSame(
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

    public function submitDataProvider(): array
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
                ],
                new RemoteResourceLocation(
                    new RemoteResource([
                        'remoteId' => 'upload|image|media/images/example.jpg',
                        'type' => 'image',
                        'url' => 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                        'name' => 'example.jpg',
                        'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
                        'folder' => Folder::fromPath('media/images'),
                    ]),
                    null,
                    [
                        new CropSettings('hero_image', 10, 20, 1920, 1080),
                    ],
                ),
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => 'Test alt text',
                    'caption' => 'Test caption',
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
                    'allowed_visibilities' => ['public', 'private', 'other'],
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
                ],
                new RemoteResourceLocation(
                    new RemoteResource([
                        'remoteId' => 'upload|image|media/images/example.jpg',
                        'type' => 'image',
                        'url' => 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                        'name' => 'example.jpg',
                        'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
                    ]),
                    'Test source',
                ),
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => null,
                    'caption' => null,
                    'tags' => [],
                    'cropSettings' => null,
                    'source' => 'Test source',
                ],
                [
                    'variation_group' => 'default',
                    'allowed_visibilities' => ['public', 'private'],
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
                ],
                new RemoteResourceLocation(
                    new RemoteResource([
                        'remoteId' => 'upload|image|media/images/example.jpg',
                        'type' => 'image',
                        'url' => 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                        'name' => 'example.jpg',
                        'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
                    ]),
                    'Test source',
                ),
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => null,
                    'caption' => null,
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
