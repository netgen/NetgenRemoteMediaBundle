<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core;

use Symfony\Component\HttpFoundation\Request;

interface RequestVerifierInterface
{
    public function verify(Request $request): bool;
}
