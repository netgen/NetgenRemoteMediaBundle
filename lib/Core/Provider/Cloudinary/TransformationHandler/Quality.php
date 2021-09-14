<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

/**
 * Class Quality
 * Set compression level to apply to an image as a value between 1
 * (smallest file size possible) and 100 (best visual quality).
 * The quality transformation parameter can be set to auto in order to
 * perform automatic quality selection and image encoding adjustments.
 * Further control of the automatic quality selection is supported as follows:
 *      q_auto - The optimal balance between file size and visual quality.
 *               By default, this is the same as q_auto:good.
 *      q_auto:best - Less aggressive algorithm. Generates bigger files with potentially
 *                    better visual quality. Example of a target audience: photography sites
 *                    that display images with a high visual quality.
 *      q_auto:good - Ensuring a relatively small file size with good visual quality.
 *      q_auto:eco - More aggressive algorithm, which results in smaller files of slightly
 *                   lower visual quality. Example of a target audience: popular sites and
 *                   social networks with a huge amount of traffic.
 *      q_auto:low - Most aggressive algorithm, which results in the smallest files of low visual
 *                  quality. Example of a target audience: sites using thumbnail images that
 *                  link to higher quality images.
 */
final class Quality implements HandlerInterface
{
    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(RemoteResource $resource, string $variationName, array $config = []): array
    {
        if (empty($config[0])) {
            throw new TransformationHandlerFailedException(self::class);
        }

        if (empty($config[1])) {
            return [
                'quality' => $config[0],
            ];
        }

        if ($config[0] === 'auto') {
            return [
                'quality' => $config[0] . ':' . $config[1],
            ];
        }

        throw new TransformationHandlerFailedException(self::class);
    }
}
