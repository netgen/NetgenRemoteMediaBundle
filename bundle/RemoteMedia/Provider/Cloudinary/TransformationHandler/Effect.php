<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\HandlerInterface;

/**
 * Class Effect
 *
 * The value of the parameter includes the name of the effect and sometimes
 * an additional value that controls the behavior of the specific effect.
 * Cloudinary supports a large number of effects that can be applied to change
 * the visual appearance of delivered images.
 * List of all available effects:
 * http://cloudinary.com/documentation/image_transformations#applying_image_effects_and_filters
 *
 */
class Effect implements HandlerInterface
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

        if (empty($config[1])) {
            return array(
                'effect' => $config[0]
            );
        }

        return array(
            'effect' => $config[0] . ':' . $config[1]
        );
    }
}
