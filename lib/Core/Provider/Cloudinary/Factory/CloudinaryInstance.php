<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory;

use Cloudinary;

final class CloudinaryInstance
{
    private ?Cloudinary $cloudinary = null;

    public function __construct(
        private string $cloudName,
        private string $apiKey,
        private string $apiSecret,
        private string $uploadPrefix,
        private bool $useSubdomains = false
    ) {
    }

    public function create(): Cloudinary
    {
        if (!$this->cloudinary instanceof Cloudinary) {
            $this->cloudinary = new Cloudinary();
            $this->cloudinary->config(
                [
                    'cloud_name' => $this->cloudName,
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'upload_prefix' => $this->uploadPrefix,
                    'cdn_subdomain' => $this->useSubdomains,
                    'force_version' => false,
                ],
            );
        }

        return $this->cloudinary;
    }
}
