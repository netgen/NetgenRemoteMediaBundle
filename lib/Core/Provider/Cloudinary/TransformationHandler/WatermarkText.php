<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\TransformationHandler;

use Netgen\RemoteMedia\Core\Transformation\HandlerInterface;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;

use function array_key_exists;
use function array_merge;
use function in_array;

/**
 * Class WatermarkText.
 */
final class WatermarkText implements HandlerInterface
{
    private const SUPPORTED_CONFIG = [
        'text',
        'font_family',
        'font_size',
        'align',
        'x',
        'y',
        'angle',
        'opacity',
        'density',
        'color',
    ];

    /**
     * Takes options from the configuration and returns
     * properly configured array of options.
     */
    public function process(array $config = []): array
    {
        if (!($config['text'] ?? null)) {
            throw new TransformationHandlerFailedException(self::class);
        }

        $options = [
            'overlay' => [
                'text' => $config['text'],
                'font_family' => $config['font_family'] ?? 'Arial',
                'font_size' => $config['font_size'] ?? 14,
            ],
        ];

        unset($config['text'], $config['font_family'], $config['font_size']);

        foreach ($config as $key => $value) {
            if (!in_array($key, self::SUPPORTED_CONFIG, true)) {
                unset($config[$key]);
            }
        }

        if (array_key_exists('align', $config)) {
            switch ($config['align']) {
                case 'left':
                    $options['gravity'] = 'west';

                    break;

                case 'right':
                    $options['gravity'] = 'east';

                    break;

                case 'top':
                    $options['gravity'] = 'north';

                    break;

                case 'bottom':
                    $options['gravity'] = 'south';

                    break;
            }
            unset($config['align']);
        }

        return array_merge($options, $config);
    }
}
