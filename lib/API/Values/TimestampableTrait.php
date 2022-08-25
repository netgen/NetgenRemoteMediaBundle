<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\API\Values;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    /**
     * @ORM\Column(name="created_at", type="datetime_immutable")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(name="updated_at", type="datetime_immutable")
     */
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

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateTimestamps(): void
    {
        $this->updatedAt = new DateTimeImmutable('now');

        if (!$this->getCreatedAt() instanceof DateTimeImmutable) {
            $this->createdAt = new DateTimeImmutable('now');
        }
    }
}
