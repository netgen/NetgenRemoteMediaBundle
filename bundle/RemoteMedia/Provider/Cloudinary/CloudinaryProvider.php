<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary;

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

class CloudinaryProvider extends RemoteMediaProvider
{
    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway */
    protected $gateway;

    protected $noExtensionMimeTypes;

    public function __construct(
        Registry $registry,
        VariationResolver $variationsResolver,
        Gateway $gateway,
        LoggerInterface $logger = null,
        array $noExtensionMimeTypes = ['image', 'video']
    ) {
        $this->gateway = $gateway;
        $this->noExtensionMimeTypes = $noExtensionMimeTypes;

        parent::__construct($registry, $variationsResolver, $logger);
    }

    /**
     * @return bool
     */
    public function supportsContentBrowser()
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

        if (\is_array($variationName)) {
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
     * Returns the remote resource with provided id and type.
     *
     * @param string $resourceType
     */
    public function getRemoteResource(string $resourceId, ?string $resourceType = 'image'): Value
    {
        if (empty($resourceId)) {
            return new Value();
        }

        $response = $this->gateway->get($resourceId, $resourceType);

        if (empty($response)) {
            return new Value();
        }

        return Value::createFromCloudinaryResponse($response);
    }

    /**
     * Adds tag to remote resource.
     *
     * @return mixed
     */
    public function addTagToResource(string $resourceId, string $tag)
    {
        return $this->gateway->addTag($resourceId, $tag);
    }

    /**
     * Removes tag from remote resource.
     *
     * @return mixed
     */
    public function removeTagFromResource(string $resourceId, string $tag)
    {
        return $this->gateway->removeTag($resourceId, $tag);
    }

    /**
     * @param $resourceId
     * @param $tags
     *
     * @return mixed
     */
    public function updateTags(string $resourceId, string $tags)
    {
        $options = [
            'tags' => $tags,
        ];

        $this->gateway->update($resourceId, $options);
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
            'resource_type' => $resourceType,
        ];

        $this->gateway->update($resourceId, $options);
    }

    /**
     * Returns thumbnail url for the video with provided id.
     *
     * @param array $options
     */
    public function getVideoThumbnail(Value $value, $options = []): string
    {
        if (!empty($options)) {
            $options['start_offset'] = !empty($options['start_offset']) ? $options['start_offset'] : 'auto';
            $options['resource_type'] = 'video';

            return $this->gateway->getVideoThumbnail($value->resourceId, $options);
        }

        $options['start_offset'] = !empty($options['start_offset']) ? $options['start_offset'] : 'auto';

        $options['crop'] = 'fit';
        $options['width'] = 320;
        $options['height'] = 240;
        $options['resource_type'] = 'video';

        return $this->gateway->getVideoThumbnail($value->resourceId, $options);
    }

    /**
     * Generates html5 video tag for the video with provided id.
     *
     * @param string $contentTypeIdentifier
     * @param string $format
     */
    public function generateVideoTag(Value $value, $contentTypeIdentifier, ?string $format = ''): string
    {
        $finalOptions = [
            'fallback_content' => 'Your browser does not support HTML5 video tags',
        ];

        if (empty($format)) {
            return $this->gateway->getVideoTag($value->resourceId, $finalOptions);
        }

        if (\is_array($format)) {
            $finalOptions = $format + $finalOptions;
        } else {
            $options = $this->processConfiguredVariation($value, $format, $contentTypeIdentifier);

            $finalOptions['transformation'] = $options;
            $finalOptions['secure'] = true;
        }

        return $this->gateway->getVideoTag($value->resourceId, $finalOptions);
    }

    /**
     * Generates the link to the remote resource.
     */
    public function generateDownloadLink(Value $value): string
    {
        $options = [
            'type' => $value->metaData['type'],
            'resource_type' => $value->metaData['resource_type'],
            'flags' => 'attachment',
        ];

        return $this->gateway->getDownloadLink($value->resourceId, $options);
    }

    /**
     * Removes the resource from the remote.
     */
    public function deleteResource(string $resourceId)
    {
        $this->gateway->delete($resourceId);
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
        $clean = \preg_replace("/[^\p{L}|\p{N}]+/u", '_', $uploadFile->originalFilename());
        $cleanFileName = \preg_replace("/[\p{Z}]{2,}/u", '_', $clean);
        $fileName = \rtrim($cleanFileName, '_');

        // check if overwrite is set, if it is, do not append random string
        $overwrite = $options['overwrite'] ?? false;
        $invalidate = $options['invalidate'] ?? $overwrite;

        $publicId = $overwrite ? $fileName : $fileName . '_' . \base_convert(\uniqid(), 16, 36);
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
                    $transformationIdentifier, $this->getIdentifier()
                );
            } catch (TransformationHandlerNotFoundException $e) {
                $this->logError($e->getMessage());

                continue;
            }

            try {
                $options[] = $transformationHandler->process($value, $variationName, $config);
            } catch (TransformationHandlerFailedException $e) {
                $this->logError($e->getMessage());

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
        $parsedMime = \explode('/', $file->getMimeType());
        if (\count($parsedMime) !== 2) {
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
        if ($extension !== 'pdf' && !\in_array($mimeCategory, $this->noExtensionMimeTypes, true)) {
            $publicId .= '.' . $extension;
        }

        return $publicId;
    }
}
