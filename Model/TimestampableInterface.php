<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use DateTimeInterface;

/**
 * Interface TimestampableInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface TimestampableInterface
{
    /**
     * Sets the 'created at' date.
     */
    public function setCreatedAt(DateTimeInterface $date): TimestampableInterface;

    /**
     * Returns the 'created at' date.
     */
    public function getCreatedAt(): DateTimeInterface;

    /**
     * Set the 'updated at' date.
     */
    public function setUpdatedAt(?DateTimeInterface $date): TimestampableInterface;

    /**
     * Returns the 'updated at' date.
     */
    public function getUpdatedAt(): ?DateTimeInterface;
}
