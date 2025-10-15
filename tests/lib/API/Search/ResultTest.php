<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\API\Search;

use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Result::class)]
final class ResultTest extends TestCase
{
    #[DataProvider('dataProvider')]
    public function testConstructor(
        int $totalCount,
        ?string $nextCursor,
        array $resources
    ): void {
        $result = new Result($totalCount, $nextCursor, $resources);

        self::assertSame(
            $totalCount,
            $result->getTotalCount(),
        );

        self::assertSame(
            $nextCursor,
            $result->getNextCursor(),
        );

        self::assertSame(
            $resources,
            $result->getResources(),
        );
    }

    public static function dataProvider(): array
    {
        return [
            [0, null, []],
            [
                5,
                null,
                [
                    new RemoteResource(
                        remoteId: 'test_image1.jpg',
                        type: 'image',
                        url: 'https://cloudinary.com/upload/image/test_image1.jpg',
                        md5: 'e522f43cf89aa0afd03387c37e2b6e12',
                    ),
                    new RemoteResource(
                        remoteId: 'test_image2.jpg',
                        type: 'image',
                        url: 'https://cloudinary.com/upload/image/test_image2.jpg',
                        md5: '943ngjfd323224ns3g44233n2e',
                    ),
                    new RemoteResource(
                        remoteId: 'test_video.mp4',
                        type: 'video',
                        url: 'https://cloudinary.com/upload/image/test_video.mp4',
                        md5: '89348r498g459t09i23nfj942fj3f024',
                    ),
                    new RemoteResource(
                        remoteId: 'test_video2.mp4',
                        type: 'video',
                        url: 'https://cloudinary.com/upload/image/test_video2.mp4',
                        md5: 'n49jf0934nedsoewf43f943094522wds',
                    ),
                    new RemoteResource(
                        remoteId: 'test_file.csv',
                        type: 'other',
                        url: 'https://cloudinary.com/upload/image/test_file.csv',
                        md5: '93jfjf0843r0439fjrej9rf4332frrq33',
                    ),
                ],
            ],
            [
                500,
                'sdjf90r9okjdspfo93h2',
                [
                    new RemoteResource(
                        remoteId: 'test_image2.jpg',
                        type: 'image',
                        url: 'https://cloudinary.com/upload/image/test_image2.jpg',
                        md5: '943ngjfd323224ns3g44233n2e',
                    ),
                    new RemoteResource(
                        remoteId: 'test_video.mp4',
                        type: 'video',
                        url: 'https://cloudinary.com/upload/image/test_video.mp4',
                        md5: '89348r498g459t09i23nfj942fj3f024',
                    ),
                    new RemoteResource(
                        remoteId: 'test_file.csv',
                        type: 'other',
                        url: 'https://cloudinary.com/upload/image/test_file.csv',
                        md5: '93jfjf0843r0439fjrej9rf4332frrq33',
                    ),
                    new RemoteResource(
                        remoteId: 'test_image1.jpg',
                        type: 'image',
                        url: 'https://cloudinary.com/upload/image/test_image1.jpg',
                        md5: 'e522f43cf89aa0afd03387c37e2b6e12',
                    ),
                    new RemoteResource(
                        remoteId: 'test_video.mp4',
                        type: 'video',
                        url: 'https://cloudinary.com/upload/image/test_video.mp4',
                        md5: '89348r498g459t09i23nfj942fj3f024',
                    ),
                ],
            ],
        ];
    }
}
