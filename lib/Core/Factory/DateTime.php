<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Factory;

use DateTimeImmutable;
use Netgen\RemoteMedia\API\Factory\DateTime as DateTimeFactoryInterface;

final class DateTime implements DateTimeFactoryInterface
{
    public function createCurrent(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}
