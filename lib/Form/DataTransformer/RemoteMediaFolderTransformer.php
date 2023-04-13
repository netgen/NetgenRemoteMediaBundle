<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\DataTransformer;

use Netgen\RemoteMedia\API\Values\Folder;
use Symfony\Component\Form\DataTransformerInterface;

final class RemoteMediaFolderTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (!$value instanceof Folder) {
            return null;
        }

        return [
            'folder' => $value->getPath(),
        ];
    }

    public function reverseTransform($value)
    {
        return ($value['folder'] ?? null) !== null ? Folder::fromPath($value['folder']) : null;
    }
}
