<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Exception;

use Exception;

class MimeCategoryParseException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $mimeType
     */
    public function __construct($mimeType)
    {
        parent::__construct(sprintf('Could not parse mime category for given mime type: %s.', $mimeType));
    }
}
