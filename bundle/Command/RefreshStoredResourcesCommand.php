<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function array_key_exists;
use function array_map;
use function count;

use const PHP_EOL;

final class RefreshStoredResourcesCommand extends Command
{
    private ObjectRepository $resourceRepository;

    private ProgressBar $progressBar;

    private int $batchSize = 500;

    /** @var \Netgen\RemoteMedia\API\Values\RemoteResource[] */
    private array $resourcesToDelete;

    public function __construct(
        EntityManagerInterface $entityManager,
        private ProviderInterface $provider,
    ) {
        $this->resourceRepository = $entityManager->getRepository(RemoteResource::class);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('netgen:remote_media:refresh')
            ->setDescription('This command will refresh all stored resources in the database with newest data. WARNING: this might consume API rate limits.')
            ->addOption('batch-size', 'bs', InputOption::VALUE_OPTIONAL, 'Size of batch', 500)
            ->addOption('delete', 'd', InputOption::VALUE_NONE, 'Force deleting resources that are not found on remote.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->batchSize = $input->getOption('batch-size');
        $delete = $input->getOption('delete');
        $offset = 0;

        $this->progressBar = new ProgressBar($output, $this->resourceRepository->count([]));
        $this->progressBar->start();

        $this->resourcesToDelete = [];

        do {
            $resources = $this->getBatch($this->batchSize, $offset);

            $this->refreshResources($resources);

            $offset += $this->batchSize;
        } while (count($resources) > 0);

        $this->progressBar->finish();

        if (count($this->resourcesToDelete) > 0 && $delete === false) {
            $output->writeln(PHP_EOL . 'There are ' . count($this->resourcesToDelete) . ' resources no longer existing on remote. Use --delete to delete them.');

            return Command::SUCCESS;
        }

        if (count($this->resourcesToDelete) > 0 && $delete === true) {
            $output->writeln(PHP_EOL . 'Deleting resources that are no longer on remote:');

            $progressBar = new ProgressBar($output, count($this->resourcesToDelete));
            $progressBar->start();

            foreach ($this->resourcesToDelete as $resource) {
                $this->provider->remove($resource);
                $progressBar->advance();
            }

            $progressBar->finish();
        }

        return Command::SUCCESS;
    }

    /**
     * @return \Netgen\RemoteMedia\API\Values\RemoteResource[]
     */
    private function getBatch(int $limit, int $offset): array
    {
        return $this->resourceRepository->findBy([], null, $limit, $offset);
    }

    /**
     * @param \Netgen\RemoteMedia\API\Values\RemoteResource[] $resources
     */
    private function refreshResources(array $resources): void
    {
        $remoteIds = array_map(
            static fn (RemoteResource $resource): string => $resource->getRemoteId(),
            $resources,
        );

        $remoteResources = $this->getRemoteBatch($remoteIds);

        foreach ($resources as $resource) {
            if (!array_key_exists($resource->getRemoteId(), $remoteResources)) {
                $this->resourcesToDelete[] = $resource;

                $this->progressBar->advance();

                continue;
            }

            $remoteResource = $remoteResources[$resource->getRemoteId()];

            $this->provider->store($resource->refresh($remoteResource));
            $this->progressBar->advance();
        }
    }

    /**
     * @param string[] $remoteIds
     *
     * @return array<string, \Netgen\RemoteMedia\API\Values\RemoteResource>
     */
    private function getRemoteBatch(array $remoteIds): array
    {
        $query = Query::fromRemoteIds($remoteIds, $this->batchSize);

        $resources = [];

        do {
            $searchResult = $this->provider->search($query);

            foreach ($searchResult->getResources() as $resource) {
                $resources[$resource->getRemoteId()] = $resource;
            }

            $query->setNextCursor($searchResult->getNextCursor());
        } while ($searchResult->getNextCursor() !== null);

        return $resources;
    }
}
