<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\HandlerInterface;

/**
 * Class NamedTransformation.
 *
 * A named transformation is a set of image transformations that has been given a custom name
 * for easy reference. It is useful to define a named transformation when you have a set of
 * relatively complex transformations that you use often and that you want to easily reference,
 * and using named transformations simplifies the enabling/disabling of transformations in
 * Strict Transformations mode.
 * Named transformations can also include other named transformations, which allows you to
 * define a chain of transformations to run on uploaded images very easily.
 */
class NamedTransformation implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
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
                'transformation' => $config[0],
            );
        }

        throw new TransformationHandlerFailedException(self::class);
    }
}
