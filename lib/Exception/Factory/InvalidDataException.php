<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Exception\Factory;

use Exception;

final class InvalidDataException extends Exception
{
    public function __construct(?string $message = null)
    {
        if ($message === null) {
            $message = 'Invalid data has been provided to the remote resource factory.';
        }

        parent::__construct($message);
    }
}
