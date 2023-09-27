<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\PhpUnit\Decimal;

use PHPUnit\Runner\BeforeFirstTestHook;

/**
 * Class DecimalExtension
 * @package Ekyna\Component\Resource\Tests\PhpUnit
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DecimalExtension implements BeforeFirstTestHook
{
    public function executeBeforeFirstTest(): void
    {
        DecimalComparator::register();
    }
}
