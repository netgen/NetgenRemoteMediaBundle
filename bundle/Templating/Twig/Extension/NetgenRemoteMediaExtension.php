<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class NetgenRemoteMediaExtension extends AbstractExtension
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    protected $provider;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver
     */
    protected $variationResolver;

    public function __construct(
        RemoteMediaProvider $provider,
        TranslationHelper $translationHelper,
        ContentTypeService $contentTypeService,
        VariationResolver $variationResolver
    ) {
        $this->provider = $provider;
        $this->translationHelper = $translationHelper;
        $this->contentTypeService = $contentTypeService;
        $this->variationResolver = $variationResolver;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'netgen_remote_media';
    }

    public function getFilters()
    {
        return [
            new TwigFilter(
                'scaling_format',
                [$this, 'scalingFormat']
            ),
        ];
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'netgen_remote_variation',
                [$this, 'getRemoteImageVariation']
            ),
            new TwigFunction(
                'netgen_remote_variation_embed',
                [$this, 'getRemoteImageVariationEmbed']
            ),
            new TwigFunction(
                'netgen_remote_video',
                [$this, 'getRemoteVideoTag']
            ),
            new TwigFunction(
                'netgen_remote_video_embed',
                [$this, 'getRemoteVideoTagEmbed']
            ),
            new TwigFunction(
                'netgen_remote_video_thumbnail',
                [$this, 'getVideoThumbnail']
            ),
            new TwigFunction(
                'netgen_remote_download',
                [$this, 'getResourceDownloadLink']
            ),
            new TwigFunction(
                'netgen_remote_media',
                [$this, 'getRemoteResource']
            ),
            new TwigFunction(
                'netgen_remote_media_embed',
                [$this, 'getRemoteResourceEmbed']
            ),
            new TwigFunction(
                'ngrm_is_croppable',
                [$this, 'contentTypeIsCroppable']
            ),
            new TwigFunction(
                'ngrm_available_variations',
                [$this, 'variationsForContent']
            ),
        ];
    }

    public function scalingFormat(array $variations)
    {
        if (empty($variations)) {
            return $variations;
        }

        $availableVariations = [];

        foreach ($variations as $variationName => $variationConfig) {
            foreach ($variationConfig['transformations'] as $name => $config) {
                if ($name !== 'crop') {
                    continue;
                }

                $availableVariations[$variationName] = $config;
            }
        }

        return $availableVariations;
    }

    /**
     * Returns variation by specified format.
     *
     * @param string $fieldIdentifier
     * @param string $format
     * @param bool $secure
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    public function getRemoteImageVariation(Content $content, $fieldIdentifier, $format, $secure = true)
    {
        $field = $this->translationHelper->getTranslatedField($content, $fieldIdentifier);
        $contentTypeIdentifier = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId)->identifier;

        return $this->provider->buildVariation($field->value, $contentTypeIdentifier, $format, $secure);
    }

    /**
     * Returns variation by specified format from resource value.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $format
     * @param bool $secure
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    public function getRemoteImageVariationEmbed(Value $value, $format, $secure = true)
    {
        return $this->provider->buildVariation($value, 'embedded', $format, $secure);
    }

    /**
     * Generates html5 video tag for the video with provided id.
     *
     * @param string $fieldIdentifier
     * @param string $format
     *
     * @return mixed
     */
    public function getRemoteVideoTag(Content $content, $fieldIdentifier, $format = '')
    {
        $field = $this->translationHelper->getTranslatedField($content, $fieldIdentifier);
        $contentTypeIdentifier = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId)->identifier;

        return $this->provider->generateVideoTag($field->value, $contentTypeIdentifier, $format);
    }

    /**
     * Generates html5 video tag for the video with provided resource and format.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $format
     *
     * @return mixed
     */
    public function getRemoteVideoTagEmbed(Value $value, $format = '')
    {
        return $this->provider->generateVideoTag($value, 'embedded', $format);
    }

    /**
     * Returns thumbnail url.
     *
     * @param array $options
     *
     * @return string
     */
    public function getVideoThumbnail(Value $value, ?array $options = [])
    {
        return $this->provider->getVideoThumbnail($value, $options);
    }

    /**
     * Returns the link to the remote resource.
     *
     * @return string
     */
    public function getResourceDownloadLink(Value $value)
    {
        return $this->provider->generateDownloadLink($value);
    }

    /**
     * Creates variation directly form Value, without the need for Content.
     *
     * @param $format
     *
     * @return Variation
     */
    public function getRemoteResource(Value $value, $format)
    {
        return $this->provider->buildVariation($value, 'custom', $format, true);
    }

    /**
     * Gets remote resource with resource ID and resource type (for embed).
     * If provided, it sets image variations coords.
     *
     * @param string $resourceId
     * @param string $resourceType
     *
     * @return Variation
     */
    public function getRemoteResourceEmbed(string $resourceId, string $resourceType, ?string $coords = null)
    {
        $resource = $this->provider->getRemoteResource($resourceId, $resourceType);

        if ($coords !== null) {
            $resource->variations = \json_decode($coords, true);
        }

        return $resource;
    }

    /**
     * Returns true if there is croppable variation configuration for the given content type.
     *
     * @return bool
     */
    public function contentTypeIsCroppable(Content $content)
    {
        $contentTypeIdentifier = $this->contentTypeService->loadContentType(
            $content->contentInfo->contentTypeId
        )->identifier;

        return !empty($this->variationResolver->getCroppbableVariations($contentTypeIdentifier));
    }

    /**
     * Returns the list of available variations for the given content.
     * If second parameter is true, it will return only variations with crop configuration.
     *
     * @param $onlyCroppable
     *
     * @return array
     */
    public function variationsForContent($contentTypeIdentifier, $onlyCroppable = false)
    {
        if ($contentTypeIdentifier instanceof Content) {
            $contentTypeIdentifier = $this->contentTypeService->loadContentType(
                $contentTypeIdentifier->contentInfo->contentTypeId
            )->identifier;
        }

        return $onlyCroppable ?
            $this->variationResolver->getCroppbableVariations($contentTypeIdentifier) :
            $this->variationResolver->getVariationsForContentType($contentTypeIdentifier);
    }
}
