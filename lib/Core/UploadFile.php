<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use function pathinfo;
use const PATHINFO_EXTENSION;
use const PATHINFO_FILENAME;

final class UploadFile
{
    private string $uri;

    private string $originalFilename;

    private string $originalExtension;

    public static function fromUri(string $uri): self
    {
        $uploadFile = new self();

        $uploadFile->uri = $uri;
        $uploadFile->originalFilename = pathinfo($uri, PATHINFO_FILENAME);
        $uploadFile->originalExtension = pathinfo($uri, PATHINFO_EXTENSION);

        return $uploadFile;
    }

    public static function fromUploadedFile(UploadedFile $file)
    {
        $uploadFile = new self();

        $uploadFile->uri = $file->getRealPath();
        $uploadFile->originalFilename = $file->getClientOriginalName();
        $uploadFile->originalExtension = $file->getClientOriginalExtension();

        return $uploadFile;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function originalFilename(): string
    {
        return $this->originalFilename;
    }

    public function originalExtension(): string
    {
        return $this->originalExtension;
    }
}
