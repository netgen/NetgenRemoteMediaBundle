<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\HandlerInterface;

/**
 * Class Lfill
 *
 * Same as the fill mode but only if the original image is larger
 * than the given limit (width and height), in which case the image
 * is scaled down to fill the given width and height while retaining
 * the original aspect ratio, using only part of the image that fills
 * the given dimensions if necessary (only part of the original image
 * might be visible if the requested aspect ratio is different from the
 * original aspect ratio). This mode doesn't scale up the image if your
 * requested dimensions are bigger than the original image's.
 *
 */
class Lfill implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $variationName name of the configured image variation configuration
     * @param array $config
     *
     * @return array
     */
    public function process(Value $value, $variationName, array $config = array())
    {
        $options = array(
            'crop' => 'lfill'
        );

        if (isset($config[0]) && $config[0] !== 0) {
            $options['width'] = $config[0];
        }

        if (isset($config[1]) && $config[1] !== 0) {
            $options['height'] = $config[1];
        }

        return $options;
    }
}
