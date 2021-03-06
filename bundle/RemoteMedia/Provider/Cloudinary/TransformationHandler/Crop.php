<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\TransformationHandler;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation\HandlerInterface;
use function array_key_exists;

/**
 * Class Crop.
 *
 * If there are saved values for the crop in the database
 * for the current alias (format), it builds crop options.
 * Otherwise, fails.
 */
class Crop implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     *
     * @param string $variationName name of the configured image variation configuration
     *
     * @throws \Netgen\Bundle\RemoteMediaBundle\Exception\TransformationHandlerFailedException
     *
     * @return array
     */
    public function process(Value $value, $variationName, array $config = [])
    {
        if (array_key_exists($variationName, $value->variations)) {
            $coords = $value->variations[$variationName];

            $options[] = [
                'x' => (int) $coords['x'],
                'y' => (int) $coords['y'],
                'width' => (int) $coords['w'],
                'height' => (int) $coords['h'],
                'crop' => 'crop',
            ];

            return $options;
        }

        throw new TransformationHandlerFailedException(self::class);
    }
}
