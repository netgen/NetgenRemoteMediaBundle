<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception;

use Exception;
use function sprintf;

final class CropSettingsNotFoundException extends Exception
{
    public function __construct(string $variationName)
    {
        parent::__construct(sprintf('Crop settings for variation "%s" were not found.', $variationName));
    }
}
