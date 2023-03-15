<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use function array_key_exists;

final class StatusData
{
    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        private array $properties = []
    ) {
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->properties);
    }

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->properties[$key];
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->properties;
    }

    public function add(string $key, mixed $value): void
    {
        $this->properties[$key] = $value;
    }
}
