<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\TransformationInterface;

/**
 * Class Crop
 *
 * If there are saved values for the crop in the database
 * for the current alias (format), it builds crop options.
 * Otherwise, fails.
 *
 */
class Crop implements TransformationInterface
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
        if (array_key_exists($alias, $value->variations)) {
            $coords = $value->variations[$alias];
            $options[] = array(
                'x' => (int)$coords['x'],
                'y' => (int)$coords['y'],
                'width' => (int)$coords['w'],
                'height' => (int)$coords['h'],
                'crop' => 'crop',
            );

            return $options;
        }

        throw new TransformationHandlerFailedException(self::class);
    }
}
