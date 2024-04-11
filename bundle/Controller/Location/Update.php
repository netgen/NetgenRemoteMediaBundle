<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Location;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Update
{
    public function __invoke(Request $request): Response
    {
        dump($request);

        return new JsonResponse([ 'locationId' => 123 ]);
    }
}
