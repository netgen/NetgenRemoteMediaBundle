<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary;

use Cloudinary\Api\NotFound;
use InvalidArgumentException;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\Variation;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use Netgen\RemoteMedia\Core\UploadFile;
use Netgen\RemoteMedia\Core\VariationResolver;
use Netgen\RemoteMedia\Exception\MimeCategoryParseException;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use Netgen\RemoteMedia\Exception\TransformationHandlerNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use function array_key_exists;
use function base_convert;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function preg_replace;
use function rtrim;
use function str_replace;
use function uniqid;

final class CloudinaryProvider extends RemoteMediaProvider
{
    protected Gateway $gateway;

    protected bool $enableAudioWaveform;

    /** @var string[] */
    protected array $noExtensionMimeTypes;

    public function __construct(
        Registry $registry,
        VariationResolver $variationsResolver,
        Gateway $gateway,
        bool $enableAudioWaveform,
        ?LoggerInterface $logger = null,
        array $noExtensionMimeTypes = ['image', 'video']
    ) {
        $this->gateway = $gateway;
        $this->enableAudioWaveform = $enableAudioWaveform;
        $this->noExtensionMimeTypes = $noExtensionMimeTypes;

        parent::__construct($registry, $variationsResolver, $logger);
    }

    public function getIdentifier(): string
    {
        return 'cloudinary';
    }

    public function usage(): array
    {
        return $this->gateway->usage();
    }

    public function supportsFolders(): bool
    {
        return true;
    }

    public function listFolders(): array
    {
        return $this->gateway->listFolders();
    }

    public function listSubFolders(string $parentFolder): array
    {
        return $this->gateway->listSubFolders($parentFolder);
    }

    public function createFolder(string $path): void
    {
        $this->gateway->createFolder($path);
    }

    public function countResources(): int
    {
        return $this->gateway->countResources();
    }

    public function countResourcesInFolder(string $folder): int
    {
        return $this->gateway->countResourcesInFolder($folder);
    }

    public function listTags(): array
    {
        return $this->gateway->listTags();
    }

    public function upload(UploadFile $uploadFile, ?array $options = []): RemoteResource
    {
        $options = $this->prepareUploadOptions($uploadFile, $options);
        $response = $this->gateway->upload($uploadFile->uri(), $options);

        return RemoteResource::createFromCloudinaryResponse($response);
    }

    public function getRemoteResource(string $resourceId, string $resourceType = 'image'): RemoteResource
    {
        try {
            $response = $this->gateway->get($resourceId, $resourceType);
        } catch (NotFound $e) {
            throw new RemoteResourceNotFoundException($resourceId, $resourceType);
        }

        if (empty($response)) {
            throw new RemoteResourceNotFoundException($resourceId, $resourceType);
        }

        try {
            return RemoteResource::createFromCloudinaryResponse($response);
        } catch (InvalidArgumentException $e) {
            throw new RemoteResourceNotFoundException($resourceId, $resourceType);
        }
    }

    public function searchResources(Query $query): Result
    {
        return $this->gateway->search($query);
    }

    public function searchResourcesCount(Query $query): int
    {
        return $this->gateway->searchCount($query);
    }

    public function deleteResource(RemoteResource $resource): void
    {
        $this->gateway->delete($resource->resourceId, $resource->resourceType);
    }

    /**
     * Gets the remote media Variation.
     * If the remote media does not support variations, this method should return the Variation
     * with the url set to original resource.
     *
     * @param mixed $format
     */
    public function buildVariation(RemoteResource $resource, string $variationGroup, $format, ?bool $secure = true): Variation
    {
        $variation = new Variation();
        $url = $secure ? $resource->secure_url : $resource->url;
        $variation->url = $url;

        if (empty($format)) {
            return $variation;
        }

        if (is_array($format)) {
            /*
             * This means the 'variationName' is actually an array with all the configuration
             * options provided, and we can pass those directly to the cloudinary
             */
            $options = $format;
        } else {
            $options = $this->processConfiguredVariation($resource, $format, $variationGroup);
        }

        $finalOptions['transformation'] = $options;
        $finalOptions['secure'] = $secure;

        $url = $this->gateway->getVariationUrl($resource->resourceId, $finalOptions);
        $variation->url = $url;

        return $variation;
    }

    public function addTagToResource(RemoteResource $resource, string $tag): void
    {
        $this->gateway->addTag($resource->resourceId, $resource->resourceType, $tag);
    }

    public function removeTagFromResource(RemoteResource $resource, string $tag): void
    {
        $this->gateway->removeTag($resource->resourceId, $resource->resourceType, $tag);
    }

    public function removeAllTagsFromResource(RemoteResource $resource): void
    {
        $this->gateway->removeAllTags($resource->resourceId, $resource->resourceType);
    }

    public function updateTags(RemoteResource $resource, array $tags): void
    {
        $options = [
            'tags' => implode(',', $tags),
        ];

        $this->gateway->update($resource->resourceId, $resource->resourceType, $options);
    }

    /**
     * Updates the resource context.
     * eg. alt text and caption:
     * context = [
     *      'caption' => 'new caption'
     *      'alt' => 'alt text'
     * ];.
     */
    public function updateResourceContext(RemoteResource $resource, array $context): void
    {
        $options = [
            'context' => $context,
        ];

        $this->gateway->update($resource->resourceId, $resource->resourceType, $options);
    }

    public function getVideoThumbnail(RemoteResource $resource, ?array $options = []): string
    {
        if (count($options) === 0 || !array_key_exists('resource_type', $options)) {
            $options['resource_type'] = 'video';
        }

        if ($this->isAudio($resource)) {
            $options['raw_transformation'] = 'fl_waveform';
        }

        $options['start_offset'] = !empty($options['start_offset']) ? $options['start_offset'] : 'auto';

        return $this->gateway->getVideoThumbnail($resource->resourceId, $options);
    }

