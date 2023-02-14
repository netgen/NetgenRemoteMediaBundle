<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Doctrine\ORM\EntityManagerInterface;
use Netgen\RemoteMedia\API\Factory\DateTime as DateTimeFactoryInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Upload\ResourceStruct;
use Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource;
use Netgen\RemoteMedia\API\Values\AuthToken;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Netgen\RemoteMedia\API\Values\StatusData;
use Netgen\RemoteMedia\Core\AbstractProvider;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver\UploadOptions as UploadOptionsResolver;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\Registry as TransformationRegistry;
use Netgen\RemoteMedia\Exception\Cloudinary\InvalidRemoteIdException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Psr\Log\LoggerInterface;

use function array_map;
use function array_merge;
use function basename;
use function count;
use function default_poster_options;
use function preg_match;
use function sprintf;
use function str_replace;

final class CloudinaryProvider extends AbstractProvider
{
    private const IDENTIFIER = 'cloudinary';

    private GatewayInterface $gateway;

    private UploadOptionsResolver $uploadOptionsResolver;

    public function __construct(
        TransformationRegistry $registry,
        VariationResolver $variationsResolver,
        EntityManagerInterface $entityManager,
        GatewayInterface $gateway,
        DateTimeFactoryInterface $datetimeFactory,
        UploadOptionsResolver $uploadOptionsResolver,
        ?LoggerInterface $logger = null,
        bool $shouldDeleteFromRemote = false
    ) {
        parent::__construct(
            $registry,
            $variationsResolver,
            $entityManager,
            $datetimeFactory,
            $logger,
            $shouldDeleteFromRemote,
        );

        $this->gateway = $gateway;
        $this->uploadOptionsResolver = $uploadOptionsResolver;
    }

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function supportsFolders(): bool
    {
        return true;
    }

    public function supportsDelete(): bool
    {
        return true;
    }

    public function supportsTags(): bool
    {
        return true;
    }

    public function supportsProtectedResources(): bool
    {
        return $this->gateway->isEncryptionEnabled();
    }

    public function status(): StatusData
    {
        return $this->gateway->usage();
    }

    public function getSupportedTypes(): array
    {
        return RemoteResource::SUPPORTED_TYPES;
    }

    public function getSupportedVisibilities(): array
    {
        if (!$this->gateway->isEncryptionEnabled()) {
            return [RemoteResource::VISIBILITY_PUBLIC];
        }

        return RemoteResource::SUPPORTED_VISIBILITIES;
    }

    public function count(): int
    {
        return $this->gateway->countResources();
    }

    /**
     * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
     */
    public function loadFromRemote(string $remoteId): RemoteResource
    {
        try {
            return $this->gateway->get(
                CloudinaryRemoteId::fromRemoteId($remoteId),
            );
        } catch (InvalidRemoteIdException $exception) {
            $this->logger->notice('[NGRM][Cloudinary] ' . $exception->getMessage());
        }

        throw new RemoteResourceNotFoundException($remoteId);
    }

    public function deleteFromRemote(RemoteResource $resource): void
    {
        $this->gateway->delete(
            CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
        );
    }

    public function search(Query $query): Result
    {
        return $this->gateway->search($query);
    }

    public function searchCount(Query $query): int
    {
        return $this->gateway->searchCount($query);
    }

    public function updateOnRemote(RemoteResource $resource): void
    {
        $options = [
            'context' => array_merge(
                $resource->getContext(),
                [
                    'alt' => $resource->getAltText(),
                    'caption' => $resource->getCaption(),
                ],
            ),
            'tags' => $resource->getTags(),
        ];

        if (count($resource->getTags()) === 0) {
            $this->gateway->removeAllTagsFromResource(
                CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
            );
        }

        $this->gateway->update(
            CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
            $options,
        );
    }

    public function generateDownloadLink(RemoteResource $resource): string
    {
        return $this->gateway->getDownloadLink(
            CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
        );
    }

    public function authenticateRemoteResource(RemoteResource $resource, AuthToken $token): AuthenticatedRemoteResource
    {
        $url = $this->gateway->getAuthenticatedUrl(CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()), $token);

