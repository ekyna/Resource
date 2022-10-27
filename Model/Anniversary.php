<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use DateTime;
use Stringable;

use function sprintf;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Class Anniversary
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Anniversary implements Stringable
{
    public function __construct(
        private ?int $month = null,
        private ?int $day = null
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return sprintf(
            '%s-%s',
            str_pad((string)$this->month, 2, '0', STR_PAD_LEFT),
            str_pad((string)$this->day, 2, '0', STR_PAD_LEFT),
        );
    }

    public function toDate(int $year = null): DateTime
    {
        $year = $year ?? (int)date('Y');

        return new DateTime("$year-" . $this->toString());
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(int $month): Anniversary
    {
        $this->month = $month;

        return $this;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(int $day): Anniversary
    {
        $this->day = $day;

        return $this;
    }
}
