<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\PhpUnit\Decimal;

use Decimal\Decimal;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;

/**
 * Class DecimalComparator
 * @package Ekyna\Component\Resource\Tests\PhpUnit
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class DecimalComparator extends Comparator
{
    public static function register(): void
    {
        Factory::getInstance()->register(new self());
    }

    /**
     * @inheritDoc
     */
    public function accepts($expected, $actual)
    {
        return $expected instanceof Decimal && $actual instanceof Decimal;
    }

    /**
     * @inheritDoc
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        /**
         * @var Decimal $expected
         * @var Decimal $actual
         */

        if ($expected->equals($actual)) {
            return;
        }

        throw new ComparisonFailure(
            $expected,
            $actual,
            $expected->toString(),
            $actual->toString(),
            false,
            'Failed asserting that two decimals are equal.'
        );
    }
}