        return new AuthenticatedRemoteResource($resource, $url, $token);
    }

    public function authenticateRemoteResourceVariation(RemoteResourceVariation $variation, AuthToken $token): AuthenticatedRemoteResource
    {
        $url = $this->gateway->getAuthenticatedUrl(
            CloudinaryRemoteId::fromRemoteId($variation->getRemoteResource()->getRemoteId()),
            $token,
            $variation->getTransformations(),
        );

        return new AuthenticatedRemoteResource($variation->getRemoteResource(), $url, $token);
    }

    protected function internalListFolders(?Folder $parent = null): array
    {
        return array_map(
            static fn ($folderPath) => Folder::fromPath($folderPath),
            $parent instanceof Folder
                ? $this->gateway->listSubFolders($parent->getPath())
                : $this->gateway->listFolders(),
        );
    }

    protected function internalCreateFolder(string $name, ?Folder $parent = null): Folder
    {
        $path = $name;
        if ($parent instanceof Folder) {
            $path = $parent->getPath() . '/' . $path;
        }

        $this->gateway->createFolder($path);

        return Folder::fromPath($path);
    }

    protected function internalCountInFolder(Folder $folder): int
    {
        return $this->gateway->countResourcesInFolder($folder->getPath());
    }

    protected function internalListTags(): array
    {
        return $this->gateway->listTags();
    }

    protected function internalUpload(ResourceStruct $resourceStruct): RemoteResource
    {
        return $this->gateway->upload(
            $resourceStruct->getFileStruct()->getUri(),
            $this->uploadOptionsResolver->resolve($resourceStruct),
        );
    }

    protected function internalBuildVariation(RemoteResource $resource, array $transformations = []): RemoteResourceVariation
    {
        $variationUrl = $this->gateway->getVariationUrl(
            CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
            $transformations,
        );

        return new RemoteResourceVariation($resource, $variationUrl, $transformations);
    }

    protected function internalBuildVideoThumbnail(RemoteResource $resource, array $transformations = [], ?int $startOffset = null): RemoteResourceVariation
    {
        $options = [
            'resource_type' => 'video',
            'transformation' => $transformations,
        ];

        if ($resource->getType() === RemoteResource::TYPE_AUDIO) {
            $options['raw_transformation'] = 'fl_waveform';
        }

        $options['start_offset'] = $startOffset !== null ? $startOffset : 'auto';

        $thumbnailUrl = $this->gateway->getVideoThumbnail(
            CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
            $options,
        );

        return new RemoteResourceVariation($resource, $thumbnailUrl, array_merge(default_poster_options(), $options));
    }

    protected function generatePictureTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string
    {
        if (!($htmlAttributes['alt'] ?? null) && $resource->getAltText()) {
            $htmlAttributes['alt'] = $resource->getAltText();
        }

        if (!($htmlAttributes['title'] ?? null) && $resource->getCaption()) {
            $htmlAttributes['title'] = $resource->getCaption();
        }

        $options = [
            'secure' => true,
            'attributes' => $htmlAttributes,
        ];

        if (count($transformations) > 0) {
            $options['transformation'] = $transformations;
        }

        return $this->gateway->getImageTag(
            CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
            $options,
        );
    }

    protected function generateVideoTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string
    {
        $options = [
            'secure' => true,
            'fallback_content' => 'Your browser does not support HTML5 video tags',
            'controls' => true,
            'poster' => [
                'secure' => true,
            ],
            'attributes' => $htmlAttributes,
        ];

        if (count($transformations) > 0) {
            $options['transformation'] = $transformations;
            $options['poster']['transformation'] = $transformations;
        }

        if ($resource->getType() === RemoteResource::TYPE_AUDIO) {
            $options['poster']['raw_transformation'] = 'fl_waveform';
        }

        return $this->gateway->getVideoTag(
            CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
            $options,
        );
    }

    protected function generateVideoThumbnailTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string
    {
        $thumbnailUrl = $this->buildVideoThumbnailRawVariation($resource, $transformations)->getUrl();

        if (!($htmlAttributes['alt'] ?? null) && $resource->getAltText()) {
            $htmlAttributes['alt'] = $resource->getAltText();
        }

        if (!($htmlAttributes['title'] ?? null) && $resource->getCaption()) {
            $htmlAttributes['title'] = $resource->getCaption();
        }

        $options = [
            'secure' => true,
            'attributes' => $htmlAttributes,
        ];

        if (count($transformations) > 0) {
            $options['transformation'] = $transformations;
        }

        $thumbnailTag = $this->gateway->getImageTag(
            CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
            $options,
        );

        preg_match('/src=["|\']([^"|\']*)["|\']/', $thumbnailTag, $parts);

        if (count($parts) > 1) {
            return str_replace($parts[1], $thumbnailUrl, $thumbnailTag);
        }
    }

    protected function generateAudioTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string
    {
        $options = [
            'secure' => true,
            'fallback_content' => 'Your browser does not support HTML5 audio tags',
            'controls' => true,
            'attributes' => $htmlAttributes,
        ];

        $tag = $this->gateway->getVideoTag(
            CloudinaryRemoteId::fromRemoteId($resource->getRemoteId()),
            $options,
        );

        return str_replace(
            ['<video', '</video>'],
            ['<audio', '</audio>'],
            $tag,
        );
    }

    protected function generateDocumentTag(RemoteResource $resource, array $transformations = [], array $htmlAttributes = []): string
    {
        return $this->generateDownloadTag($resource, $htmlAttributes);
    }

    protected function generateDownloadTag(RemoteResource $resource, array $htmlAttributes = []): string
    {
        $downloadLink = $this->generateDownloadLink($resource);
        $filename = basename($downloadLink);

        unset($htmlAttributes['href']);

        $htmlAttributesString = '';
        foreach ($htmlAttributes as $attributeKey => $attributeValue) {
            $htmlAttributesString .= sprintf(' %s="%s"', $attributeKey, $attributeValue);
        }

        return sprintf('<a href="%s"%s>%s</a>', $downloadLink, $htmlAttributesString, $filename);
    }
}
