<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use function array_pop;
use function count;
use function explode;
use function implode;

final class Folder
{
    private string $name;
    private ?Folder $parent = null;

    public function __construct(string $name, ?self $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    public function __toString(): string
    {
        return $this->getPath();
    }

    public static function fromPath(string $path): self
    {
        $folders = explode('/', $path);

        if (count($folders) === 1) {
            return new self($folders[0]);
        }

        return new self(
            array_pop($folders),
            self::fromPath(implode('/', $folders)),
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isRoot(): bool
    {
        return !$this->parent instanceof self;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getPath(): string
    {
        return $this->isRoot()
            ? $this->name
            : $this->parent->getPath() . '/' . $this->name;
    }
}
