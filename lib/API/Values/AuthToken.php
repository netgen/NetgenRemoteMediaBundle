<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use DateTimeImmutable;
use DateTime;
use DateInterval;

final class AuthToken
{
    private ?DateTimeImmutable $startsAt = null;

    private ?DateTimeImmutable $expiresAt = null;

    private ?string $ipAddress = null;

    public static function fromDuration(int $duration): self
    {
        $expiresAt = new DateTime();
        $expiresAt->add(new DateInterval('PT' . $duration . 'S'));

        return new self(null, DateTimeImmutable::createFromMutable($expiresAt));
    }

    public static function fromExpiresAt(DateTimeImmutable $expiresAt): self
    {
        return new self(null, $expiresAt);
    }

    public static function fromPeriod(DateTimeImmutable $startsAt, DateTimeImmutable $expiresAt): self
    {
        return new self($startsAt, $expiresAt);
    }

    public function __construct(
        ?DateTimeImmutable $startsAt = null,
        ?DateTimeImmutable $expiresAt = null,
        ?string $ipAddress = null
    ) {
        $this->startsAt = $startsAt;
        $this->expiresAt = $expiresAt;
        $this->ipAddress = $ipAddress;
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

    public function setExpiresAt(?DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
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

        if ($this->expiresAt instanceof DateTimeImmutable && $this->expiresAt <= $now) {
            return false;
        }

        if ($ipAddress && $this->ipAddress !== $ipAddress) {
            return false;
        }

        return true;
    }
}
