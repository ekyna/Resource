<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use DateTime;
use DateTimeInterface;

/**
 * Trait TimestampableTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait TimestampableTrait
{
    protected DateTimeInterface  $createdAt;
    protected ?DateTimeInterface $updatedAt = null;


    protected function initializeTimestampable(): void
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @return $this|TimestampableInterface
     */
    public function setCreatedAt(DateTimeInterface $date): TimestampableInterface
    {
        $this->createdAt = $date;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return $this|TimestampableInterface
     */
    public function setUpdatedAt(?DateTimeInterface $date): TimestampableInterface
    {
        $this->updatedAt = $date;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
}
