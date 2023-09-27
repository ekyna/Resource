<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\PhpUnit\Decimal;

use Decimal\Decimal;
use PHPUnit\Framework\TestCase;

/**
 * Class DecimalComparatorTest
 * @package Ekyna\Component\Resource\Tests\PhpUnit
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DecimalComparatorTest extends TestCase
{
    public function testEqual(): void
    {
        self::assertEquals(new Decimal(0), new Decimal(0));
        self::assertEquals(new Decimal(12), new Decimal(12));
        self::assertEquals(new Decimal('12.34'), new Decimal('12.34'));
        self::assertEquals(new Decimal('12.3456789'), new Decimal('12.3456789'));
    }

    public function testNotEqual(): void
    {
        self::assertNotEquals(new Decimal(0), new Decimal(1));
        self::assertNotEquals(new Decimal(1), new Decimal(2));
        self::assertNotEquals(new Decimal('12.34'), new Decimal('12.35'));
        self::assertNotEquals(new Decimal('12.34'), new Decimal('12.344'));
        self::assertNotEquals(new Decimal('12.34567891'), new Decimal('12.34567892'));
    }
}
