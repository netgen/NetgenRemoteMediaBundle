<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Netgen\RemoteMedia\API\Factory\DateTime as DateTimeFactoryInterface;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\Core\Transformation\Registry as TransformationRegistry;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Exception\NotSupportedException;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractProvider implements ProviderInterface
{
    protected TransformationRegistry $registry;

    protected VariationResolver $variationResolver;

    protected EntityManagerInterface $entityManager;

    protected ObjectRepository $resourceRepository;

    protected ObjectRepository $locationRepository;

    protected DateTimeFactoryInterface $dateTimeFactory;

    protected ?LoggerInterface $logger;

    private bool $shouldDeleteFromRemote;

    public function __construct(
        TransformationRegistry $registry,
        VariationResolver $variationsResolver,
        EntityManagerInterface $entityManager,
        DateTimeFactoryInterface $dateTimeFactory,
        ?LoggerInterface $logger = null,
        bool $shouldDeleteFromRemote = false
    ) {
        $this->registry = $registry;
        $this->variationResolver = $variationsResolver;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->logger = $logger ?? new NullLogger();
        $this->shouldDeleteFromRemote = $shouldDeleteFromRemote;

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
            throw new NotSupportedException($this->getIdentifier(), 'folders');
        }

        return $this->internalListFolders($parent);
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
     */
    public function createFolder(string $name, ?Folder $parent = null): Folder
    {
        if (!$this->supportsFolders()) {
            throw new NotSupportedException($this->getIdentifier(), 'folders');
        }

        return $this->internalCreateFolder($name, $parent);
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
     */
    public function countInFolder(Folder $folder): int
    {
        if (!$this->supportsFolders()) {
            throw new NotSupportedException($this->getIdentifier(), 'folders');
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
            throw new NotSupportedException($this->getIdentifier(), 'tags');
        }

        return $this->internalListTags();
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
        $remoteResource = $this->resourceRepository->findOneBy(['remoteId' => $remoteId]);

        if ($remoteResource instanceof RemoteResource) {
            return $remoteResource;
        }

        throw new RemoteResourceNotFoundException($remoteId);
    }

    public function store(RemoteResource $resource): RemoteResource
    {
        if ($resource->getId()) {
            $resource->setUpdatedAt($this->dateTimeFactory->createCurrent());

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

        $existingResource
            ->setType($resource->getType())
            ->setUrl($resource->getUrl())
            ->setSize($resource->getSize())
            ->setAltText($resource->getAltText())
            ->setCaption($resource->getCaption())
            ->setTags($resource->getTags())
            ->setMetadata($resource->getMetadata())
            ->setUpdatedAt($this->dateTimeFactory->createCurrent());

        $this->entityManager->persist($existingResource);
        $this->entityManager->flush();

        return $existingResource;
    }

    public function remove(RemoteResource $resource): void
    {
        $this->entityManager->remove($resource);
        $this->entityManager->flush();

        if ($this->shouldDeleteFromRemote && $this->supportsDelete()) {
            $this->deleteFromRemote($resource);
        }
    }

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

    public function storeLocation(RemoteResourceLocation $location): RemoteResourceLocation
    {
        $this->entityManager->persist($location);
        $this->entityManager->flush();

        return $location;
    }

    public function removeLocation(RemoteResourceLocation $resourceLocation): void
    {
        $this->entityManager->remove($resourceLocation);
        $this->entityManager->flush();
    }

    public function upload(ResourceStruct $resourceStruct): RemoteResource
    {
        $remoteResource = $this->internalUpload($resourceStruct);

        return $this->store($remoteResource);
    }

    public function buildVariation(RemoteResourceLocation $location, string $variationGroup, string $variationName): RemoteResourceVariation
    {
        $transformations = $this->variationResolver->processConfiguredVariation(
            $location,
            $this->getIdentifier(),
            $variationGroup,
            $variationName,
        );

        return $this->internalBuildVariation($location->getRemoteResource(), $transformations);
    }

    public function buildRawVariation(RemoteResource $resource, array $transformations): RemoteResourceVariation
    {
        return $this->internalBuildVariation($resource, $transformations);
    }

    public function buildVideoThumbnail(RemoteResource $resource, ?int $startOffset = null): RemoteResourceVariation
    {
        return $this->internalBuildVideoThumbnail($resource, [], $startOffset);
    }

    public function buildVideoThumbnailVariation(
        RemoteResourceLocation $location,
        string $variationGroup,
        string $variationName,
        ?int $startOffset = null
    ): RemoteResourceVariation {
        $transformations = $this->variationResolver->processConfiguredVariation(
            $location,
            $this->getIdentifier(),
            $variationGroup,
            $variationName,
        );

        return $this->internalBuildVideoThumbnail($location->getRemoteResource(), $transformations, $startOffset);
    }

    public function buildVideoThumbnailRawVariation(RemoteResource $resource, array $transformations = [], ?int $startOffset = null): RemoteResourceVariation
    {
        return $this->internalBuildVideoThumbnail($resource, $transformations, $startOffset);
    }

    public function generateHtmlTag(RemoteResource $resource, array $htmlAttributes = [], bool $forceVideo = false): string
    {
        return $this->generateRawVariationHtmlTag($resource, [], $htmlAttributes, $forceVideo);
    }

    public function generateVariationHtmlTag(
        RemoteResourceLocation $location,
        string $variationGroup,
        string $variationName,
        array $htmlAttributes = [],
        bool $forceVideo = false
    ): string {
        $transformations = $this->variationResolver->processConfiguredVariation(
            $location,
            $this->getIdentifier(),
            $variationGroup,
            $variationName,
        );

        return $this->generateRawVariationHtmlTag($location->getRemoteResource(), $transformations, $htmlAttributes, $forceVideo);
    }

    public function generateRawVariationHtmlTag(
        RemoteResource $resource,
        array $transformations = [],
        array $htmlAttributes = [],
        bool $forceVideo = false
    ): string {
        switch ($resource->getType()) {
            case RemoteResource::TYPE_IMAGE:
                return $this->generatePictureTag($resource, $transformations, $htmlAttributes);

            case RemoteResource::TYPE_VIDEO:
                return $this->generateVideoTag($resource, $transformations, $htmlAttributes);

            case RemoteResource::TYPE_AUDIO:
                return $forceVideo
                    ? $this->generateVideoTag($resource, $transformations, $htmlAttributes)
                    : $this->generateAudioTag($resource, $transformations, $htmlAttributes);

            case RemoteResource::TYPE_DOCUMENT:
                return $this->generateDocumentTag($resource, $transformations, $htmlAttributes);

            default:
                return $this->generateDownloadTag($resource, $transformations, $htmlAttributes);
        }
    }

    /**
     * @return \Netgen\RemoteMedia\API\Values\Folder[]
     */
    abstract protected function internalListFolders(?Folder $parent = null): array;

    abstract protected function internalCreateFolder(string $name, ?Folder $parent = null): Folder;

    abstract protected function internalCountInFolder(Folder $folder): int;

    /**
     * @return string[]
     */
    abstract protected function internalListTags(): array;

    abstract protected function internalUpload(ResourceStruct $resourceStruct): RemoteResource;

    abstract protected function internalBuildVariation(RemoteResource $resource, array $transformations = []): RemoteResourceVariation;

    abstract protected function internalBuildVideoThumbnail(RemoteResource $resource, array $transformations = [], ?int $startOffset = null): RemoteResourceVariation;

    abstract protected function generatePictureTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string;

    abstract protected function generateVideoTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string;

    abstract protected function generateAudioTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string;

    abstract protected function generateDocumentTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string;

    abstract protected function generateDownloadTag(RemoteResource $resource, array $htmlAttributes = []): string;
}
