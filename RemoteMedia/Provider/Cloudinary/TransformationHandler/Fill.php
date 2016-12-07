<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\TransformationInterface;

class Fill implements TransformationInterface
{
    /**
     * If transformation is supported in the admin interface
     * for cropping.
     *
     * @return bool
     */
    public function isCroppable()
    {
        return true;
    }

    /**
     * Takes options from the configuration and returns
     * properly configured array of options
     *
     * @param array $config
     *
     * @return array
     */
    public function process(array $config = array())
    {
        $options = array(
            'crop' => 'fill'
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
