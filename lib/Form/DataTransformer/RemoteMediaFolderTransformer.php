<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Netgen\RemoteMedia\API\Values\Folder;
use Symfony\Component\Form\DataTransformerInterface;

final class RemoteMediaFolderTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (!$value instanceof Folder) {
            return null;
        }

        return [
            'folder' => $value->getPath(),
        ];
    }

    public function reverseTransform(mixed $value): mixed
    {
        return ($value['folder'] ?? null) !== null ? Folder::fromPath($value['folder']) : null;
    }
}
