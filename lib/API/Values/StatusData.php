<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use function array_key_exists;

final class StatusData
{
    /**
     * @var array<string, mixed>
     */
    private array $properties;

    public function __construct(array $properties = [])
    {
        $this->properties = $properties;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->properties);
    }

    /**
     * @return mixed
     */
    public function get(string $key)
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

    /**
     * @param mixed $value
     */
    public function add(string $key, $value): void
    {
        $this->properties[$key] = $value;
    }
}
