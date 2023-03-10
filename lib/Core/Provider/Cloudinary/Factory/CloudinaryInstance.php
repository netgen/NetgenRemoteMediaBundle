<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory;

use Cloudinary;

final class CloudinaryInstance
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;
    private string $uploadPrefix;
    private bool $useSubdomains;

    private ?Cloudinary $cloudinary = null;

    public function __construct(
        string $cloudName,
        string $apiKey,
        string $apiSecret,
        string $uploadPrefix,
        bool $useSubdomains = false
    ) {
        $this->cloudName = $cloudName;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->uploadPrefix = $uploadPrefix;
        $this->useSubdomains = $useSubdomains;
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
