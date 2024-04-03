<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Upload;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use function pathinfo;

use const PATHINFO_BASENAME;
use const PATHINFO_EXTENSION;

final class FileStruct
{
    public const TYPE_LOCAL = 'local';

    public const TYPE_EXTERNAL = 'external';

    private function __construct(
        private string $uri,
        private string $originalFilename,
        private string $originalExtension,
        private string $type = self::TYPE_LOCAL,
    ) {
    }

    public static function fromPath(string $path): self
    {
        return new self(
            $path,
            pathinfo($path, PATHINFO_BASENAME),
            pathinfo($path, PATHINFO_EXTENSION),
            self::TYPE_LOCAL,
        );
    }

    public static function fromUrl(string $url): self
    {
        return new self(
            $url,
            pathinfo($url, PATHINFO_BASENAME),
            pathinfo($url, PATHINFO_EXTENSION),
            self::TYPE_EXTERNAL,
        );
    }

    public static function fromUploadedFile(UploadedFile $file): self
    {
        return new self(
            $file->getRealPath(),
            $file->getClientOriginalName(),
            $file->getClientOriginalExtension(),
            self::TYPE_LOCAL,
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

    public function getType(): string
    {
        return $this->type;
    }

    public function isLocal(): bool
    {
        return $this->type === self::TYPE_LOCAL;
    }

    public function isExternal(): bool
    {
        return $this->type === self::TYPE_EXTERNAL;
    }
}
