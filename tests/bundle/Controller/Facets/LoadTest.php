<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Controller\Facets;

use Netgen\Bundle\RemoteMediaBundle\Controller\Facets\Load as LoadController;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\Exception\NotSupportedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

use function json_encode;

final class LoadTest extends TestCase
{
    private LoadController $controller;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\ProviderInterface */
    private MockObject $providerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Contracts\Translation\TranslatorInterface */
    private MockObject $translatorMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->translatorMock = $this->createMock(TranslatorInterface::class);

        $this->controller = new LoadController($this->providerMock, $this->translatorMock);
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Facets\Load::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Facets\Load::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Facets\Load::resolveTypeName
     */
    public function test(): void
    {
        $folders = [
            Folder::fromPath('media'),
            Folder::fromPath('media/images'),
            Folder::fromPath('media/videos'),
            Folder::fromPath('media/raw'),
        ];

        $tags = [
            'car',
            'audio',
            'media',
        ];

        $supportedTypes = [
            'image',
            'video',
            'raw',
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        $this->providerMock
            ->expects(self::once())
            ->method('listTags')
            ->willReturn($tags);

        $this->providerMock
            ->expects(self::once())
            ->method('getSupportedTypes')
            ->willReturn($supportedTypes);

        $this->providerMock
            ->expects(self::exactly(3))
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        $this->translatorMock
            ->expects(self::exactly(3))
            ->method('trans')
            ->withConsecutive(
                ['ngrm.provider.cloudinary.supported_types.image', [], 'ngremotemedia'],
                ['ngrm.provider.cloudinary.supported_types.video', [], 'ngremotemedia'],
                ['ngrm.provider.cloudinary.supported_types.raw', [], 'ngremotemedia'],
            )
            ->willReturnOnConsecutiveCalls(
                'Images and documents',
                'Video and audio',
                'Other',
            );

        $expectedResponseContent = json_encode([
            'types' => [
                [
                    'name' => 'Images and documents',
                    'id' => 'image',
                ],
                [
                    'name' => 'Video and audio',
                    'id' => 'video',
                ],
                [
                    'name' => 'Other',
                    'id' => 'raw',
                ],
            ],
            'folders' => [
                [
                    'id' => 'media',
                    'label' => 'media',
                    'children' => null,
                ],
                [
                    'id' => 'media/images',
                    'label' => 'images',
                    'children' => null,
                ],
                [
                    'id' => 'media/videos',
                    'label' => 'videos',
                    'children' => null,
                ],
                [
                    'id' => 'media/raw',
                    'label' => 'raw',
                    'children' => null,
                ],
            ],
            'tags' => [
                [
                    'name' => 'car',
                    'id' => 'car',
                ],
                [
                    'name' => 'audio',
                    'id' => 'audio',
                ],
                [
                    'name' => 'media',
                    'id' => 'media',
                ],
            ],
        ]);

        $response = $this->controller->__invoke();

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            $expectedResponseContent,
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Facets\Load::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Facets\Load::__invoke
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Facets\Load::resolveTypeName
     */
    public function testNotSupportedFoldersAndTagsMissingTrans(): void
    {
        $supportedTypes = [
            'image',
            'video',
            'raw',
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listFolders')
            ->willThrowException(new NotSupportedException('cloudinary', 'folders'));

        $this->providerMock
            ->expects(self::once())
            ->method('listTags')
            ->willThrowException(new NotSupportedException('cloudinary', 'tags'));

        $this->providerMock
            ->expects(self::once())
            ->method('getSupportedTypes')
            ->willReturn($supportedTypes);

        $this->providerMock
            ->expects(self::exactly(3))
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        $this->translatorMock
            ->expects(self::exactly(3))
            ->method('trans')
            ->withConsecutive(
                ['ngrm.provider.cloudinary.supported_types.image', [], 'ngremotemedia'],
                ['ngrm.provider.cloudinary.supported_types.video', [], 'ngremotemedia'],
                ['ngrm.provider.cloudinary.supported_types.raw', [], 'ngremotemedia'],
            )
            ->willReturnOnConsecutiveCalls(
                'Images and documents',
                'Video and audio',
                'ngrm.provider.cloudinary.supported_types.raw',
            );

        $expectedResponseContent = json_encode([
            'types' => [
                [
                    'name' => 'Images and documents',
                    'id' => 'image',
                ],
                [
                    'name' => 'Video and audio',
                    'id' => 'video',
                ],
                [
                    'name' => 'raw',
                    'id' => 'raw',
                ],
            ],
            'folders' => [],
            'tags' => [],
        ]);

        $response = $this->controller->__invoke();

        self::assertInstanceOf(
            JsonResponse::class,
            $response,
        );

        self::assertSame(
            $expectedResponseContent,
            $response->getContent(),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );
    }
}
