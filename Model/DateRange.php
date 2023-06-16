<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;

use function array_map;
use function iterator_to_array;
use function preg_match;

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

    private DateTimeImmutable $start;
    private DateTimeImmutable $end;

    public function __construct(DateTimeInterface $start = null, DateTimeInterface $end = null)
    {
        $start = $start ?? new DateTime();
        $end = $end ?? clone $start;

        if ($start->getTimestamp() <= $end->getTimestamp()) {
            $this->setStart($start);
            $this->setEnd($end);

            return;
        }

        $this->setStart($end);
        $this->setEnd($start);
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(DateTimeInterface $start): DateRange
    {
        $this->start = DateTimeImmutable::createFromInterface($start);

        $this->start = $this->start->setTime(0, 0);

        return $this;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    public function setEnd(DateTimeInterface $end): DateRange
    {
        $this->end = DateTimeImmutable::createFromInterface($end);;

        $this->end = $this->end->setTime(23, 59, 59, 999999);

        return $this;
    }

    public function getDays(): int
    {
        return $this->start->diff($this->end)->days;
    }

    public function getYears(): array
    {
        $years = new DatePeriod($this->start, new DateInterval('P1Y'), $this->end);

        return array_map(fn(DateTimeInterface $year): string => $year->format('Y'), iterator_to_array($years));
    }

    /**
     * @return Generator<DateRange>
     */
    public function byMonths(): Generator
    {
        $period = new DatePeriod($this->start, new DateInterval('P1M'), $this->end);

        $months = iterator_to_array($period->getIterator());

        /** @var DateTimeImmutable $date */
        foreach ($months as $date) {
            $start = $date->modify('first day of this month')->setTime(0, 0);
            if ($start < $this->start) {
                $start = clone $this->start;
            }

            $end = $date->modify('last day of this month')->setTime(23, 59, 59, 999999);
            if ($end > $this->end) {
                $end = clone $this->end;
            }

            yield new DateRange($start, $end);
        }
    }

    public static function fromString(string $value): ?DateRange
    {
        $pattern = '~^(?P<start>\d{4}-\d{2}-\d{2})_(?P<end>\d{4}-\d{2}-\d{2})$~';
        if (!preg_match($pattern, $value, $matches)) {
            return null;
        }

        return new self(
            new DateTime($matches['start']),
            new DateTime($matches['end']),
        );
    }

    public function toString(): string
    {
        return implode('_', [
            $this->start->format('Y-m-d'),
            $this->end->format('Y-m-d'),
        ]);
    }
}
