<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Import;

use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Interface ConfigInterface
 * @package Ekyna\Component\Resource\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ConfigInterface
{
    /**
     * Returns the available column keys.
     */
    public static function getKeys(): array;

    public static function getLabel(string $key): TranslatableInterface;

    public function getNumbers(): array;

    public function setNumbers(array $numbers): void;
}
