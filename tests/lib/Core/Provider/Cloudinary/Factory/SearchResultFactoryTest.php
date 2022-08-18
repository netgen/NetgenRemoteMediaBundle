<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary;

use Netgen\RemoteMedia\API\RemoteResource;
use Netgen\RemoteMedia\API\SearchResult;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\SearchResult;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\SearchResultFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function count;

class SearchResultFactoryTest extends TestCase
{
    protected SearchResult $searchResultFactory;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\RemoteResource */
    protected MockObject $remoteResourceFactoryMock;

    protected function setUp(): void
    {
        $this->remoteResourceFactoryMock = self::createMock(RemoteResource::class);

        $this->searchResultFactory = new SearchResultFactory(
            $this->remoteResourceFactoryMock,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\SearchResultFactory::create
     * @dataProvider createDataProvider
     */
    public function testCreate(array $data, SearchResult $expectedResult): void
    {
        $this->remoteResourceFactoryMock
            ->expects(self::exactly(count($data['resources'] ?? [])))
            ->method('create')
            ->willReturn(new RemoteResource());

        $result = $this->searchResultFactory->create($data);

        self::assertInstanceOf(SearchResult::class, $result);

        self::assertSame(
            $expectedResult->getTotalCount(),
            $result->getTotalCount(),
        );

        self::assertSame(
            $expectedResult->getNextCursor(),
            $result->getNextCursor(),
        );

        self::containsOnlyInstancesOf(
            RemoteResource::class,
            $result->getResources(),
        );

        self::assertSame(
            count($expectedResult->getResources()),
            count($result->getResources()),
        );
    }

    public function createDataProvider(): array
    {
        return [
            [
                [
                    'total_count' => 2,
                    'next_cursor' => 'dsr943565kjosdf',
                    'resources' => [
                        [
                            'public_id' => 'c87hg9xfxrd4itiim3t0',
                            'resource_type' => 'image',
                            'type' => 'upload',
                            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                        ],
                        [
                            'public_id' => 'c87hg9xfxrd4defe3t0',
                            'resource_type' => 'video',
                            'type' => 'upload',
                            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4defe3t0.mp4',
                        ],
                    ],
                ],
                new SearchResult(
                    2,
                    'dsr943565kjosdf',
                    [
                        new RemoteResource(),
                        new RemoteResource(),
                    ],
                ),
            ],
            [
                [
                    'total_count' => 1,
                    'resources' => [
                        [
                            'public_id' => 'c87hg9xfxrd4itiim3t0',
                            'resource_type' => 'image',
                            'type' => 'upload',
                            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                        ],
                    ],
                ],
                new SearchResult(
                    1,
                    null,
                    [
                        new RemoteResource(),
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
