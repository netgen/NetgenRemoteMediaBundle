<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Factory;

use DateTimeImmutable;

interface DateTime
{
    public function createCurrent(): DateTimeImmutable;
}
