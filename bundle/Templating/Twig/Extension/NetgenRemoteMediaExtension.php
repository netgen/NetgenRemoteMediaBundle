<?php

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\Content\Content;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Twig_Extension;
use Twig_SimpleFunction;

class NetgenRemoteMediaExtension extends Twig_Extension
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
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper
     */
    protected $helper;

    /**
     * NetgenRemoteMediaExtension constructor.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider $provider
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper
     */
    public function __construct(
        RemoteMediaProvider $provider,
        TranslationHelper $translationHelper,
        ContentTypeService $contentTypeService,
        Helper $helper
    ) {
        $this->provider = $provider;
        $this->translationHelper = $translationHelper;
        $this->contentTypeService = $contentTypeService;
        $this->helper = $helper;
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

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'netgen_remote_variation',
                [$this, 'getRemoteImageVariation']
            ),
            new Twig_SimpleFunction(
                'netgen_remote_video',
                [$this, 'getRemoteVideoTag']
            ),
            new Twig_SimpleFunction(
                'netgen_remote_video_thumbnail',
                [$this, 'getVideoThumbnail']
            ),
            new Twig_SimpleFunction(
                'netgen_remote_download',
                [$this, 'getResourceDownloadLink']
            ),
            new Twig_SimpleFunction(
                'netgen_remote_media',
                [$this, 'getRemoteResource']
            ),
        ];
    }

    /**
     * Returns variation by specified format.
     *
     * @param Content $content
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
     * Generates html5 video tag for the video with provided id.
     *
     * @param Content $content
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
     * Returns thumbnail url.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param array $options
     *
     * @return string
     */
    public function getVideoThumbnail(Value $value, $options)
    {
        return $this->provider->getVideoThumbnail($value, $options);
    }

    /**
     * Returns the link to the remote resource.
     *
     * @param Value $value
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
     * @param Value $value
     * @param $format
     *
     * @return Variation
     */
    public function getRemoteResource(Value $value, $format)
    {
        return $this->provider->buildVariation($value, 'custom', $format, true);
    }
}
