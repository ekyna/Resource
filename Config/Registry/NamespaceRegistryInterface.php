<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\NamespaceConfig;

/**
 * Interface NamespaceRegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements RegistryInterface<NamespaceConfig>
 */
interface NamespaceRegistryInterface extends RegistryInterface
{
    public const NAME = 'namespace';

    /**
     * Finds the namespace configuration by its name.
     *
     * @param string $namespace
     * @param bool   $throwException
     *
     * @return NamespaceConfig|null
     */
    public function find(string $namespace, bool $throwException = true): ?NamespaceConfig;
}
