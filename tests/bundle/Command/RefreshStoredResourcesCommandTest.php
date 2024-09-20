<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Netgen\Bundle\RemoteMediaBundle\Command\RefreshStoredResourcesCommand;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

use function array_map;
use function array_slice;
use function trim;

use const PHP_EOL;

#[CoversClass(RefreshStoredResourcesCommand::class)]
final class RefreshStoredResourcesCommandTest extends TestCase
{
    private MockObject|ProviderInterface $providerMock;

    private EntityRepository|MockObject $repositoryMock;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->repositoryMock = $this->createMock(EntityRepository::class);

        $entityManagerMock
            ->expects(self::once())
            ->method('getRepository')
            ->with(RemoteResource::class)
            ->willReturn($this->repositoryMock);

        $application = new Application();
        $application->add(new RefreshStoredResourcesCommand($entityManagerMock, $this->providerMock));

        $command = $application->find('netgen:remote_media:refresh');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $resources = [
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t0',
                type: 'raw',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t0.jpg',
                md5: 'o03jp5093hn3850ds07l35j6043s823h',
                id: 56,
                name: 'a87hg9xfxrd4itiim3t0',
            ),
        ];

        $this->repositoryMock
            ->expects(self::once())
            ->method('count')
            ->with([])
            ->willReturn(1);

        $this->repositoryMock
            ->expects(self::exactly(2))
            ->method('findBy')
            ->willReturnCallback(
                static fn (
                    array $criteria,
                    ?array $orderBy = null,
                    $limit = null,
                    $offset = null
                ) => match ([$criteria, $orderBy, $limit, $offset]) {
                    [[], null, 500, 0] => $resources,
                    [[], null, 500, 500] => [],
                },
            );

        $query = Query::fromRemoteIds(
            array_map(static fn (RemoteResource $resource): string => $resource->getRemoteId(), $resources),
            500,
        );

        $searchResult = new Result(1, null, $resources);

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->providerMock
            ->expects(self::once())
            ->method('store')
            ->with($resources[0])
            ->willReturn($resources[0]);

        $this->commandTester->execute([]);

        self::assertSame(
            '0/1 [>---------------------------]   0%'
            . PHP_EOL . ' 1/1 [============================] 100%',
            trim($this->commandTester->getDisplay()),
        );
    }

    public function testExecuteWithoutDelete(): void
    {
        $resources = [
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t0',
                type: 'raw',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t0.jpg',
                md5: 'o03jp5093hn3850ds07l35j6043s823h',
                id: 56,
                name: 'a87hg9xfxrd4itiim3t0',
            ),
        ];

        $this->repositoryMock
            ->expects(self::once())
            ->method('count')
            ->with([])
            ->willReturn(1);

        $this->repositoryMock
            ->expects(self::exactly(2))
            ->method('findBy')
            ->willReturnCallback(
                static fn (
                    array $criteria,
                    ?array $orderBy = null,
                    $limit = null,
                    $offset = null
                ) => match ([$criteria, $orderBy, $limit, $offset]) {
                    [[], null, 500, 0] => $resources,
                    [[], null, 500, 500] => [],
                },
            );

        $query = Query::fromRemoteIds(
            array_map(static fn (RemoteResource $resource): string => $resource->getRemoteId(), $resources),
            500,
        );

        $searchResult = new Result(0, null, []);

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->commandTester->execute([]);

        self::assertSame(
            '0/1 [>---------------------------]   0%'
            . PHP_EOL . ' 1/1 [============================] 100%'
            . PHP_EOL . 'There are 1 resources no longer existing on remote. Use --delete to delete them.',
            trim($this->commandTester->getDisplay()),
        );
    }

    public function testExecuteWithDelete(): void
    {
        $resources = [
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t0',
                type: 'raw',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t0.jpg',
                md5: 'o03jp5093hn3850ds07l35j6043s823h',
                id: 56,
                name: 'a87hg9xfxrd4itiim3t0',
            ),
        ];

        $this->repositoryMock
            ->expects(self::once())
            ->method('count')
            ->with([])
            ->willReturn(1);

        $this->repositoryMock
            ->expects(self::exactly(2))
            ->method('findBy')
            ->willReturnCallback(
                static fn (
                    array $criteria,
                    ?array $orderBy = null,
                    $limit = null,
                    $offset = null
                ) => match ([$criteria, $orderBy, $limit, $offset]) {
                    [[], null, 500, 0] => $resources,
                    [[], null, 500, 500] => [],
                },
            );

        $query = Query::fromRemoteIds(
            array_map(static fn (RemoteResource $resource): string => $resource->getRemoteId(), $resources),
            500,
        );

        $searchResult = new Result(0, null, []);

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->commandTester->execute(['--delete' => true]);

        self::assertSame(
            '0/1 [>---------------------------]   0%'
            . PHP_EOL . ' 1/1 [============================] 100%'
            . PHP_EOL . 'Deleting resources that are no longer on remote:'
            . PHP_EOL . ' 0/1 [>---------------------------]   0%'
            . PHP_EOL . ' 1/1 [============================] 100%',
            trim($this->commandTester->getDisplay()),
        );
    }

    public function testExecuteWithCustomBatchSizeAndDelete(): void
    {
        $resources = [
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t0',
                type: 'raw',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t0.zip',
                md5: 'o03jp5093hn3850ds07l35j6043s823h',
                id: 56,
                name: 'a87hg9xfxrd4itiim3t0',
            ),
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t1',
                type: 'image',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t1.jpg',
                md5: 'o03jp5093hn3850ds07l35j6043s323h',
                id: 57,
                name: 'a87hg9xfxrd4itiim3t1',
            ),
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t2',
                type: 'video',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t2.mp4',
                md5: 'o03jp5093hn3850as07l35j6043s823h',
                id: 58,
                name: 'a87hg9xfxrd4itiim3t2',
            ),
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t3',
                type: 'video',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t3.mp4',
                md5: 'o03jp5093hn3850zs07l35j6043s823h',
                id: 59,
                name: 'a87hg9xfxrd4itiim3t3',
            ),
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t4',
                type: 'image',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t4.jpg',
                md5: 'o03jp5093hn3830zs07l35j6043s823h',
                id: 60,
                name: 'a87hg9xfxrd4itiim3t4',
            ),
        ];

        $this->repositoryMock
            ->expects(self::once())
            ->method('count')
            ->with([])
            ->willReturn(5);

        $this->repositoryMock
            ->expects(self::exactly(4))
            ->method('findBy')
            ->willReturnCallback(
                static fn (
                    array $criteria,
                    ?array $orderBy = null,
                    $limit = null,
                    $offset = null
                ) => match ([$criteria, $orderBy, $limit, $offset]) {
                    [[], null, 2, 0] => array_slice($resources, 0, 2),
                    [[], null, 2, 2] => array_slice($resources, 2, 2),
                    [[], null, 2, 4] => array_slice($resources, 4, 2),
                    [[], null, 2, 6] => [],
                },
            );

        $resourcesFromCloudinary = [
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t0',
                type: 'raw',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t0.zip',
                md5: 'o03jp5093hn3850ds07l35j6043s823h',
                id: 56,
                name: 'a87hg9xfxrd4itiim3t0',
            ),
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t1',
                type: 'image',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t1.jpg',
                md5: 'o03jp5093hn3850ds07l35j6043s323h',
                id: 57,
                name: 'a87hg9xfxrd4itiim3t1',
            ),
            new RemoteResource(
                remoteId: 'upload|image|a87hg9xfxrd4itiim3t2',
                type: 'video',
                url: 'https://res.cloudinary.com/demo/image/upload/media/image/a87hg9xfxrd4itiim3t2.mp4',
                md5: 'o03jp5093hn3850as07l35j6043s823h',
                id: 58,
                name: 'a87hg9xfxrd4itiim3t2',
            ),
        ];

        $query1 = Query::fromRemoteIds(
            array_map(static fn (RemoteResource $resource): string => $resource->getRemoteId(), array_slice($resources, 0, 2)),
            2,
        );

        $query2 = Query::fromRemoteIds(
            array_map(static fn (RemoteResource $resource): string => $resource->getRemoteId(), array_slice($resources, 2, 2)),
            2,
        );

        $query3 = Query::fromRemoteIds(
            array_map(static fn (RemoteResource $resource): string => $resource->getRemoteId(), array_slice($resources, 4, 2)),
            2,
        );

        $this->providerMock
            ->expects(self::exactly(3))
            ->method('search')
            ->willReturnCallback(
                static fn (
                    Query $query,
                ) => match ([$query->getRemoteIds(), $query->getNextCursor(), $query->getLimit()]) {
                    [$query1->getRemoteIds(), $query1->getNextCursor(), $query1->getLimit()] => new Result(2, null, array_slice($resourcesFromCloudinary, 0, 2)),
                    [$query2->getRemoteIds(), $query2->getNextCursor(), $query2->getLimit()] => new Result(1, null, array_slice($resourcesFromCloudinary, 2, 2)),
                    [$query3->getRemoteIds(), $query3->getNextCursor(), $query3->getLimit()] => new Result(0, null, []),
                },
            );

        $finalResources = [
            $resources[0]->refresh($resourcesFromCloudinary[0]),
            $resources[1]->refresh($resourcesFromCloudinary[1]),
            $resources[2]->refresh($resourcesFromCloudinary[2]),
        ];

        $this->providerMock
            ->expects(self::exactly(3))
            ->method('store')
            ->willReturnCallback(
                static fn (
                    RemoteResource $resource,
                ) => match ($resource) {
                    $finalResources[0] => $finalResources[0],
                    $finalResources[1] => $finalResources[1],
                    $finalResources[2] => $finalResources[2],
                },
            );

        $this->providerMock
            ->expects(self::exactly(2))
            ->method('remove')
            ->willReturnCallback(
                static fn (
                    RemoteResource $resource,
                ) => match ($resource) {
                    $resources[3] => $resources[3],
                    $resources[4] => $resources[4],
                },
            );

        $this->commandTester->execute([
            '--batch-size' => 2,
            '--delete' => true,
        ]);

        self::assertSame(
            '0/5 [>---------------------------]   0%'
            . PHP_EOL . ' 5/5 [============================] 100%'
            . PHP_EOL . 'Deleting resources that are no longer on remote:'
            . PHP_EOL . ' 0/2 [>---------------------------]   0%'
            . PHP_EOL . ' 2/2 [============================] 100%',
            trim($this->commandTester->getDisplay()),
        );
    }
}
