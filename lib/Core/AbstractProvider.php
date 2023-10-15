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
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\Registry as TransformationRegistry;
use Netgen\RemoteMedia\Exception\NamedRemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\NamedRemoteResourceNotFoundException;
use Netgen\RemoteMedia\Exception\NotSupportedException;
use Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractProvider implements ProviderInterface
{
    protected ObjectRepository $resourceRepository;

    protected ObjectRepository $locationRepository;

    public function __construct(
        protected TransformationRegistry $registry,
        protected VariationResolver $variationResolver,
        protected EntityManagerInterface $entityManager,
        protected DateTimeFactoryInterface $dateTimeFactory,
        private array $namedRemoteResources,
        private array $namedRemoteResourceLocations,
        protected ?LoggerInterface $logger = null,
        private bool $shouldDeleteFromRemote = false
    ) {
        $this->logger = $this->logger ?? new NullLogger();

        $this->resourceRepository = $entityManager->getRepository(RemoteResource::class);
        $this->locationRepository = $entityManager->getRepository(RemoteResourceLocation::class);
    }

    /**
     * @return \Netgen\RemoteMedia\API\Values\Folder[]
     *
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
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
     * @return string[]
     *
     * @throws \Netgen\RemoteMedia\Exception\NotSupportedException
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

        $existingResource->refresh($resource);
        $existingResource->setUpdatedAt($this->dateTimeFactory->createCurrent());

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

    public function loadNamedRemoteResource(string $name): RemoteResource
    {
        if (($this->namedRemoteResources[$name] ?? null) === null) {
            throw new NamedRemoteResourceNotFoundException($name);
        }

        $remoteId = $this->namedRemoteResources[$name];

        try {
            return $this->loadByRemoteId($remoteId);
        } catch (RemoteResourceNotFoundException $exception) {
            $resource = $this->loadFromRemote($remoteId);

            $this->store($resource);

            return $resource;
        }
    }

    public function loadNamedRemoteResourceLocation(string $name): RemoteResourceLocation
    {
        if (($this->namedRemoteResourceLocations[$name] ?? null) === null) {
            throw new NamedRemoteResourceLocationNotFoundException($name);
        }

        $source = $this->namedRemoteResourceLocations[$name]['source'] ?? 'named_remote_resource_location_' . $name;
        $watermarkText = $this->namedRemoteResourceLocations[$name]['watermark_text'] ?? null;

        try {
            $location = $this->loadLocationBySource($source);

            if ($location->getWatermarkText() !== $watermarkText) {
                $location->setWatermarkText($watermarkText);

                $this->storeLocation($location);
            }

            return $location;
        } catch (RemoteResourceLocationNotFoundException $e) {
        }

        $remoteId = $this->namedRemoteResourceLocations[$name]['resource_remote_id'];

        try {
            $resource = $this->loadByRemoteId($remoteId);
        } catch (RemoteResourceNotFoundException $exception) {
            $resource = $this->loadFromRemote($remoteId);

            $this->store($resource);
        }

        $location = new RemoteResourceLocation($resource, $source, [], $watermarkText);
        $this->storeLocation($location);

        return $location;
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

    public function generateHtmlTag(RemoteResource $resource, array $htmlAttributes = [], bool $forceVideo = false, bool $useThumbnail = false): string
    {
        return $this->generateRawVariationHtmlTag($resource, [], $htmlAttributes, $forceVideo, $useThumbnail);
    }

    public function generateVariationHtmlTag(
        RemoteResourceLocation $location,
        string $variationGroup,
        string $variationName,
        array $htmlAttributes = [],
        bool $forceVideo = false,
        bool $useThumbnail = false
    ): string {
        $transformations = $this->variationResolver->processConfiguredVariation(
            $location,
            $this->getIdentifier(),
            $variationGroup,
            $variationName,
        );

        return $this->generateRawVariationHtmlTag($location->getRemoteResource(), $transformations, $htmlAttributes, $forceVideo, $useThumbnail);
    }

    public function generateRawVariationHtmlTag(
        RemoteResource $resource,
        array $transformations = [],
        array $htmlAttributes = [],
        bool $forceVideo = false,
        bool $useThumbnail = false
    ): string {
        switch ($resource->getType()) {
            case RemoteResource::TYPE_IMAGE:
                return $this->generatePictureTag($resource, $transformations, $htmlAttributes);

            case RemoteResource::TYPE_VIDEO:
                return $useThumbnail
                    ? $this->generateVideoThumbnailTag($resource, $transformations, $htmlAttributes)
                    : $this->generateVideoTag($resource, $transformations, $htmlAttributes);

            case RemoteResource::TYPE_AUDIO:
                if ($useThumbnail) {
                    return $this->generateVideoThumbnailTag($resource, $transformations, $htmlAttributes);
                }

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

    abstract protected function generateVideoThumbnailTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string;

    abstract protected function generateAudioTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string;

    abstract protected function generateDocumentTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string;

    abstract protected function generateDownloadTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string;

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceLocationNotFoundException
     */
    private function loadLocationBySource(string $source): RemoteResourceLocation
    {
        $remoteResourceLocation = $this->locationRepository->findOneBy(['source' => $source]);

        if ($remoteResourceLocation instanceof RemoteResourceLocation) {
            return $remoteResourceLocation;
        }

        throw new RemoteResourceLocationNotFoundException(0);
    }
}
