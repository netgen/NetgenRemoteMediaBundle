<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Factory;

use Netgen\RemoteMedia\API\Factory\RemoteResource as RemoteResourceFactory;
use Netgen\RemoteMedia\API\Factory\SearchResult as SearchResultFactoryInterface;
use Netgen\RemoteMedia\API\Search\Result as SearchResult;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\SearchResult as SearchResultFactory;
use Netgen\RemoteMedia\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

use function count;

#[CoversClass(SearchResultFactoryInterface::class)]
final class SearchResultTest extends AbstractTestCase
{
    protected SearchResultFactoryInterface $searchResultFactory;

    protected MockObject|RemoteResourceFactory $remoteResourceFactoryMock;

    protected function setUp(): void
    {
        $this->remoteResourceFactoryMock = self::createMock(RemoteResourceFactory::class);

        $this->searchResultFactory = new SearchResultFactory(
            $this->remoteResourceFactoryMock,
        );
    }

    #[DataProvider('createDataProvider')]
    public function testCreate(array $data, SearchResult $expectedResult): void
    {
        $this->remoteResourceFactoryMock
            ->expects(self::exactly(count($data['resources'] ?? [])))
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$expectedResult->getResources());

        self::assertSearchResultSame(
            $expectedResult,
            $this->searchResultFactory->create($data),
        );
    }

    public static function createDataProvider(): array
    {
        return [
            [
                [
                    'total_count' => 2,
                    'next_cursor' => 'dsr943565kjosdf',
                    'resources' => [
                        [
                            'public_id' => 'c87hg9xfxrd4itiim3t0',
                            'vesrion' => 435464523,
                            'resource_type' => 'image',
                            'type' => 'upload',
                            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                        ],
                        [
                            'public_id' => 'c87hg9xfxrd4defe3t0',
                            'resource_type' => 'video',
                            'version' => 547034322,
                            'type' => 'upload',
                            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4defe3t0.mp4',
                        ],
                    ],
                ],
                new SearchResult(
                    2,
                    'dsr943565kjosdf',
                    [
                        new RemoteResource(
                            remoteId: 'upload|image|c87hg9xfxrd4itiim3t0',
                            type: 'image',
                            url: 'https://res.cloudinary.com/demo/image/upload/c87hg9xfxrd4itiim3t0.jpg',
                            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                            name: 'c87hg9xfxrd4itiim3t0',
                            version: '435464523',
                        ),
                        new RemoteResource(
                            remoteId: 'upload|video|c87hg9xfxrd4defe3t0',
                            type: 'video',
                            url: 'https://res.cloudinary.com/demo/image/upload/c87hg9xfxrd4defe3t0.mp4',
                            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                            name: 'c87hg9xfxrd4defe3t0',
                            version: '547034322',
                        ),
                    ],
                ),
            ],
            [
                [
                    'total_count' => 1,
                    'resources' => [
                        [
                            'public_id' => 'media/image/c87hg9xfxrd4itiim3t0',
                            'version' => 465788331,
                            'resource_type' => 'image',
                            'type' => 'upload',
                            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/media/image/c87hg9xfxrd4itiim3t0.jpg',
                        ],
                    ],
                ],
                new SearchResult(
                    1,
                    null,
                    [
                        new RemoteResource(
                            remoteId: 'upload|image|c87hg9xfxrd4itiim3t0',
                            type: 'image',
                            url: 'https://res.cloudinary.com/demo/image/upload/media/image/c87hg9xfxrd4itiim3t0.jpg',
                            md5: 'e522f43cf89aa0afd03387c37e2b6e29',
                            name: 'c87hg9xfxrd4itiim3t0',
                            version: '465788331',
                            folder: Folder::fromPath('media/image'),
                        ),
                    ],
                ),
            ],
            [
                [
                    'total_count' => 0,
                    'next_cursor' => null,
                    'resources' => [],
                ],
                new SearchResult(
                    0,
                    null,
                    [],
                ),
            ],
            [
                [],
                new SearchResult(
                    0,
                    null,
                    [],
                ),
            ],
        ];
    }
}
