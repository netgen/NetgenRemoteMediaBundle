<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\Type;

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

    protected function setUp(): void
    {
        $this->dataTransformerMock = $this->createMock(DataTransformerInterface::class);

        parent::setUp();
    }

    /**
     * @covers \Netgen\RemoteMedia\Form\Type\RemoteMediaType::__construct
     * @covers \Netgen\RemoteMedia\Form\Type\RemoteMediaType::buildForm
     * @covers \Netgen\RemoteMedia\Form\Type\RemoteMediaType::configureOptions
     * @dataProvider submitDataProvider
     */
    public function testSubmitValidData(
        array $formData,
        array $transformerData,
        RemoteResourceLocation $expectedLocation,
        array $viewData
    ): void {
        $location = new RemoteResourceLocation(new RemoteResource());

        $form = $this->factory->create(RemoteMediaType::class, $location);

        $this->dataTransformerMock
            ->expects(self::once())
            ->method('reverseTransform')
            ->with($transformerData)
            ->willReturn($expectedLocation);

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
                    'tags' => 'example,image, test',
                    'cropSettings' => '{"hero_image": {"x": 10, "y": 20, "width": 1920, "height": 1080}}',
                ],
                [
                    'locationId' => '',
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => 'Test alt text',
                    'caption' => 'Test caption',
                    'tags' => 'example,image, test',
                    'cropSettings' => '{"hero_image": {"x": 10, "y": 20, "width": 1920, "height": 1080}}',
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
                    'tags' => 'example,image, test',
                    'cropSettings' => '{"hero_image": {"x": 10, "y": 20, "width": 1920, "height": 1080}}',
                ],
            ],
            [
                [
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                ],
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => null,
                    'caption' => null,
                    'tags' => null,
                    'cropSettings' => null,
                ],
                new RemoteResourceLocation(
                    new RemoteResource([
                        'remoteId' => 'upload|image|media/images/example.jpg',
                        'type' => 'image',
                        'url' => 'https://cloudinary.com/test/upload/image/media/images/example.jpg',
                        'name' => 'example.jpg',
                        'md5' => 'e522f43cf89aa0afd03387c37e2b6e29',
                    ]),
                ),
                [
                    'locationId' => null,
                    'remoteId' => 'upload|image|media/images/example.jpg',
                    'type' => 'image',
                    'altText' => null,
                    'caption' => null,
                    'tags' => null,
                    'cropSettings' => null,
                ],
            ],
        ];
    }

    protected function getExtensions(): array
    {
        $type = new RemoteMediaType($this->dataTransformerMock);

        return [
            new PreloadedExtension([$type], []),
        ];
    }
}
