<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

use function count;

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
final class NamedTransformation implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        return match (true) {
            count($config) > 0 => ['transformation' => $config[0]],
            default => throw new TransformationHandlerFailedException(self::class),
        };
    }
}
