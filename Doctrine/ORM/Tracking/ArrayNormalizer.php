<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Tracking;

use Doctrine\DBAL\Types\Types;

use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

use function is_array;
use function is_null;
use function ksort;

/**
 * Class ArrayNormalizer
 * @package Ekyna\Component\Resource\Doctrine\ORM\Tracking
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ArrayNormalizer implements NormalizerInterface
{
    public function getTypes(): array
    {
        return [
            Types::ARRAY,
            Types::JSON,
        ];
    }

    public function convert(mixed $value, array $mapping): null|string|array
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            $this->sort($value);

            return $value;
        }

        throw new UnexpectedTypeException($value, ['array', 'null']);
    }

    private function sort(array &$array): void
    {
        ksort($array);

        foreach ($array as &$value) {
            if (!is_array($value)) {
                return;
            }

            sort($value);
        }
    }
}
