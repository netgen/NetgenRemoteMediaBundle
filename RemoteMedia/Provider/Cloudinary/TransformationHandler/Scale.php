<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\HandlerInterface;

/**
 * Class Scale.
 *
 * Change the size of the image exactly to the given width and
 * height without necessarily retaining the original aspect ratio:
 * all original image parts are visible but might be stretched or
 * shrunk. If only the width or height is given, then the image is
 * scaled to the new dimension while retaining the original aspect ratio
 */
class Scale implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
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
            'crop' => 'scale',
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
