<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Form\Type;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\Form\Type\RemoteMediaFolderType;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(RemoteMediaFolderType::class)]
class RemoteMediaFolderTypeTest extends TypeTestCase
{
    private MockObject|DataTransformerInterface $dataTransformerMock;

    protected function setUp(): void
    {
        $this->dataTransformerMock = $this->createMock(DataTransformerInterface::class);

        parent::setUp();
    }

    #[DataProvider('submitDataProvider')]
    public function testSubmitValidData(
        array $formData,
        array $options,
        array $transformerOptions,
        ?Folder $expectedFolder,
        array $viewData,
        array $viewOptions
    ): void {
        $form = $this->factory->create(RemoteMediaFolderType::class, Folder::fromPath('media'), $options);

        $this->dataTransformerMock
            ->expects(self::once())
            ->method('reverseTransform')
            ->with($transformerOptions)
            ->willReturn($expectedFolder);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());

        if ($expectedFolder instanceof Folder) {
            AbstractTestCase::assertFolderSame(
                $expectedFolder,
                $form->getData(),
            );
        }

        if (!$expectedFolder instanceof Folder) {
            self::assertNull($form->getData());
        }

        $view = $form->createView();

        self::assertSame(
            $viewData,
            $view->vars['value'],
        );

        self::assertSame(
            'remote_media_folder',
            $view->vars['id'],
        );

        self::assertSame(
            'remote_media_folder',
            $view->vars['name'],
        );

        self::assertSame(
            'remote_media_folder',
            $view->vars['full_name'],
        );

        foreach ($viewOptions as $key => $value) {
            self::assertSame(
                $value,
                $view->vars[$key],
            );
        }
    }

    public static function submitDataProvider(): array
    {
        return [
            [
                [
                    'folder' => 'media/images',
                ],
                [],
                [
                    'folder' => 'media/images',
                ],
                Folder::fromPath('media/images'),
                [
                    'folder' => 'media/images',
                ],
                [
                    'parent_folder' => null,
                ],
            ],
            [
                [
                    'folder' => 'media/images',
                ],
                [
                    'parent_folder' => Folder::fromPath('media'),
                ],
                [
                    'folder' => 'media/images',
                ],
                Folder::fromPath('media/images'),
                [
                    'folder' => 'media/images',
                ],
                [
                    'parent_folder' => ['id' => 'media', 'label' => 'media'],
                ],
            ],
            [
                [
                    'folder' => 'media/images/new',
                ],
                [
                    'parent_folder' => Folder::fromPath('media/images'),
                ],
                [
                    'folder' => 'media/images/new',
                ],
                Folder::fromPath('media/images/new'),
                [
                    'folder' => 'media/images/new',
                ],
                [
                    'parent_folder' => ['id' => 'media/images', 'label' => 'images'],
                ],
            ],
            [
                [
                    'folder' => null,
                ],
                [
                    'parent_folder' => Folder::fromPath('media/images'),
                ],
                [
                    'folder' => null,
                ],
                null,
                [
                    'folder' => null,
                ],
                [
                    'parent_folder' => ['id' => 'media/images', 'label' => 'images'],
                ],
            ],
            [
                [
                    'test' => 'something',
                ],
                [
                    'parent_folder' => Folder::fromPath('media/images'),
                ],
                [
                    'folder' => null,
                ],
                null,
                [
                    'folder' => null,
                ],
                [
                    'parent_folder' => ['id' => 'media/images', 'label' => 'images'],
                ],
            ],
            [
                [],
                [],
                [
                    'folder' => null,
                ],
                null,
                [
                    'folder' => null,
                ],
                [
                    'parent_folder' => null,
                ],
            ],
        ];
    }

    protected function getExtensions(): array
    {
        $type = new RemoteMediaFolderType($this->dataTransformerMock);

        return [
            new PreloadedExtension([$type], []),
        ];
    }
}
