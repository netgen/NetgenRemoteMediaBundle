<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Facets;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\Exception\NotSupportedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class Load
{
    private ProviderInterface $provider;

    private TranslatorInterface $translator;

    public function __construct(ProviderInterface $provider, TranslatorInterface $translator)
    {
        $this->provider = $provider;
        $this->translator = $translator;
    }

    public function __invoke(): Response
    {
        try {
            $folders = $this->provider->listFolders();
        } catch (NotSupportedException $e) {
            $folders = [];
        }

        try {
            $tags = $this->provider->listTags();
        } catch (NotSupportedException $e) {
            $tags = [];
        }

        $formattedFolders = [];

        /** @var \Netgen\RemoteMedia\API\Values\Folder $folder */
        foreach ($folders as $folder) {
            $formattedFolders[] = [
                'id' => $folder->getPath(),
                'label' => $folder->getName(),
                'children' => null,
            ];
        }

        $formattedTags = [];
        foreach ($tags as $tag) {
            $formattedTags[] = [
                'name' => $tag,
                'id' => $tag,
            ];
        }

        $supportedTypes = $this->provider->getSupportedTypes();
        $formattedTypes = [];

        foreach ($supportedTypes as $type) {
            $formattedTypes[] = [
                'name' => $this->resolveTypeName($this->provider->getIdentifier(), $type),
                'id' => $type,
            ];
        }

        $result = [
            'types' => $formattedTypes,
            'folders' => $formattedFolders,
            'tags' => $formattedTags,
        ];

        return new JsonResponse($result);
    }

    private function resolveTypeName(string $provider, string $type): string
    {
        $transKey = 'ngrm.provider.' . $provider . '.supported_types.' . $type;
        $trans = $this->translator->trans($transKey, [], 'ngremotemedia');

        if ($trans === $transKey) {
            return $type;
        }

        return $trans;
    }
}
