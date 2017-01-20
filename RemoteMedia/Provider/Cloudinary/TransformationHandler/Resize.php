<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\TransformationInterface;

/**
 * Class Resize
 *
 * To change the size of a image, use the width and height parameters
 * (w and h in URLs) to assign new values. You can resize the image
 * by using both the width and height parameters or with only one of them:
 * the other dimension is automatically updated to maintain the aspect ratio.
 */
class Resize implements TransformationInterface
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
        if ($config[0] !== 0) {
            $options['width'] = $config[0];
        }

        if ($config[1] !== 0) {
            $options['height'] = $config[1];
        }

        return $options;
    }
}
