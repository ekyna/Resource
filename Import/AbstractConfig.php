<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Import;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AbstractConfig
 * @package Ekyna\Component\Resource\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractConfig implements ConfigInterface
{
    protected static array $definitions = [];

    /**
     * Returns the available column keys.
     */
    public static function getKeys(): array
    {
        return array_keys(static::$definitions);
    }

    public static function getLabel(string $key): TranslatableInterface
    {
        if (!isset(static::$definitions[$key])) {
            throw new InvalidArgumentException("Unknown import column '$key'.");
        }

        return t(static::$definitions[$key][0], [], static::$definitions[$key][1]);
    }

    private array $numbers = [];

    public function getNumbers(): array
    {
        return $this->numbers;
    }

    public function setNumbers(array $numbers): void
    {
        $this->numbers = $numbers;
    }
}
