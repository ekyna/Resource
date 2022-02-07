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
     */
    public function convert($value, array $mapping): ?string;
}
