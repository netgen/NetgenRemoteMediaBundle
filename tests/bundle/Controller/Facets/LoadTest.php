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
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Facets\Load::resolveVisibilityName
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
            'audio',
            'document',
            'other',
        ];

        $supportedVisibilities = [
            'public',
            'private',
            'protected',
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
            ->expects(self::once())
            ->method('getSupportedVisibilities')
            ->willReturn($supportedVisibilities);

        $this->translatorMock
            ->expects(self::exactly(8))
            ->method('trans')
            ->withConsecutive(
                ['ngrm.supported_types.image', [], 'ngremotemedia'],
                ['ngrm.supported_types.video', [], 'ngremotemedia'],
                ['ngrm.supported_types.audio', [], 'ngremotemedia'],
                ['ngrm.supported_types.document', [], 'ngremotemedia'],
                ['ngrm.supported_types.other', [], 'ngremotemedia'],
                ['ngrm.supported_visibilities.public', [], 'ngremotemedia'],
                ['ngrm.supported_visibilities.private', [], 'ngremotemedia'],
                ['ngrm.supported_visibilities.protected', [], 'ngremotemedia'],
            )
            ->willReturnOnConsecutiveCalls(
                'Image',
                'Video',
                'Audio',
                'Document',
                'Other',
                'Public',
                'Private',
                'Protected',
            );

        $expectedResponseContent = json_encode([
            'types' => [
                [
                    'name' => 'Image',
                    'id' => 'image',
                ],
                [
                    'name' => 'Video',
                    'id' => 'video',
                ],
                [
                    'name' => 'Audio',
                    'id' => 'audio',
                ],
                [
                    'name' => 'Document',
                    'id' => 'document',
                ],
                [
                    'name' => 'Other',
                    'id' => 'other',
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
            'visibilities' => [
                [
                    'name' => 'Public',
                    'id' => 'public',
                ],
                [
                    'name' => 'Private',
                    'id' => 'private',
                ],
                [
                    'name' => 'Protected',
                    'id' => 'protected',
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
     * @covers \Netgen\Bundle\RemoteMediaBundle\Controller\Facets\Load::resolveVisibilityName
     */
    public function testNotSupportedFoldersAndTagsMissingTrans(): void
    {
        $supportedTypes = [
            'image',
            'video',
            'audio',
            'document',
            'other',
        ];

        $supportedVisibilities = [
            'public',
            'private',
            'protected',
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
            ->expects(self::once())
            ->method('getSupportedVisibilities')
            ->willReturn($supportedVisibilities);

        $this->translatorMock
            ->expects(self::exactly(8))
            ->method('trans')
            ->withConsecutive(
                ['ngrm.supported_types.image', [], 'ngremotemedia'],
                ['ngrm.supported_types.video', [], 'ngremotemedia'],
                ['ngrm.supported_types.audio', [], 'ngremotemedia'],
                ['ngrm.supported_types.document', [], 'ngremotemedia'],
                ['ngrm.supported_types.other', [], 'ngremotemedia'],
                ['ngrm.supported_visibilities.public', [], 'ngremotemedia'],
                ['ngrm.supported_visibilities.private', [], 'ngremotemedia'],
                ['ngrm.supported_visibilities.protected', [], 'ngremotemedia'],
            )
            ->willReturnOnConsecutiveCalls(
                'Image',
                'Video',
                'Audio',
                'Document',
                'Other',
                'Public',
                'Private',
                'Protected',
            );

        $expectedResponseContent = json_encode([
            'types' => [
                [
                    'name' => 'Image',
                    'id' => 'image',
                ],
                [
                    'name' => 'Video',
                    'id' => 'video',
                ],
                [
                    'name' => 'Audio',
                    'id' => 'audio',
                ],
                [
                    'name' => 'Document',
                    'id' => 'document',
                ],
                [
                    'name' => 'Other',
                    'id' => 'other',
                ],
            ],
            'folders' => [],
            'tags' => [],
            'visibilities' => [
                [
                    'name' => 'Public',
                    'id' => 'public',
                ],
                [
                    'name' => 'Private',
                    'id' => 'private',
                ],
                [
                    'name' => 'Protected',
                    'id' => 'protected',
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
}
