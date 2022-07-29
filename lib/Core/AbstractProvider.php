<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\Query;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\SearchResult;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\API\Values\Variation;
use Netgen\RemoteMedia\Core\Transformation\Registry as TransformationRegistry;
use Netgen\RemoteMedia\Exception\NotSupportedException;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

abstract class AbstractProvider implements ProviderInterface
{
    protected TransformationRegistry $registry;

    protected VariationResolver $variationResolver;

    protected EntityManagerInterface $entityManager;

    protected ObjectRepository $resourceRepository;

    protected ObjectRepository $locationRepository;

    protected ?LoggerInterface $logger;

    public function __construct(
        TransformationRegistry $registry,
        VariationResolver $variationsResolver,
        EntityManagerInterface $entityManager,
        ?LoggerInterface $logger = null
    ) {
        $this->registry = $registry;
        $this->variationResolver = $variationsResolver;
        $this->logger = $logger;

        $this->entityManager = $entityManager;
        $this->resourceRepository = $entityManager->getRepository(RemoteResource::class);
        $this->locationRepository = $entityManager->getRepository(RemoteResourceLocation::class);
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
     *
     * @return \Netgen\RemoteMedia\API\Values\Folder[]
     */
    public function listFolders(?Folder $parent = null): array
    {
        if (!$this->supportsFolders()) {
            throw new NotSupportedException('folders', $this->getIdentifier());
        }

        return $this->getFolders($parent);
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
     */
    public function createFolder(string $name, ?Folder $parent = null): Folder
    {
        if (!$this->supportsFolders()) {
            throw new NotSupportedException('folders', $this->getIdentifier());
        }

        return $this->internalCreateFolder($name, $parent);
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
     */
    public function countInFolder(Folder $folder): int
    {
        if (!$this->supportsFolders()) {
            throw new NotSupportedException('folders', $this->getIdentifier());
        }

        return $this->internalCountInFolder($folder);
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
     *
     * @return string[]
     */
    public function listTags(): array
    {
        if (!$this->supportsTags()) {
            throw new NotSupportedException('tags', $this->getIdentifier());
        }

        return $this->getTags();
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function load(int $id): RemoteResource
    {
        $remoteResource = $this->resourceRepository->find($id);

        if ($remoteResource instanceof RemoteResource) {
            return $remoteResource;
        }

        throw new RemoteResourceNotFoundException((string) $id);
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function loadByRemoteId(string $remoteId): RemoteResource
    {
        $remoteResource = $this->resourceRepository->findBy(['remoteId' => $remoteId]);

        if ($remoteResource instanceof RemoteResource) {
            return $remoteResource;
        }

        throw new RemoteResourceNotFoundException($remoteId);
    }

    /**
     * @return \Netgen\RemoteMedia\API\Values\Folder[]
     */
    abstract protected function getFolders(?Folder $parent = null): array;

    abstract protected function internalCreateFolder(string $name, ?Folder $parent = null): Folder;

    abstract protected function internalCountInFolder(Folder $folder): int;

    /**
     * @return string[]
     */
    abstract protected function getTags(): array;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException
     */
    public function loadLocation(int $id): RemoteResourceLocation
    {
        $remoteResourceLocation = $this->locationRepository->find($id);

        if ($remoteResourceLocation instanceof RemoteResourceLocation) {
            return $remoteResourceLocation;
        }

        throw new RemoteResourceLocationNotFoundException($id);
    }

    /*abstract public function upload(ResourceStruct $resourceStruct): RemoteResource;*/

    public function store(RemoteResource $resource): RemoteResource
    {
        if ($resource->getId()) {
            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            return $resource;
        }

        $existingResource = $this->resourceRepository->findOneBy(['remoteId' => $resource->getRemoteId()]);

        if (!$existingResource instanceof RemoteResource) {
            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            return $resource;
        }

        $existingResource->setType($resource->getType());
        $existingResource->setUrl($resource->getUrl());
        $existingResource->setSize($resource->getSize());
        $existingResource->setAltText($resource->getAltText());
        $existingResource->setCaption($resource->getCaption());
        $existingResource->setTags($resource->getTags());
        $existingResource->setMetadata($resource->getMetadata());
        $existingResource->setUpdatedAt(new DateTimeImmutable('now'));

        $this->entityManager->persist($existingResource);
        $this->entityManager->flush();

        return $existingResource;
    }

    public function addLocation(RemoteResource $remoteResource, array $cropSettings = []): RemoteResourceLocation
    {
        $location = new RemoteResourceLocation($remoteResource, $cropSettings);

        $this->entityManager->persist($location);
        $this->entityManager->flush();

        return $location;
    }

    public function delete(RemoteResource $resource): void
    {
        $this->entityManager->remove($resource);
        $this->entityManager->flush();

        if ($this->shouldDeleteFromRemote && $this->supportsDelete()) {
            $this->deleteFromRemote($resource);
        }
    }

    public function deleteLocation(RemoteResourceLocation $resourceLocation): void
    {
        $this->entityManager->remove($resourceLocation);
        $this->entityManager->flush();

        /** @var \Netgen\RemoteMedia\API\Values\RemoteResourceLocation $location */
        foreach ($resourceLocation->getRemoteResource()->locations as $location) {
            if ($location->getId() !== $resourceLocation->getId()) {
                return;
            }
        }

        $this->delete($resourceLocation->getRemoteResource());
    }

    protected function logError(string $message): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->error($message);
        }
    }
}
