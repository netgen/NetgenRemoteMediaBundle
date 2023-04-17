<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary;

use Cloudinary\Api\NotFound;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\Exception\MimeCategoryParseException;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerNotFoundException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Result;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\Registry;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\UploadFile;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use function array_key_exists;
use function base_convert;
use function count;
use function explode;
use function in_array;
use function is_array;
use function preg_replace;
use function rtrim;
use function str_replace;
use function uniqid;

class CloudinaryProvider extends RemoteMediaProvider
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway */
    protected $gateway;

    /** @var bool */
    protected $enableAudioWaveform;

    protected $noExtensionMimeTypes;

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

    /**
     * Returns API rate limits information.
     */
    public function usage(): array
    {
        return $this->gateway->usage();
    }

    public function supportsContentBrowser(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function supportsFolders()
    {
        return true;
    }

    /**
     * Uploads the local resource to remote storage and builds the Value from the response.
     *
     * @param array $options
     */
    public function upload(UploadFile $uploadFile, ?array $options = []): Value
    {
        $options = $this->prepareUploadOptions($uploadFile, $options);

        $response = $this->gateway->upload($uploadFile->uri(), $options);

        return Value::createFromCloudinaryResponse($response);
    }

    /**
     * Gets the remote media Variation.
     * If $variationName is an array, it is treated as an explicit set of options to build the variation.
     *
     * @param string|array $variationName
     * @param bool $secure
     */
    public function buildVariation(Value $value, string $contentTypeIdentifier, $variationName, ?bool $secure = true): Variation
    {
        $variation = new Variation();
        $url = $secure ? $value->secure_url : $value->url;
        $variation->url = $url;

        if (empty($variationName)) {
            return $variation;
        }

        if (is_array($variationName)) {
            /*
             * This means the 'variationName' is actually an array with all the configuration
             * options provided, and we can pass those directly to the cloudinary
             */
            $options = $variationName;
        } else {
            $options = $this->processConfiguredVariation($value, $variationName, $contentTypeIdentifier);
        }

        $finalOptions['transformation'] = $options;
        $finalOptions['secure'] = $secure;

        $url = $this->gateway->getVariationUrl($value->resourceId, $finalOptions);
        $variation->url = $url;

        return $variation;
    }

    /**
     * Counts available resources from the remote storage.
     */
    public function countResources(): int
    {
        return $this->gateway->countResources();
    }

    /**
     * Lists all available folders.
     */
    public function listFolders(): array
    {
        return $this->gateway->listFolders();
    }

    /**
     * Lists all available folders inside a given parent folder.
     * If folders are not supported, should return empty array.
     */
    public function listSubFolders(string $parentFolder): array
    {
        return $this->gateway->listSubFolders($parentFolder);
    }

    /**
     * Creates new folder in Cloudinary.
     */
    public function createFolder(string $path): void
    {
        $this->gateway->createFolder($path);
    }

    /**
     * @param $folder
     */
    public function countResourcesInFolder(string $folder): int
    {
        return $this->gateway->countResourcesInFolder($folder);
    }

    /**
     * Searches for the remote resource containing term in the query.
     */
    public function searchResources(Query $query): Result
    {
        return $this->gateway->search($query);
    }

    /**
     * Searches for the remote resource containing term in the query
     * and returns total results count.
     */
    public function searchResourcesCount(Query $query): int
    {
        return $this->gateway->searchCount($query);
    }

    /**
     * Returns the remote resource with provided id and type.
     */
    public function getRemoteResource(string $resourceId, string $resourceType = 'image'): Value
    {
        if (empty($resourceId)) {
            return new Value();
        }

        try {
            $response = $this->gateway->get($resourceId, $resourceType);
        } catch (NotFound $e) {
            return new Value();
        }

        if (empty($response)) {
            return new Value();
        }

        return Value::createFromCloudinaryResponse($response);
    }

    /**
     * Lists all available tags.
     */
    public function listTags(): array
    {
        return $this->gateway->listTags();
    }

    /**
     * Adds tag to remote resource.
     *
     * @return mixed
     */
    public function addTagToResource(string $resourceId, string $tag, string $resourceType = 'image')
    {
        return $this->gateway->addTag($resourceId, $resourceType, $tag);
    }

    /**
     * Removes tag from remote resource.
     *
     * @return mixed
     */
    public function removeTagFromResource(string $resourceId, string $tag, string $resourceType = 'image')
    {
        return $this->gateway->removeTag($resourceId, $resourceType, $tag);
    }

    /**
     * Removes all tags from remote resource.
     *
     * @return mixed
     */
    public function removeAllTagsFromResource(string $resourceId, string $resourceType = 'image')
    {
        return $this->gateway->removeAllTags($resourceId, $resourceType);
    }

    /**
     * @param $resourceId
     * @param $tags
     *
     * @return mixed
     */
    public function updateTags(string $resourceId, string $tags, string $resourceType = 'image')
    {
        $options = [
            'tags' => $tags,
        ];

        $this->gateway->update($resourceId, $resourceType, $options);
    }

    /**
     * Lists metadata fields.
     */
    public function listMetadataFields(): array
    {
        return $this->gateway->listMetadataFields();
    }

    /**
     * Updates the resource context.
     * eg. alt text and caption:
     * context = [
     *      'caption' => 'new caption'
     *      'alt' => 'alt text'
     * ];.
     *
     * @return mixed
     */
    public function updateResourceContext(string $resourceId, string $resourceType, array $context)
    {
        $options = [
            'context' => $context,
        ];

        $this->gateway->update($resourceId, $resourceType, $options);
    }

    /**
     * Returns thumbnail url for the video with provided id.
     *
     * @param array $options
     */
    public function getVideoThumbnail(Value $value, $options = []): string
    {
        if (array_key_exists('content_type_identifier', $options) && array_key_exists('variation_name', $options)) {
            $options['transformation'] = $this->processConfiguredVariation($value, $options['variation_name'], $options['content_type_identifier']);
            unset($options['content_type_identifier'], $options['variation_name']);
        }

        if (count($options) === 0 || !array_key_exists('resource_type', $options)) {
            $options['resource_type'] = 'video';
        }

        if ($this->isAudio($value)) {
            $options['raw_transformation'] = 'fl_waveform';
        }

        $options['start_offset'] = !empty($options['start_offset']) ? $options['start_offset'] : 'auto';

        return $this->gateway->getVideoThumbnail($value->resourceId, $options);
    }

    /**
     * Generates html5 video tag for the video with provided id.
     *
     * @param string $contentTypeIdentifier
     * @param string|array $format
     */
    public function generateVideoTag(Value $value, $contentTypeIdentifier, $format = ''): string
    {
        $transformationOptions = $format;

        if (!is_array($transformationOptions)) {
            $transformationOptions = [];
            $transformationOptions['transformation'] = $this->processConfiguredVariation($value, $format, $contentTypeIdentifier);
            $transformationOptions['secure'] = true;
        }

        $finalOptions = [
            'fallback_content' => 'Your browser does not support HTML5 video tags',
            'poster' => $transformationOptions,
        ];

        if (!array_key_exists('controls', $format) || $format['controls']) {
            $finalOptions['controls'] = true;
        }

        if (array_key_exists('controls', $transformationOptions)) {
            unset($transformationOptions['controls']);
        }

        $finalOptions = $finalOptions + $transformationOptions;

        $enableAudioWaveform = $this->enableAudioWaveform;

        if (is_array($format) && array_key_exists('enable_audio_waveform', $format)) {
            $enableAudioWaveform = $format['enable_audio_waveform'];
        }

        if ($this->isAudio($value)) {
            if ($enableAudioWaveform) {
                $finalOptions['poster']['raw_transformation'] = 'fl_waveform';
            }

            if (!$enableAudioWaveform) {
                $finalOptions['attributes']['poster'] = null;
            }
        }

        $tag = $this->gateway->getVideoTag($value->resourceId, $finalOptions);

        if ($this->isAudio($value) && !$enableAudioWaveform) {
            $tag = str_replace(['<video', '</video>'], ['<audio', '</audio>'], $tag);
        }

        return $tag;
    }

    /**
     * Generates the link to the remote resource.
     */
    public function generateDownloadLink(Value $value): string
    {
        $options = [
            'type' => $value->type,
            'resource_type' => $value->resourceType,
            'flags' => 'attachment',
            'secure' => true,
        ];

        return $this->gateway->getDownloadLink($value->resourceId, $value->resourceType, $options);
    }

    /**
     * Removes the resource from the remote.
     */
    public function deleteResource(string $resourceId, string $resourceType = 'image')
    {
        $this->gateway->delete($resourceId, $resourceType);
    }

    /**
     * Returns unique identifier of the provided.
     */
    public function getIdentifier(): string
    {
        return 'cloudinary';
    }

    /**
     * Prepares upload options for Cloudinary.
     * Every image with the same name will be overwritten.
     *
     * @param array $options
     */
    protected function prepareUploadOptions(UploadFile $uploadFile, $options = []): array
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
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     * @param string $variationName
     * @param string $contentTypeIdentifier
     *
     * @return array options of the total sum of transformations for the provider to use
     */
    protected function processConfiguredVariation(Value $value, $variationName, $contentTypeIdentifier): array
    {
        $configuredVariations = $this->variationResolver->getVariationsForContentType($contentTypeIdentifier);

        $options = [];

        if (!isset($configuredVariations[$variationName])) {
            return $options;
        }

        $variationConfiguration = $configuredVariations[$variationName];
        foreach ($variationConfiguration['transformations'] as $transformationIdentifier => $config) {
            try {
                $transformationHandler = $this->registry->getHandler(
                    $transformationIdentifier,
                    $this->getIdentifier()
                );
            } catch (TransformationHandlerNotFoundException $transformationHandlerNotFoundException) {
                $this->logError($transformationHandlerNotFoundException->getMessage());

                continue;
            }

            try {
                $options[] = $transformationHandler->process($value, $variationName, $config);
            } catch (TransformationHandlerFailedException $transformationHandlerFailedException) {
                $this->logError($transformationHandlerFailedException->getMessage());

                continue;
            }
        }

        return $options;
    }

    /**
     * @throws MimeCategoryParseException
     *
     * @return mixed
     */
    private function parseMimeCategory(File $file)
    {
        $parsedMime = explode('/', $file->getMimeType());
        if (count($parsedMime) !== 2) {
            throw new MimeCategoryParseException($file->getMimeType());
        }

        return $parsedMime[0];
    }

    /**
     * @param $publicId
     */
    private function appendExtension($publicId, UploadFile $uploadFile): string
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

    private function isAudio(Value $value): bool
    {
        $audioFormats = ['aac', 'aiff', 'amr', 'flac', 'm4a', 'mp3', 'ogg', 'opus', 'wav'];

        return array_key_exists('format', $value->metaData) && in_array($value->metaData['format'], $audioFormats, true);
    }
}
