<?php

namespace Netgen\Bundle\RemoteMediaBundle\Templating\Twig\Extension;

use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
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
     * NetgenRemoteMediaExtension constructor.
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProviderInterface $provider
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
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
                array($this, 'getRemoteMediaVariation')
            ),
        );
    }

    /**
     * @param \eZ\Publish\Core\FieldType\Value $field
     * @param array                            $fieldSettings
     * @param string                           $format
     * @param bool                             $secure
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation
     */
    public function getRemoteMediaVariation(Value $value, $availableFormats, $format, $secure = true)
    {
        return $this->provider->getVariation($value, $availableFormats, $format, $secure);
    }
}
