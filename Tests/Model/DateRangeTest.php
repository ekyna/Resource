<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Model;

use DateTime;
use Ekyna\Component\Resource\Model\DateRange;
use PHPUnit\Framework\TestCase;

/**
 * Class DateRangeTest
 * @package Ekyna\Component\Resource\Tests\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DateRangeTest extends TestCase
{
    public function testGetYears(): void
    {
        $range = new DateRange(
            new DateTime('2025-04-1'),
            new DateTime('2026-02-28')
        );

        $years = $range->getYears();

        self::assertEquals(['2025', '2026'], $years);

        $range = new DateRange(
            new DateTime('2025-04-1'),
            new DateTime('2025-04-2')
        );

        $years = $range->getYears();

        self::assertEquals(['2025'], $years);
    }

    public function testGetDays(): void
    {
        $range = new DateRange(
            new DateTime('2026-01-01'),
            new DateTime('2026-12-31')
        );

        $days = $range->getDays();

        self::assertEquals(365, $days);

        $range = new DateRange(
            new DateTime('2026-01-01'),
            new DateTime('2026-01-01')
        );

        $days = $range->getDays();

        self::assertEquals(1, $days);
    }
}
