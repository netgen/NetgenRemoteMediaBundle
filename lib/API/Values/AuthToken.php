<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use DateInterval;
use DateTimeImmutable;

final class AuthToken
{
    private ?DateTimeImmutable $startsAt;

    private DateTimeImmutable $expiresAt;

    private ?string $ipAddress;

    private function __construct(
        ?DateTimeImmutable $startsAt = null,
        ?DateTimeImmutable $expiresAt = null,
        ?string $ipAddress = null
    ) {
        $this->startsAt = $startsAt;
        $this->expiresAt = $expiresAt;
        $this->ipAddress = $ipAddress;
    }

    public static function fromDuration(int $duration): self
    {
        $expiresAt = new DateTimeImmutable();
        $expiresAt = $expiresAt->add(new DateInterval('PT' . $duration . 'S'));

        return new self(null, $expiresAt);
    }

    public static function fromExpiresAt(DateTimeImmutable $expiresAt): self
    {
        return new self(null, $expiresAt);
    }

    public static function fromPeriod(DateTimeImmutable $startsAt, DateTimeImmutable $expiresAt): self
    {
        return new self($startsAt, $expiresAt);
    }

    public function getStartsAt(): ?DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(?DateTimeImmutable $startsAt): self
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function isValid(?string $ipAddress = null): bool
    {
        $now = new DateTimeImmutable();

        if ($this->startsAt instanceof DateTimeImmutable && $this->startsAt > $now) {
            return false;
        }

        if ($this->expiresAt <= $now) {
            return false;
        }

        if ($ipAddress && $this->ipAddress !== $ipAddress) {
            return false;
        }

        return true;
    }
}
