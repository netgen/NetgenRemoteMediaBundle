<?php

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class NetgenRemoteMediaExtension extends Twig_Extension
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface
     */
    protected $provider;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper
     */
    protected $helper;

    /**
     * NetgenRemoteMediaExtension constructor.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface $provider
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Helper
     */
    public function __construct(RemoteMediaProviderInterface $provider, TranslationHelper $translationHelper, Helper $helper)
    {
        $this->provider = $provider;
        $this->translationHelper = $translationHelper;
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
        return array(
            new Twig_SimpleFunction(
                'netgen_remote_media',
                array($this, 'getRemoteMediaVariation')
            ),
            new Twig_SimpleFunction(
                'ng_field_settings',
                array($this, 'getFieldSettings')
            ),
            new Twig_SimpleFunction(
                'netgen_remote_thumbnail',
                array($this, 'getVideoThumbnail')
            ),
            new Twig_SimpleFunction(
                'netgen_remote_video',
                array($this, 'getRemoteVideoTag')
            ),
            new Twig_SimpleFunction(
                'netgen_remote_media_fits',
                array($this, 'mediaFits')
            ),
            new Twig_SimpleFunction(
                'netgen_remote_resource',
                array($this, 'getResourceDownloadLink')
            ),
            new Twig_SimpleFunction(
                'netgen_remote_variation',
                array($this, 'getRemoteMediaByVariation')
            ),
        );
    }

    /**
     * Returns the Variation with the provided format
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param array                            $availableFormats
     * @param string                           $format
     * @param bool                             $secure
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    public function getRemoteMediaVariation(Value $value, $availableFormats, $format, $secure = true)
    {
        // Make sure named-formats are array
        $availableFormats = is_array($availableFormats) ? $availableFormats : array();
        return $this->provider->getVariation($value, $format, $availableFormats, $secure);
    }

    public function getFieldSettings(Content $content, $fieldIdentifier)
    {
        $field = $content->getField($fieldIdentifier);

        return $this->helper->loadAvailableFormats($content->id, $field->id);
    }

    /**
     * Returns thumbnail url
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     *
     * @return string
     */
    public function getVideoThumbnail(Value $value)
    {
        return $this->provider->getVideoThumbnail($value->resourceId);
    }

    /**
     * Generates html5 video tag for the video with provided id
     *
     * @param Value $value
     * @param string $format
     * @param array $availableFormats
     *
     * @return mixed
     */
    public function getRemoteVideoTag(Value $value, $format = '', $availableFormats = array())
    {
        return $this->provider->generateVideoTag($value->resourceId, $format, $availableFormats);
    }

    /**
     * Calculates whether the remote image fits into all variations
     *
     * @param Value $value
     * @param $variations
     *
     * @return bool
     */
    public function mediaFits(Value $value, $variations)
    {
        $valueWidth = $value->metaData['width'];
        $valueHeight = $value->metaData['height'];

        foreach ($variations as $variationName => $variationSize) {
            $variationSizeArray = explode('x', $variationSize);

            if ($valueWidth < $variationSizeArray[0] || $valueHeight < $variationSizeArray[1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the link to the remote resource
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
     * Returns variation by specified format
     *
     * @param APIContent    $content
     * @param string        $fieldIdentifier
     * @param string        $format
     * @param bool          $secure
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    public function getRemoteMediaByVariation(APIContent $content, $fieldIdentifier, $format, $secure = true)
    {
        $emptyVariation = new Variation();
        $settings = $this->getFieldSettings($content, $fieldIdentifier);

        if (empty($settings)) {
            return $emptyVariation;
        }

        $field = $this->translationHelper->getTranslatedField($content, $fieldIdentifier);

        $variation =  $this->getRemoteMediaVariation($field->value, $settings, $format, $secure);

        return $variation;
    }
}
