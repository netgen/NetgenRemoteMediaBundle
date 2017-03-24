<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\HandlerInterface;

/**
 * Class Format
 *
 * Sets the in which format should the media be delivered.
 * Images can be uploaded to Cloudinary in various formats, and these images can be converted
 * to other formats for displaying.
 *
 * Automatic format selection can also be used to save bandwidth and optimize delivery time by
 * automatically delivering images as WebP to Chrome browsers or JPEG-XR to Internet Explorer
 * browsers. The best format will be delivered to the supported browser.
 * If a browser does not support either of these formats then the image is delivered in the format
 * specified by the file extension.
 *
 */
class Format implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $variationName name of the configured image variation configuration
     * @param array $config
     *
     * @throws \Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException
     *
     * @return array
     */
    public function process(Value $value, $variationName, array $config = array())
    {
        if (empty($config[0])) {
            throw new TransformationHandlerFailedException(self::class);
        }

        if (!empty($config[0])) {
            return array(
                'fetch_format' => $config[0]
            );
        }

        throw new TransformationHandlerFailedException(self::class);
    }
}
