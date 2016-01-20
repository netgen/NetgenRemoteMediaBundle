<?php

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation;
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
     * Constructor.
     *
     */
    public function __construct(RemoteMediaProviderInterface $provider, TranslationHelper $translationHelper)
    {
        $this->provider = $provider;
        $this->translationHelper = $translationHelper;
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
                array($this, 'getRemoteMediaUrl')
            ),
        );
    }

    /**
     *
     * @return string
     */
    public function getRemoteMediaUrl($field, $fieldSettings, $format, $secure = true)
    {
        $variation = new Variation();
        $url = $secure ? $field->value->secure_url : $field->value->url;

        if (array_key_exists($format, $fieldSettings['formats'])) {
            $selectedFormat = $fieldSettings['formats'][$format];

            $sizes = explode('x', $selectedFormat);
            $width = $sizes[0];
            $height = $sizes[1];

            $url = $this->provider->getFormattedUrl(
                $field->value->public_id,
                array(
                    'width' => $width,
                    'height' => $height,
                    'secure' => $secure
                )
            );

            $variation->width = $width;
            $variation->height = $height;
        }

        $variation->url = $url;

        return $variation;
    }
}
