<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use DateTimeInterface;

/**
 * Class DateRange
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class DateRange
{
    public static function from(DateTimeInterface $date, string $modifier): self
    {
        return new self($date, (clone $date)->modify($modifier));
    }

    private DateTimeInterface $start;
    private DateTimeInterface $end;

    public function __construct(DateTimeInterface $a, DateTimeInterface $b)
    {
        if ($a->getTimestamp() <= $b->getTimestamp()) {
            $this->start = $a;
            $this->end = $b;

            return;
        }

        $this->start = $b;
        $this->end = $a;
    }

    public function getStart(): DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(DateTimeInterface $start): DateRange
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(DateTimeInterface $end): DateRange
    {
        $this->end = $end;

        return $this;
    }
}
