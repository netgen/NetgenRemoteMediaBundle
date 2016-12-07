<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\TransformationInterface;

/**
 * Class Fit
 *
 * The image is resized so that it takes up as much space as possible
 * within a bounding box defined by the given width and height parameters.
 * The original aspect ratio is retained and all of the original image is visible.
 */
class Fit implements TransformationInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options
     *
     * @param \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value $value
     * @param string $alias
     * @param array $config
     *
     * @return array
     */
    public function process(Value $value, $alias, array $config = array())
    {
        $options = array(
            'crop' => 'fit'
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
