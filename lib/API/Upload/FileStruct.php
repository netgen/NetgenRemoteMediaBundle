<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Upload;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use function pathinfo;

use const PATHINFO_BASENAME;
use const PATHINFO_EXTENSION;

final class FileStruct
{
    private function __construct(
        private string $uri,
        private string $originalFilename,
        private string $originalExtension,
    ) {}

    public static function fromUri(string $uri): self
    {
        return new self(
            $uri,
            pathinfo($uri, PATHINFO_BASENAME),
            pathinfo($uri, PATHINFO_EXTENSION),
        );
    }

    public static function fromUploadedFile(UploadedFile $file)
    {
        return new self(
            $file->getRealPath(),
            $file->getClientOriginalName(),
            $file->getClientOriginalExtension(),
        );
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