    public function generateVideoTag(RemoteResource $resource, string $variationGroup, $format = []): string
    {
        $transformationOptions = $format;

        if (!is_array($transformationOptions)) {
            $transformationOptions = [];
            $transformationOptions['transformation'] = $this->processConfiguredVariation($resource, $format, $variationGroup);
            $transformationOptions['secure'] = true;
        }

        $finalOptions = [
            'fallback_content' => 'Your browser does not support HTML5 video tags',
            'controls' => true,
            'poster' => $transformationOptions,
        ];

        $finalOptions = $finalOptions + $transformationOptions;

        $enableAudioWaveform = $this->enableAudioWaveform;

        if (is_array($format) && array_key_exists('enable_audio_waveform', $format)) {
            $enableAudioWaveform = $format['enable_audio_waveform'];
        }

        if ($this->isAudio($resource)) {
            if ($enableAudioWaveform) {
                $finalOptions['poster']['raw_transformation'] = 'fl_waveform';
            }

            if (!$enableAudioWaveform) {
                $finalOptions['attributes']['poster'] = null;
            }
        }

        $tag = $this->gateway->getVideoTag($resource->resourceId, $finalOptions);

        if ($this->isAudio($resource) && !$enableAudioWaveform) {
            $tag = str_replace(['<video', '</video>'], ['<audio', '</audio>'], $tag);
        }

        return $tag;
    }

    public function generateDownloadLink(RemoteResource $resource): string
    {
        $options = [
            'type' => $resource->type,
            'resource_type' => $resource->resourceType,
            'flags' => 'attachment',
            'secure' => true,
        ];

        return $this->gateway->getDownloadLink($resource->resourceId, $resource->resourceType, $options);
    }

    /**
     * Prepares upload options for Cloudinary.
     * Every image with the same name will be overwritten.
     */
    protected function prepareUploadOptions(UploadFile $uploadFile, array $options = []): array
    {
        $clean = preg_replace('#[^\\p{L}|\\p{N}]+#u', '_', $options['filename'] ?? $uploadFile->originalFilename());
        $cleanFileName = preg_replace('#[\\p{Z}]{2,}#u', '_', $clean);
        $fileName = rtrim($cleanFileName, '_');

        // check if overwrite is set, if it is, do not append random string
        $overwrite = $options['overwrite'] ?? false;
        $invalidate = $options['invalidate'] ?? $overwrite;

        $publicId = $overwrite ? $fileName : $fileName . '_' . base_convert(uniqid(), 16, 36);
        $publicId = $this->appendExtension($publicId, $uploadFile);

        if (!empty($options['folder'])) {
            $publicId = $options['folder'] . '/' . $publicId;
        }

        return [
            'public_id' => $publicId,
            'overwrite' => $overwrite,
            'invalidate' => $invalidate,
            'discard_original_filename' => $options['discard_original_filename'] ?? true,
            'context' => [
                'alt' => !empty($options['alt_text']) ? $options['alt_text'] : '',
                'caption' => !empty($options['caption']) ? $options['caption'] : '',
            ],
            'resource_type' => !empty($options['resource_type']) ? $options['resource_type'] : 'auto',
            'tags' => !empty($options['tags']) ? $options['tags'] : [],
        ];
    }

    /**
     * Builds transformation options for the provider to consume.
     *
     * @return array options of the total sum of transformations for the provider to use
     */
    protected function processConfiguredVariation(RemoteResource $resource, string $variationName, string $variationGroup): array
    {
        $configuredVariations = $this->variationResolver->getVariationsForGroup($variationGroup);

        $options = [];

        if (!isset($configuredVariations[$variationName])) {
            return $options;
        }

        $variationConfiguration = $configuredVariations[$variationName];
        foreach ($variationConfiguration['transformations'] as $transformationIdentifier => $config) {
            try {
                $transformationHandler = $this->registry->getHandler(
                    $transformationIdentifier,
                    $this->getIdentifier(),
                );
            } catch (TransformationHandlerNotFoundException $transformationHandlerNotFoundException) {
                $this->logError($transformationHandlerNotFoundException->getMessage());

                continue;
            }

            try {
                $options[] = $transformationHandler->process($resource, $variationName, $config);
            } catch (TransformationHandlerFailedException $transformationHandlerFailedException) {
                $this->logError($transformationHandlerFailedException->getMessage());

                continue;
            }
        }

        return $options;
    }

    private function parseMimeCategory(File $file)
    {
        $parsedMime = explode('/', $file->getMimeType());
        if (count($parsedMime) !== 2) {
            throw new MimeCategoryParseException($file->getMimeType());
        }

        return $parsedMime[0];
    }

    private function appendExtension(string $publicId, UploadFile $uploadFile): string
    {
        $extension = $uploadFile->originalExtension();

        if (empty($extension)) {
            return $publicId;
        }

        $file = new File($uploadFile->uri());
        $mimeCategory = $this->parseMimeCategory($file);

        // cloudinary handles pdf in a weird way - it is considered an "image" but it delivers it with proper extension on download
        if ($extension !== 'pdf' && !in_array($mimeCategory, $this->noExtensionMimeTypes, true)) {
            $publicId .= '.' . $extension;
        }

        return $publicId;
    }

    private function isAudio(RemoteResource $resource): bool
    {
        $audioFormats = ['aac', 'aiff', 'amr', 'flac', 'm4a', 'mp3', 'ogg', 'opus', 'wav'];

        return array_key_exists('format', $resource->metaData) && in_array($resource->metaData['format'], $audioFormats, true);
    }
}
