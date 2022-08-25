<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Factory;

use Netgen\RemoteMedia\API\Factory\RemoteResource as RemoteResourceFactory;
use Netgen\RemoteMedia\API\Factory\SearchResult as SearchResultFactoryInterface;
use Netgen\RemoteMedia\API\Search\Result as SearchResult;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\SearchResult as SearchResultFactory;
use Netgen\RemoteMedia\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;

use function count;

final class SearchResultTest extends AbstractTest
{
    protected SearchResultFactoryInterface $searchResultFactory;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\Factory\RemoteResource */
    protected MockObject $remoteResourceFactoryMock;

    protected function setUp(): void
    {
        $this->remoteResourceFactoryMock = self::createMock(RemoteResourceFactory::class);

        $this->searchResultFactory = new SearchResultFactory(
            $this->remoteResourceFactoryMock,
        );
    }

    /**
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\SearchResult::__construct
     * @covers \Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory\SearchResult::create
     * @dataProvider createDataProvider
     */
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
                        new RemoteResource([
                            'remoteId' => 'upload|image|c87hg9xfxrd4itiim3t0',
                            'type' => 'image',
                            'url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                        ]),
                        new RemoteResource([
                            'remoteId' => 'upload|video|c87hg9xfxrd4defe3t0',
                            'type' => 'video',
                            'url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4defe3t0.mp4',
                        ]),
                    ],
                ),
            ],
            [
                [
                    'total_count' => 1,
                    'resources' => [
                        [
                            'public_id' => 'upload|image|c87hg9xfxrd4itiim3t0',
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
                        new RemoteResource([
                            'remoteId' => 'upload|image|c87hg9xfxrd4itiim3t0',
                            'type' => 'image',
                            'url' => 'https://res.cloudinary.com/demo/image/upload/v1371995958/c87hg9xfxrd4itiim3t0.jpg',
                        ]),
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
