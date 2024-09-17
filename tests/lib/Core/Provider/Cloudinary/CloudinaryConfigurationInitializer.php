<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary;

use Cloudinary\Configuration\Configuration;

final class CloudinaryConfigurationInitializer
{
    public const CLOUD_NAME = 'testcloud';

    public const API_KEY = 'apikey';

    public const API_SECRET = 'apisecret';

    public const UPLOAD_PREFIX = 'https://api.cloudinary.com';

    public const ENCRYPTION_KEY = '38128319a3a49e1d589a31a217e1a3f8';

    public static function getConfiguration(): Configuration
    {
        return Configuration::instance([
            'cloud_name' => self::CLOUD_NAME,
            'api_key' => self::API_KEY,
            'api_secret' => self::API_SECRET,
            'upload_prefix' => self::UPLOAD_PREFIX,
            'cdn_subdomain' => false,
            'force_version' => false,
        ]);
    }
}
