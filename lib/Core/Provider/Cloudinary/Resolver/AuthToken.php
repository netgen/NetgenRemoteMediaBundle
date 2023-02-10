<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Resolver;

use Netgen\RemoteMedia\API\Values\AuthToken as AuthTokenEntity;
use DateTimeImmutable;

final class AuthToken
{
    private ?string $encryptionKey = null;

    public function __construct(?string $encryptionKey = null)
    {
        $this->encryptionKey = $encryptionKey;
    }

    public function resolve(AuthTokenEntity $token): array
    {
        $options = [];

        $options['secure'] = true;
        $options['sign_url'] = true;
        $options['auth_token'] = [
            'key' => $this->encryptionKey,
        ];

        if ($token->getStartsAt() instanceof DateTimeImmutable) {
            $options['auth_token']['start_time'] = $token->getStartsAt()->getTimestamp();
        }

        if ($token->getExpiresAt() instanceof DateTimeImmutable) {
            $options['auth_token']['expiration'] = $token->getExpiresAt()->getTimestamp();
        }

        if ($token->getIpAddress()) {
            $options['auth_token']['ip'] = $token->getIpAddress();
        }

        return $options;
    }
}
