<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Upload;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use function pathinfo;

use const PATHINFO_BASENAME;
use const PATHINFO_EXTENSION;

final class FileStruct
{
    private string $uri;

    private string $originalFilename;

    private string $originalExtension;

    public static function fromUri(string $uri): self
    {
        $uploadFile = new self();

        $uploadFile->uri = $uri;
        $uploadFile->originalFilename = pathinfo($uri, PATHINFO_BASENAME);
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

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getOriginalExtension(): string
    {
        return $this->originalExtension;
    }
}
