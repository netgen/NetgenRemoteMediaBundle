<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Resource;

use Netgen\RemoteMedia\API\Search\Query;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Browse extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        $query = new Query([
            'query' => $request->query->get('query'),
            'types' => $request->query->get('type') ? [$request->query->get('type')] : [],
            'folders' => $request->query->get('folder') !== null ? [$request->query->get('folder')] : [],
            'tags' => $request->query->get('tag') ? [$request->query->get('tag')] : [],
            'limit' => $request->query->get('limit') ? (int) $request->query->get('limit') : 25,
            'nextCursor' => $request->query->get('next_cursor'),
        ]);

        $results = $this->provider->search($query);

        $result = [
            'hits' => $this->formatResources($results->getResources()),
            'load_more' => $results->getNextCursor() !== null,
            'next_cursor' => $results->getNextCursor(),
        ];

        return new JsonResponse($result);
    }
}
