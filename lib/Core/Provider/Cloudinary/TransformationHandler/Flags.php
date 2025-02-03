<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;

/**
 * Class Flags.
 *
 * This transformation allows you to set flags which Cloudinary supports,
 * see: https://cloudinary.com/documentation/transformation_reference#fl_flag.
 */
final class Flags implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        $options = [];

        if (count($config) === 0) {
            return $options;
        }

        return ['flags' => $config];
    }
}
