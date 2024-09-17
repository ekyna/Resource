<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Tracking;

/**
 * Interface NormalizerInterface
 * @package Ekyna\Component\Resource\Doctrine\ORM\Tracking
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface NormalizerInterface
{
    /**
     * @return array<string>
     */
    public function getTypes(): array;

    /**
     * @param mixed $value
     * @param array $mapping
     * @return string|array|null
     */
    public function convert(mixed $value, array $mapping): null|string|array;
}
