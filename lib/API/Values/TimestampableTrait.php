<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use DateTimeImmutable;

trait TimestampableTrait
{
    private ?DateTimeImmutable $createdAt = null;

    private ?DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function updateTimestamps(): void
    {
        $this->updatedAt = new DateTimeImmutable('now');

        if (!$this->getCreatedAt() instanceof DateTimeImmutable) {
            $this->createdAt = new DateTimeImmutable('now');
        }
    }
}
