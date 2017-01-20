<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\HandlerInterface;

/**
 * Class Mfit
 *
 * Same as the fit mode but only if the original image is smaller
 * than the given minimum (width and height), in which case the image
 * is scaled up so that it takes up as much space as possible within
 * a bounding box defined by the given width and height parameters.
 * The original aspect ratio is retained and all of the original image
 * is visible. This mode doesn't scale down the image if your requested
 * dimensions are smaller than the original image's.
 */
class Mfit implements HandlerInterface
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
            'crop' => 'mfit'
        );

        if ($config[0] !== 0) {
            $options['width'] = $config[0];
        }

        if ($config[1] !== 0) {
            $options['height'] = $config[1];
        }

        return $options;
    }
}
